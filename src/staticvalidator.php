<?php

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
 * Dummy Exception extension.
 */
class StaticValidatorException extends Exception {
    
}

/**
 * Main class to check if variable satisfies certain conditions.
 *
 * @author Dawid Spiechowicz <spiechu@gmail.com>
 */
class StaticValidator {

    /**
     * Responds to methods starting with 'check_'.
     * Use '_' as method part separator.
     *
     * @param string $name name of unexisting method
     * @param array $args given params to unexisting method
     * @return bool true if all conditions satisfied
     */
    public static function __callStatic($name, $args) {
        if (stripos($name, 'check_') === 0) {
            $name = substr($name, 6);
            $partialNames = explode('_', $name);
            foreach ($partialNames as $funcName) {
                $funcName = self::extractFunction($funcName);
                if (!method_exists(__CLASS__, $funcName['function'])) {
                    throw new StaticValidatorException("Function {$funcName} doesn't exist!");
                }
                // check if given params are array of values to check
                if (is_array($args[0])) {
                    foreach ($args[0] as $arg) {
                        // firing up extracted function name (if args are array)
                        if (self::$funcName['function']($arg, $funcName['args']) == false) {
                            return false;
                        }
                    }
                }
                // firing up extracted function name (if args are NOT array)
                else {
                    if (self::$funcName['function']($args[0], $funcName['args']) == false) {
                        return false;
                    }
                }
            }
            // all extracted functions passed, args are valid
            return true;
        }
        // catches wrong function name at start
        throw new StaticValidatorException("Unknown function {$name}");
    }

    /**
     * Searches for known validation functions.
     * @param string $name name to search
     * @return array assoc table with func name and optional parameters
     */
    protected static function extractFunction($name) {
        $name = strtolower($name);
        // if name starts with 'not'
        if (stripos($name, 'not') === 0) {
            // strip 'not'
            $name = substr($name, 3);
            return array(
                'function' => 'isOrNot',
                'args' => array(
                    'not' => true,
                    'funcname' => $name
            ));
        }
        // the same if name starts with 'is'
        elseif (stripos($name, 'is') === 0) {
            $name = substr($name, 2);
            return array(
                'function' => 'isOrNot',
                'args' => array(
                    'not' => false,
                    'funcname' => $name
            ));
        } elseif (stripos($name, 'gt') === 0) {
            return array(
                'function' => 'eqLtGt',
                'args' => array(
                    'func' => 'gt',
                    'warunek' => substr($name, 2)
            ));
        } elseif (stripos($name, 'lt') === 0) {
            return array(
                'function' => 'eqLtGt',
                'args' => array(
                    'func' => 'lt',
                    'warunek' => substr($name, 2)
            ));
        } elseif (stripos($name, 'eq') === 0) {
            return array(
                'function' => 'eqLtGt',
                'args' => array(
                    'func' => 'eq',
                    'warunek' => substr($name, 2)
            ));
        } elseif (stripos($name, 'between') === 0) {
            $name = substr($name, 7);
            return array(
                'function' => 'between',
                // explode numbers at front and end of 'and'
                'args' => explode('and', $name));
        } elseif (stripos($name, 'minlength') === 0) {
            return array(
                'function' => 'minMaxLength',
                'args' => array(
                    'func' => 'min',
                    'warunek' => substr($name, 9)
            ));
        } elseif (stripos($name, 'maxlength') === 0) {
            return array(
                'function' => 'minMaxLength',
                'args' => array(
                    'func' => 'max',
                    'warunek' => substr($name, 9)
            ));
        } elseif (stripos($name, 'only') === 0) {
            return array(
                'function' => 'only',
                'args' => substr($name, 4));
        }
        // if it comes here, function name can't be found
        else {
            throw new StaticValidatorException("Couldn't reslove function name {$name}");
        }
    }

    protected static function eqLtGt($var, array $args) {
        if (!is_numeric($var))
            throw new StaticValidatorException("Value {$var} is not numeric");
        if (!is_numeric($args['warunek']))
            throw new StaticValidatorException("Condition {$args['warunek']} is not numeric");
        switch ($args['func']) {
            case 'gt':
                return ($var > $args['warunek']);
                break;
            case 'lt':
                return ($var < $args['warunek']);
                break;
            case 'eq':
                return ($var === $args['warunek']);
                break;
            default:
                throw new StaticValidatorException("Couldn't resolve condition (eq|lt|gt) at {$args['func']}");
        }
    }

    protected static function isOrNot($var, array $args) {
        $not = (isset($args['not'])
                && is_bool($args['not'])) ? $args['not'] : false;
        $funcname = (isset($args['funcname'])) ? (string) $args['funcname'] : '';
        $funcname = strtolower($funcname);
        switch ($funcname) {
            case 'null':
                $result = is_null($var);
                break;
            case 'set':
                $result = isset($var);
                break;
            case 'empty':
                if ($var === 0) {
                    $result = false;
                }
                else {
                    $result = empty($var);
                }
                break;
            case 'int':
                $result = is_int($var);
                break;
            case 'string':
                $result = is_string($var);
                break;
            default:
                throw new StaticValidatorException("Couldn't resolve argument {$funcname}");
        }
        return ($not === true) ? !$result : $result;
    }

    /**
     * Checks if $var stands in range $arg[0] <= $var <= arg[1].
     * @param numeric $var to check
     * @param array $arg $arg[0] i $arg[1] numeric type
     * @return bool
     */
    protected static function between($var, array $arg) {
        if (!is_numeric($var))
            throw new StaticValidatorException("Value {$var} is not numeric");
        if (!is_numeric($arg[0]) || !is_numeric($arg[1]))
            throw new StaticValidatorException("Condition {$arg[0]} or {$arg[1]} is not numeric");
        return (($var >= $arg[0]) && ($var <= $arg[1]));
    }

    /**
     * Checks if $var string is between given lenght.
     * @param string $var to check
     * @param array $args
     * @return bool
     */
    protected static function minMaxLength($var, array $args) {
        if (!is_string($var))
            throw new StaticValidatorException("Checked value {$var} is not a string");
        if (!is_int($args['warunek']))
            throw new StaticValidatorException("Condition {$args['warunek']} is not integer");
        switch ($args['func']) {
            case 'min':
                return (strlen($var) >= (int) $args['warunek']);
                break;
            case 'max':
                return (strlen($var) <= (int) $args['warunek']);
                break;
            default:
                throw new StaticValidatorException("Couldn't resolve condition {$args['func']}");
        }
    }

    protected static function only($var, $arg) {
        switch ($arg) {
            case 'letters':
                if (function_exists('ctype_alpha')) {
                    return ctype_alpha($var);
                } else {
                    $alg = '/^[A-Z]{1,}$/i';
                }
                break;
            case 'numbers':
                if (function_exists('ctype_digit')) {
                    return ctype_digit($var);
                } else {
                    $alg = '/^[0-9]{1,}$/';
                }
                break;
            case 'alnums':
                if (function_exists('ctype_alnum')) {
                    return ctype_alnum($var);
                } else {
                    $alg = '/^[A-Z0-9]{1,}$/i';
                }
                break;
            default:
                throw new StaticValidatorException("Couldn't resolve condition {$arg}");
        }
        if (preg_match_all($alg, $var, $match) === 1) {
            return true;
        } else {
            return false;
        }
    }

}