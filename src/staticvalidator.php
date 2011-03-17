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
            foreach($partialNames as $funcName) {
                $funcName = self::extractFunction($funcName);
                if (!method_exists(__CLASS__, $funcName['funkcja'])) {
                    throw new Exception("Function {$funcName} doesn't exist!");
                }
                // check if given params are array of values to check
                if (is_array($args[0])) {
                    foreach ($args[0] as $arg) {
                        // gwozdz programu! wywolujemy
                        // rozpracowana funkcje i podajemy ewentualne
                        // dodatkowe parametry
                        if (self::$funcName['funkcja']($arg, $funcName['args']) == false) {
                            return false;
                        }
                    }
                }
                // jezeli parametr nie jest tablica
                else {
                    if (self::$funcName['funkcja']($args[0], $funcName['args']) == false) {
                        return false;
                    }
                }
            }
            // jezeli dolecialo az tutaj tzn., ze wartosc poprawnie zwalidowana
            return true;
        }
        // wylapie zle skonstruowany poczatek nazwy funkcji
        throw new Exception("Próba wywołania nierozpoznanej funkcji {$name}");
    }

    /**
     * Szuka znanych sobie funkcji skladowych do wywolania.
     * @param string $name
     * @return array tablica z nazwa funkcji i parametrami
     */
    protected static function extractFunction($name) {
        // calosc na male litery
        $name = strtolower($name);
        // jezeli zaczyna sie od 'not'
        if (stripos($name, 'not') === 0) {
            // ciachnij 'not'
            $name = substr($name, 3);
            return array(
                    'funkcja' => 'isOrNot',
                    'args' => array(
                            'not' => true,
                            'funcname' => $name
            ));
        }
        // to samo jezeli zaczyna sie od 'is'
        elseif (stripos($name, 'is') === 0) {
            $name = substr($name, 2);
            return array(
                    'funkcja' => 'isOrNot',
                    'args' => array(
                            'not' => false,
                            'funcname' => $name
            ));
        }
        elseif (stripos($name, 'gt') === 0) {
            return array(
                    'funkcja' => 'eqLtGt',
                    'args' => array(
                            'func' => 'gt',
                            'warunek' => substr($name, 2)
            ));
        }
        elseif (stripos($name, 'lt') === 0) {
            return array(
                    'funkcja' => 'eqLtGt',
                    'args' => array(
                            'func' => 'lt',
                            'warunek' => substr($name, 2)
            ));
        }
        elseif (stripos($name, 'eq') === 0) {
            return array(
                    'funkcja' => 'eqLtGt',
                    'args' => array(
                            'func' => 'eq',
                            'warunek' => substr($name, 2)
            ));
        }
        elseif (stripos($name, 'between') === 0) {
            $name = substr($name, 7);
            return array(
                    'funkcja' => 'between',
                    // z tego co zostalo
                    // rozwalamy wartosci przy 'and'
                    'args' => explode('and', $name));
        }
        elseif (stripos($name, 'minlength') === 0) {
            return array(
                    'funkcja' => 'minMaxLength',
                    'args' => array(
                            'func' => 'min',
                            'warunek' => substr($name, 9)
            ));
        }
        elseif (stripos($name, 'maxlength') === 0) {
            return array(
                    'funkcja' => 'minMaxLength',
                    'args' => array(
                            'func' => 'max',
                            'warunek' => substr($name, 9)
            ));
        }
        elseif (stripos($name, 'only') === 0) {
            return array(
                    'funkcja' => 'only',
                    'args' => substr($name,4));
        }
        // jezeli doszlo az tutaj to
        // nierozpoznano funkcji i
        // wywalam wyjatek
        else {
            throw new Exception("Nie rozpoznano funkcji {$name}");
        }
    }

    public static function eqLtGt($var, array $argz) {
        if (!is_numeric($var)) throw new Exception("Sprawdzana wartosc {$var} nie jest liczba!");
        if (!is_numeric($argz['warunek'])) throw new Exception("Warunek {$argz['warunek']} nie jest liczba!");
        switch ($argz['func']) {
            case 'gt':
                return ($var > $argz['warunek']);
                break;
            case 'lt':
                return ($var < $argz['warunek']);
                break;
            case 'eq':
                return ($var === $argz['warunek']);
                break;
            default:
                throw new Exception("Nie rozpoznano warunku eq lt gt {$argz['func']}");
        }
    }

    public static function isOrNot($var, array $argz) {
        $not = (isset($argz['not'])
                        && is_bool($argz['not']))
                ? $argz['not']
                : false;
        $funcname = (isset($argz['funcname']))
                ? (string) $argz['funcname']
                : '';
        $funcname = strtolower($funcname);
        switch($funcname) {
            case 'null':
                $result = is_null($var);
                break;
            case 'set':
                $result = isset($var);
                break;
            case 'empty':
                $result = empty($var);
                break;
            case 'int':
                $result = is_int($var);
                break;
            case 'string':
                $result = is_string($var);
                break;
            default:
                throw new Exception("Nie rozpoznano argumentu {$funcname}");
                break;
        }
        return ($not === true) ? !$result : $result;
    }

    /**
     * Sprawdza czy $var znajduje sie w przedziale $arg[0] <= $var <= arg[1].
     * @param numeric $var
     * @param array $arg $arg[0] i $arg[1] typu numeric
     * @return bool
     */
    public static function between($var, array $arg) {
        if (!is_numeric($var)) throw new Exception("Sprawdzana wartosc {$var} nie jest liczba!");
        if (!is_numeric($arg[0]) || !is_numeric($arg[1])) throw new Exception("Warunek {$arg[0]} lub {$arg[1]} nie jest liczba!");
        return (($var >= $arg[0]) && ($var <= $arg[1]));
    }

    /**
     * Sprawdza min i max dlugosc ciagu $var.
     * @param string $var
     * @param int $arg
     * @return bool
     */
    public static function minMaxLength($var, array $argz) {
        if (!is_string($var)) throw new Exception("Sprawdzana wartosc {$var} nie jest stringiem!");
        if (!is_numeric($argz['warunek'])) throw new Exception("Warunek {$argz['warunek']} nie jest liczba calkowita!");
        switch ($argz['func']) {
            case 'min':
                return (strlen($var) >= (int) $argz['warunek']);
                break;
            case 'max':
                return (strlen($var) <= (int) $argz['warunek']);
                break;
            default:
                throw new Exception("Nie rozpoznano warunku {$argz['func']}");
                break;
        }
    }

    public static function only($var, $arg) {
        $arg = strtolower($arg);
        $alg = '';
        $var = (string) $var;
        switch($arg) {
            case 'letters':
                if (function_exists('ctype_alpha')) {
                    return ctype_alpha($var);
                }
                else {
                    $alg = '/^[A-Z]{1,}$/i';
                }
                break;
            case 'numbers':
                if (function_exists('ctype_digit')) {
                    return ctype_digit($var);
                }
                else {
                    $alg = '/^[0-9]{1,}$/';
                }
                break;
            case 'alnums':
                if (function_exists('ctype_alnum')) {
                    return ctype_alnum($var);
                }
                else {
                    $alg = '/^[A-Z0-9]{1,}$/i';
                }
                break;
        }
        if (preg_match_all($alg, $var, $match) === 1) {
            return true;
        }
        else {
            return false;
        }
    }
}