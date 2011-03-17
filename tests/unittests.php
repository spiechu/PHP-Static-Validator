<?php
require_once 'PHPUnit/Framework.php';
require_once __DIR__ . '/../src/staticvalidator.php';

class StaticValidatorTestCase extends PHPUnit_Framework_TestCase {
    protected $errorLevel = null;
    
    const EMPTY_VALUE = '';
    const INT_ZERO = 0;
    const INT_POS = 5;
    const INT_NEG = -5;

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
    }
}