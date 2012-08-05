#  PHP Static Validator

Class primarily created to find some useful purpose for `__callStatic` function new in PHP 5.3.0. With PHP StaticValidator You can chain multiple conditions and checked value is being tested one by one.

## Installation

Since Validator uses namespaces, it is required to use class autoloader, for example `SplClassLoader.php` (You will find it in `tests` directory). It is standard autoloading class [more info here](http://groups.google.com/group/php-standards/web/psr-0-final-proposal?pli=1). You have to register Validator classes:

```php
<?php
require_once('SplClassLoader.php');
$classLoader = new SplClassLoader('Spiechu\StaticValidator' , 'library');
$classLoader->register();
```

No external libraries required.

## Documentation

Static Validator's class is designed to check if given variable or array of variables meet certain conditions.

Basically every check method starts with `check` word, and then You can use separator `_` and type next condition which tested variable is supposed to meet.

For exaple `Validator::check_notNull_isInt_gt5($testedVariable);` we can translate to `!is_null($testedVariable) && is_int($testedVariable) && ($testedVariable > 5)` You can see we gain some concise and some flexibility (`gt5`).

We can distinguish three main components in PHP Static Validator:

1. simple wrappers (for eg. isSet, notSet, isNull, notNull, isInt, notInt, isString, notString)
2. regular expressions based (for eg. onlyLetters, onlyNumbers)
3. magic functions (for eg. eq3, gt3, lt3, minLength5)

Highly recommended way of constructing magic method name is from broadest condition to narrowest. For eg. `notNull` is broader condition than `isInt`, which is broader than `gt5`.

### Complete list of functions
1. Wrappers:
    - [is|not]Set
    - [is|not]Null
    - [is|not]Empty
    - [is|not]Int
    - [is|not]String
2. Regular expressions:
    - onlyLetters
    - onlyNumbers
    - onlyAlnums
3. Magic functions:
    - lt# - less than _number_
    - gt# - greater than _number_
    - eq# - equals to _number_
    - between#and# - matches range _number_
    - min# - minimum string length
    - max# - maximum string length

## Known Issues

When variable is not set, the only way to avoid `E_NOTICE` is to pass variable to check by reference instead of pass by value. Passing by reference cannot be performed in __callStatic's `$arg` table so You have to either suppress `E_NOTICE` warnings or avoid using `isSet` or `notSet`.