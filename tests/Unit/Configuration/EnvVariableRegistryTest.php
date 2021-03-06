<?php

namespace DDTrace\Tests\Unit\Configuration;

use DDTrace\Configuration\EnvVariableRegistry;
use DDTrace\Tests\Unit\BaseTestCase;

final class EnvVariableRegistryTest extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();
        putenv('DD_SOME_TEST_PARAMETER');
    }

    public function testTrueValueWhenEnvNotSet()
    {
        $registry = new EnvVariableRegistry();
        $this->assertTrue($registry->boolValue('some.test.parameter', true));
    }

    public function testFalseValueWhenEnvNotSet()
    {
        $registry = new EnvVariableRegistry();
        $this->assertFalse($registry->boolValue('some.test.parameter', false));
    }

    public function testBoolValueTrueEnvSetWord()
    {
        $registry = new EnvVariableRegistry();
        putenv('DD_SOME_TEST_PARAMETER=tRuE   ');
        $this->assertTrue($registry->boolValue('some.test.parameter', false));
    }

    public function testBoolValueTrueEnvSetNumber()
    {
        $registry = new EnvVariableRegistry();
        putenv('DD_SOME_TEST_PARAMETER=1   ');
        $this->assertTrue($registry->boolValue('some.test.parameter', false));
    }

    public function testBoolValueFalseEnvSetWord()
    {
        $registry = new EnvVariableRegistry();
        putenv('DD_SOME_TEST_PARAMETER=fAlSe   ');
        $this->assertFalse($registry->boolValue('some.test.parameter', true));
    }

    public function testBoolValueFalseEnvSetNumber()
    {
        $registry = new EnvVariableRegistry();
        putenv('DD_SOME_TEST_PARAMETER=0   ');
        $this->assertFalse($registry->boolValue('some.test.parameter', true));
    }

    public function testInArrayNotSet()
    {
        $registry = new EnvVariableRegistry();
        $this->assertFalse($registry->inArray('some.test.parameter', 'name'));
    }

    public function testInArraySet()
    {
        $registry = new EnvVariableRegistry();
        putenv('DD_SOME_TEST_PARAMETER=value1,value2');
        $this->assertTrue($registry->inArray('some.test.parameter', 'value1'));
        $this->assertTrue($registry->inArray('some.test.parameter', 'value2'));
        $this->assertFalse($registry->inArray('some.test.parameter', 'value3'));
    }

    public function testInArrayCaseInsensitive()
    {
        $registry = new EnvVariableRegistry();
        putenv('DD_SOME_TEST_PARAMETER=vAlUe1,VaLuE2');
        $this->assertTrue($registry->inArray('some.test.parameter', 'value1'));
        $this->assertTrue($registry->inArray('some.test.parameter', 'value2'));
        $this->assertFalse($registry->inArray('some.test.parameter', 'value3'));
    }

    public function testInArrayWhiteSpaceBetweenDefinitions()
    {
        $registry = new EnvVariableRegistry();
        putenv('DD_SOME_TEST_PARAMETER= value1    ,     value2     ');
        $this->assertTrue($registry->inArray('some.test.parameter', 'value1'));
        $this->assertTrue($registry->inArray('some.test.parameter', 'value2'));
        $this->assertFalse($registry->inArray('some.test.parameter', 'value3'));
    }
}
