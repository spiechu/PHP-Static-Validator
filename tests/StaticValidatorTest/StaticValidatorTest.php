<?php

/*
 * This file is part of the PHP Static Validator package.
 *
 * (c) Dawid Spiechowicz <spiechu@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiechu\StaticValidatorTest;

use Spiechu\StaticValidator\Validator;

/**
 * @author Dawid Spiechowicz <spiechu@gmail.com>
 * @package PHP Static Validator
 * @since 0.1
 */
class StaticValidatorTest extends \PHPUnit_Framework_TestCase
{

    protected $errorLevel = null;

    const EMPTY_VALUE = '';
    const NULL_VALUE = null;
    const INT_ZERO = 0;
    const INT_POS = 5;
    const INT_NEG = -5;
    const STRING_LETTERS = 'abcdef';
    const STRING_NUMBERS = '12345';
    const STRING_ALNUMS = 'abcdef12345';
    const STRING_ADDITIONAL_CHARS = ' _.,;';

    protected function suppressNotices()
    {
        $this->errorLevel = error_reporting();
        error_reporting(E_ALL ^ E_NOTICE);
    }

    protected function endNoticeSuppression()
    {
        if ($this->errorLevel === null) {
            throw new Exception('Cant end notices suppression');
        } else {
            error_reporting($this->errorLevel);
        }
    }

    public function testValidatorWontStart()
    {
        $this->setExpectedException('Spiechu\StaticValidator\Exceptions\ValidatorException');
        Validator::unknown('dummy');
    }

    public function testValidatorWontFindFunction()
    {
        $this->setExpectedException('Spiechu\StaticValidator\Exceptions\ValidatorException');
        Validator::check('abc');
    }

    public function testValidatorWontResolveFunctionName()
    {
        $this->setExpectedException('Spiechu\StaticValidator\Exceptions\ValidatorException');
        Validator::check_('abc');
    }

    public function testIsSetWillRaiseError()
    {
        $this->setExpectedException('PHPUnit_Framework_Error');
        $this->assertFalse(Validator::check_isSet($unset));
    }

    public function testIsOrNotSet()
    {
        $this->suppressNotices();
        $this->assertFalse(Validator::check_isSet($unset));
        $this->assertTrue(Validator::check_notSet($unset));
        $this->endNoticeSuppression();
        $this->assertTrue(Validator::check_isSet(self::EMPTY_VALUE));
        $this->assertFalse(Validator::check_notSet(self::EMPTY_VALUE));
    }

    public function testEmpty()
    {
        $this->assertTrue(Validator::check_isEmpty(self::EMPTY_VALUE));
        $this->assertFalse(Validator::check_isEmpty(self::STRING_LETTERS));
        $this->assertTrue(Validator::check_notEmpty(self::STRING_LETTERS));
        $this->assertFalse(Validator::check_notEmpty(self::EMPTY_VALUE));
    }

    public function testNull()
    {
        $this->assertTrue(Validator::check_isNull(self::NULL_VALUE));
        $this->assertFalse(Validator::check_isNull(self::INT_NEG));
        $this->assertTrue(Validator::check_notNull(self::EMPTY_VALUE));
        $this->assertFalse(Validator::check_notNull(self::NULL_VALUE));
    }

    public function testInt()
    {
        $this->assertTrue(Validator::check_isInt(self::INT_POS));
        $this->assertFalse(Validator::check_isInt(self::STRING_NUMBERS));
        $this->assertFalse(Validator::check_notInt(self::INT_POS));
        $this->assertTrue(Validator::check_notInt(self::STRING_NUMBERS));
    }

    public function testZeroValue()
    {
        $this->assertTrue(Validator::check_isInt(self::INT_ZERO));
        $this->assertFalse(Validator::check_notInt(self::INT_ZERO));
        $this->assertFalse(Validator::check_isNull(self::INT_ZERO));
        $this->assertTrue(Validator::check_notNull(self::INT_ZERO));
        $this->assertTrue(Validator::check_notEmpty(self::INT_ZERO));
        $this->assertFalse(Validator::check_isEmpty(self::INT_ZERO));
    }

    public function testString()
    {
        $this->assertTrue(Validator::check_isString(self::STRING_ADDITIONAL_CHARS));
        $this->assertFalse(Validator::check_isString(self::INT_NEG));
        $this->assertTrue(Validator::check_notString(self::INT_POS));
        $this->assertFalse(Validator::check_notString(self::STRING_ADDITIONAL_CHARS));
    }

    public function testGtCondition()
    {
        $this->assertFalse(Validator::check_gt1(1));
        $this->assertTrue(Validator::check_gt1(2));
        $this->assertTrue(Validator::check_gt1(1.1));
        $this->assertFalse(Validator::check_gt1(0));
        $this->assertFalse(Validator::check_gt1(-1));
        $this->setExpectedException('Spiechu\StaticValidator\Exceptions\ValidatorException');
        $this->assertFalse(Validator::check_gt1('2'));
    }

    public function testLtCondition()
    {
        $this->assertFalse(Validator::check_lt1(1));
        $this->assertTrue(Validator::check_lt1(0));
        $this->assertFalse(Validator::check_lt1(2));
        $this->assertTrue(Validator::check_lt1(0.9));
        $this->assertTrue(Validator::check_lt1(-1));
        $this->setExpectedException('Spiechu\StaticValidator\Exceptions\ValidatorException');
        $this->assertFalse(Validator::check_lt1('2'));
    }

    public function testEqCondition()
    {
        $this->assertTrue(Validator::check_eq1(1));
        $this->assertFalse(Validator::check_eq1(0));
        $this->assertFalse(Validator::check_eq1(-1));
        $this->assertFalse(Validator::check_eq1(2));
        $this->setExpectedException('Spiechu\StaticValidator\Exceptions\ValidatorException');
        $this->assertFalse(Validator::check_eq1('1'));
    }

}
