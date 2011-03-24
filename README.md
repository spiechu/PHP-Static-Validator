#  PHP Static Validator

Class primarily created to find some useful purpose for `__callStatic` function new in PHP 5.3.0. With PHP StaticValidator You can chain multiple conditions and checked value is being tested one by one condition.

## Installation

Nothing special here. Just make sure You have at least PHP 5.3.0 and perform `require_once('staticvalidator.php');`

## Documentation

In progress...

## Known Issues

When variable is not set, the only way to avoid `E_NOTICE` is to pass variable to check by reference instead of pass by value. Passing by reference cannot be performed in __callStatic's `$arg` table so You have to either suppress `E_NOTICE` warnings or avoid using `isSet` or `notSet`.