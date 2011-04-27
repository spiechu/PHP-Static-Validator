<?php
require_once 'PHPUnit/Framework.php';
require_once __DIR__ . '/../src/staticvalidator.php';

class StaticValidatorTestCase extends PHPUnit_Framework_TestCase {

    protected $errorLevel = null;

    const EMPTY_VALUE = '';
    const NULL_VALUE  = null;
    const INT_ZERO = 0;
    const INT_POS = 5;
    const INT_NEG = -5;
    const STRING_LETTERS = 'abcdef';
    const STRING_NUMBERS = '12345';
    const STRING_ALNUMS  = 'abcdef12345';
    const STRING_ADDITIONAL_CHARS  = ' _.,;';

    protected function suppressNotices() {
        $this->errorLevel = error_reporting();
        error_reporting (E_ALL ^ E_NOTICE);
    }

    protected function endNoticeSuppression() {
        if ($this->errorLevel === null) {
            throw new Exception('Cant end notices suppression');
        }
        else {
            error_reporting ($this->errorLevel);
        }
    }

    public function testValidatorWontStart() {
        $this->setExpectedException('StaticValidatorException');
        StaticValidator::unknown('abc');
    }

    public function testValidatorWontFindFunction() {
        $this->setExpectedException('StaticValidatorException');
        StaticValidator::check('abc');
    }

    public function testValidatorWontResolveFunctionName() {
        $this->setExpectedException('StaticValidatorException');
        StaticValidator::check_('abc');
    }

    public function testIsSetWillRaiseError() {
        $this->setExpectedException('PHPUnit_Framework_Error');
        $this->assertFalse(StaticValidator::check_isSet($unset));
    }

    public function testIsOrNotSet() {
        $this->suppressNotices();
        $this->assertFalse(StaticValidator::check_isSet($unset));
        $this->assertTrue(StaticValidator::check_notSet($unset));
        $this->endNoticeSuppression();
        $this->assertTrue(StaticValidator::check_isSet(self::EMPTY_VALUE));
        $this->assertFalse(StaticValidator::check_notSet(self::EMPTY_VALUE));
    }

    public function testEmpty() {
        $this->assertTrue(StaticValidator::check_isEmpty(self::EMPTY_VALUE));
        $this->assertFalse(StaticValidator::check_isEmpty(self::STRING_LETTERS));
        $this->assertTrue(StaticValidator::check_notEmpty(self::STRING_LETTERS));
        $this->assertFalse(StaticValidator::check_notEmpty(self::EMPTY_VALUE));
    }

    public function testNull() {
        $this->assertTrue(StaticValidator::check_isNull(self::NULL_VALUE));
        $this->assertFalse(StaticValidator::check_isNull(self::INT_NEG));
        $this->assertTrue(StaticValidator::check_notNull(self::EMPTY_VALUE));
        $this->assertFalse(StaticValidator::check_notNull(self::NULL_VALUE));
    }

    public function testInt() {
        $this->assertTrue(StaticValidator::check_isInt(self::INT_POS));
        $this->assertFalse(StaticValidator::check_isInt(self::STRING_NUMBERS));
        $this->assertFalse(StaticValidator::check_notInt(self::INT_POS));
        $this->assertTrue(StaticValidator::check_notInt(self::STRING_NUMBERS));
    }
    
    public function testZeroValue() {
        $this->assertTrue(StaticValidator::check_isInt(self::INT_ZERO));
        $this->assertFalse(StaticValidator::check_notInt(self::INT_ZERO));
        $this->assertFalse(StaticValidator::check_isNull(self::INT_ZERO));
        $this->assertTrue(StaticValidator::check_notNull(self::INT_ZERO));
        $this->assertTrue(StaticValidator::check_notEmpty(self::INT_ZERO));
        $this->assertFalse(StaticValidator::check_isEmpty(self::INT_ZERO));
    }

    public function testString() {
        $this->assertTrue(StaticValidator::check_isString(self::STRING_ADDITIONAL_CHARS));
        $this->assertFalse(StaticValidator::check_isString(self::INT_NEG));
        $this->assertTrue(StaticValidator::check_notString(self::INT_POS));
        $this->assertFalse(StaticValidator::check_notString(self::STRING_ADDITIONAL_CHARS));
    }
    
    public function testGtCondition() {
        $this->assertFalse(StaticValidator::check_gt1(1));
        $this->assertTrue(StaticValidator::check_gt1(2));
        $this->assertTrue(StaticValidator::check_gt1(1.1));
        $this->assertFalse(StaticValidator::check_gt1(0));
        $this->assertFalse(StaticValidator::check_gt1(-1));
        $this->setExpectedException('StaticValidatorException');
        $this->assertFalse(StaticValidator::check_gt1('2'));
    }
}