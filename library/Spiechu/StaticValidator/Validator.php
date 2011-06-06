<?php

namespace Spiechu\StaticValidator;

use Spiechu\StaticValidator\Exceptions\ValidatorException;
use Spiechu\StaticValidator\Exceptions\ValidatorDataTypeMismatchException;

/**
 * Copyright 2011 Dawid Spiechowicz
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/**
 * Main class to check if variable satisfies certain conditions.
 *
 * @author Dawid Spiechowicz <spiechu@gmail.com>
 * @package PHP Static Validator
 */
class Validator
{

    /**
     * Responds to methods starting with 'check_'.
     * Use '_' as method part separator.
     *
     * @param string $name name of unexisting method
     * @param array $args given params to unexisting method
     * @return bool true if all conditions satisfied
     * @throws StaticValidatorException when called function not starts with check_ or function name cannot be recognized
     */
    public static function __callStatic($name , $args)
    {
        if (stripos($name , 'check_') === 0)
        {
            $name = substr($name , 6);
            $partialNames = explode('_' , $name);
            foreach ($partialNames as $funcName)
            {
                $funcName = self::extractFunction($funcName);
                if (!method_exists(__CLASS__ , $funcName['function']))
                    throw new ValidatorException("Function {$funcName} doesn't exist!");
                // check if given params are array of values to check
                if (is_array($args[0]))
                {
                    foreach ($args[0] as $arg)
                    {
                        // firing up extracted function name (if args are array)
                        if (self::$funcName['function']($arg , $funcName['args']) == false)
                            return false;
                    }
                }
                // firing up extracted function name (if args are NOT array)
                else
                {
                    if (self::$funcName['function']($args[0] , $funcName['args']) == false)
                        return false;
                }
            }
            // all extracted functions passed, args are valid
            return true;
        }
        // catches wrong function name at start
        throw new ValidatorException("Unknown function {$name}");
    }

    /**
     * Searches for known validation functions.
     * @param string $name name to search
     * @return array assoc table with func name and optional parameters
     * @throws StaticValidatorException when cannot resolve function name
     */
    protected static function extractFunction($name)
    {
        $name = strtolower($name);
        // if name starts with 'not'
        if (stripos($name , 'not') === 0)
        {
            // strip 'not'
            $name = substr($name , 3);
            return array(
                'function' => 'isOrNot' ,
                'args' => array(
                    'not' => true ,
                    'subfunc' => $name
            ));
        }
        // the same if name starts with 'is'
        elseif (stripos($name , 'is') === 0)
        {
            $name = substr($name , 2);
            return array(
                'function' => 'isOrNot' ,
                'args' => array(
                    'not' => false ,
                    'subfunc' => $name
            ));
        }
        elseif (stripos($name , 'gt') === 0)
        {
            return array(
                'function' => 'eqLtGt' ,
                'args' => array(
                    'subfunc' => 'gt' ,
                    'condition' => substr($name , 2)
            ));
        }
        elseif (stripos($name , 'lt') === 0)
        {
            return array(
                'function' => 'eqLtGt' ,
                'args' => array(
                    'subfunc' => 'lt' ,
                    'condition' => substr($name , 2)
            ));
        }
        elseif (stripos($name , 'eq') === 0)
        {
            return array(
                'function' => 'eqLtGt' ,
                'args' => array(
                    'subfunc' => 'eq' ,
                    'condition' => substr($name , 2)
            ));
        }
        elseif (stripos($name , 'between') === 0)
        {
            $name = substr($name , 7);
            return array(
                'function' => 'between' ,
                // explode numbers at front and end of 'and'
                'args' => explode('and' , $name));
        }
        elseif (stripos($name , 'minlength') === 0)
        {
            return array(
                'function' => 'minMaxLength' ,
                'args' => array(
                    'subfunc' => 'min' ,
                    'condition' => substr($name , 9)
            ));
        }
        elseif (stripos($name , 'maxlength') === 0)
        {
            return array(
                'function' => 'minMaxLength' ,
                'args' => array(
                    'subfunc' => 'max' ,
                    'condition' => substr($name , 9)
            ));
        }
        elseif (stripos($name , 'only') === 0)
        {
            return array(
                'function' => 'only' ,
                'args' => array(
                    'subfunc' => substr($name , 4)
            ));
        }
        // if it comes here, function name can't be found
        else
        {
            throw new ValidatorException("Couldn't reslove function name {$name}");
        }
    }

    /**
     * @param numeric $var value to check
     * @param array $args condition type and value to check against
     * @return bool
     * @throws StaticValidatorDataTypeMismatchException when $var or $args['condition'] is not numeric
     * @throws StaticValidatorException when $args['subfunc'] is other than eq, lt, gt
     */
    protected static function eqLtGt($var , array $args)
    {
        if (is_string($var))
            throw new ValidatorDataTypeMismatchException("Value {$var} is string, cast it to numeric");
        if (!is_numeric($var))
            throw new ValidatorDataTypeMismatchException("Value {$var} is not numeric");
        if (!is_numeric($args['condition']))
            throw new ValidatorDataTypeMismatchException("Condition {$args['condition']} is not numeric");
        switch ($args['subfunc'])
        {
            case 'gt':
                return ($var > $args['condition']);
                break;
            case 'lt':
                return ($var < $args['condition']);
                break;
            case 'eq':
                return ($var == $args['condition']);
                break;
            default:
                throw new ValidatorException("Couldn't resolve argument (eq|lt|gt) at {$args['subfunc']}");
        }
    }

    /**
     * @param mixed $var variable to check type
     * @param array $args array with datatype name to check and optional not to reverse returned bool value
     * @return bool
     * @throws StaticValidatorException when cannot recognize datatype name to check
     */
    protected static function isOrNot($var , array $args)
    {
        $not = (isset($args['not'])
                && is_bool($args['not'])) ? $args['not'] : false;
        switch ($args['subfunc'])
        {
            case 'null':
                $result = is_null($var);
                break;
            case 'set':
                $result = isset($var);
                break;
            case 'empty':
                if ($var === 0)
                    $result = false;
                else
                    $result = empty($var);
                break;
            case 'int':
                $result = is_int($var);
                break;
            case 'string':
                $result = is_string($var);
                break;
            default:
                throw new ValidatorException("Couldn't resolve argument {$args['subfunc']}");
        }
        return ($not === true) ? !$result : $result;
    }

    /**
     * Checks if $var stands in range $arg[0] <= $var <= arg[1].
     * @param numeric $var to check
     * @param array $arg $arg[0] i $arg[1] numeric type
     * @return bool
     */
    protected static function between($var , array $arg)
    {
        if (!is_numeric($var))
            throw new ValidatorDataTypeMismatchException("Value {$var} is not numeric");
        if (!is_numeric($arg[0]) || !is_numeric($arg[1]))
            throw new ValidatorDataTypeMismatchException("Condition {$arg[0]} or {$arg[1]} is not numeric");
        return (($var >= $arg[0]) && ($var <= $arg[1]));
    }

    /**
     * Checks if $var string is between given length.
     * @param string $var to check
     * @param array $args
     * @return bool
     */
    protected static function minMaxLength($var , array $args)
    {
        if (!is_string($var))
            throw new ValidatorDataTypeMismatchException("Checked value {$var} is not a string");
        if (!is_int($args['condition']))
            throw new ValidatorDataTypeMismatchException("Condition {$args['condition']} is not integer");
        switch ($args['subfunc'])
        {
            case 'min':
                return (strlen($var) >= (int) $args['condition']);
                break;
            case 'max':
                return (strlen($var) <= (int) $args['condition']);
                break;
            default:
                throw new ValidatorException("Couldn't resolve condition {$args['subfunc']}");
        }
    }

    protected static function only($var , array $args)
    {
        switch ($args['subfunc'])
        {
            case 'letters':
                if (function_exists('ctype_alpha'))
                    return ctype_alpha($var);
                else
                    $alg = '/^[A-Z]{1,}$/i';
                break;
            case 'numbers':
                if (function_exists('ctype_digit'))
                    return ctype_digit($var);
                else
                    $alg = '/^[0-9]{1,}$/';
                break;
            case 'alnums':
                if (function_exists('ctype_alnum'))
                    return ctype_alnum($var);
                else
                    $alg = '/^[A-Z0-9]{1,}$/i';
                break;
            default:
                throw new ValidatorException("Couldn't resolve condition {$args['subfunc']}");
        }
        if (preg_match_all($alg , $var , $match) === 1)
            return true;
        else
            return false;
    }

}