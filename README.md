#  PHP Static Validator

Class primarily created to find some useful purpose for `__callStatic` function new in PHP 5.3.0. With PHP StaticValidator You can chain multiple conditions and checked value is being tested one by one condition.

## Installation

Nothing special here. Just make sure You have at least PHP 5.3.0 and perform `require_once('staticvalidator.php');`

## Documentation

Static Validator's class is designed to check if given variable or table of variables meet certain conditions.

Basically every check method starts with `check` word, and then You can use separator `_` and type next condition which tested variable is supposed to meet.

For exaple `StaticValidator::check_notNull_isInt_gt5($testedVariable);` we can translate to `!is_null($testedVariable) && is_int($testedVariable) && ($testedVariable > 5)` You can see we gain some concise and some flexibility (`gt5`).

We can distinguish three main components in PHP Static Validator:

- simple wrappers (for eg. isSet, notSet, isNull, notNull, isInt, notInt, isString, notString)
- regular expressions based (for eg. onlyLetters, onlyNumbers)
- magic functions (for eg. eq3, gt3, lt3, minLength5)

## Known Issues

When variable is not set, the only way to avoid `E_NOTICE` is to pass variable to check by reference instead of pass by value. Passing by reference cannot be performed in __callStatic's `$arg` table so You have to either suppress `E_NOTICE` warnings or avoid using `isSet` or `notSet`.