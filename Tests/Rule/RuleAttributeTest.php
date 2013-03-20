<?php

namespace Funddy\Yodo\Tests\Rule;

use Funddy\Yodo\Rule\RuleAttribute;
use Mockery as m;

class RuleAttributeTest extends \PHPUnit_Framework_TestCase
{
    const IRRELEVANT_ATTRIBUTE_NAME = 'X';
    const IRRELEVANT_ATTRIBUTE_VALUE = 'XX';

    private $rule;
    private $ruleAttribute;
    private $element;

    protected function setUp()
    {
        $this->rule = m::mock('Funddy\Yodo\Rule\Rule');
        $this->ruleAttribute = new RuleAttribute($this->rule, self::IRRELEVANT_ATTRIBUTE_NAME);

        $dom = new \DOMDocument('1.0', 'utf-8');
        $this->element = new \DOMElement('element');
        $dom->appendChild($this->element);
    }

    /**
     * @test
     */
    public function mandatoryAndNotFoundShouldBeInvalid()
    {
        $isMandatoryAndIsInvalid = $this->ruleAttribute->isMandatoryAndIsInvalid($this->element);

        $this->assertThat($isMandatoryAndIsInvalid, $this->isTrue());
    }

    /**
     * @test
     */
    public function optionalShouldNotBeMandatoryInvalid()
    {
        $this->ruleAttribute->optional();

        $isMandatoryAndIsInvalid = $this->ruleAttribute->isMandatoryAndIsInvalid($this->element);

        $this->assertThat($isMandatoryAndIsInvalid, $this->isFalse());
    }

    /**
     * @test
     */
    public function attributeIsMandatoryAndShouldBeInvalid()
    {
        $this->ruleAttribute->equals('invalid');
        $this->element->setAttribute(self::IRRELEVANT_ATTRIBUTE_NAME, self::IRRELEVANT_ATTRIBUTE_VALUE);

        $isMandatoryAndIsInvalid = $this->ruleAttribute->isMandatoryAndIsInvalid($this->element);

        $this->assertThat($isMandatoryAndIsInvalid, $this->isTrue());
    }

    /**
     * @test
     */
    public function attributeIsMandatoryAndShouldBeValid()
    {
        $this->ruleAttribute->equals(self::IRRELEVANT_ATTRIBUTE_VALUE);
        $this->element->setAttribute(self::IRRELEVANT_ATTRIBUTE_NAME, self::IRRELEVANT_ATTRIBUTE_VALUE);

        $isMandatoryAndIsInvalid = $this->ruleAttribute->isMandatoryAndIsInvalid($this->element);

        $this->assertThat($isMandatoryAndIsInvalid, $this->isFalse());
    }

    /**
     * @test
     */
    public function inTestOnMandatoryAttributeShouldBeValid()
    {
        $testValues = array('value1', 'value2', 'value3');
        $this->ruleAttribute->in($testValues);

        foreach ($testValues as $testValue){
            $this->element->setAttribute(self::IRRELEVANT_ATTRIBUTE_NAME, $testValue);

            $isMandatoryAndIsInvalid = $this->ruleAttribute->isMandatoryAndIsInvalid($this->element);

            $this->assertThat($isMandatoryAndIsInvalid, $this->isFalse());
        }
    }

    /**
     * @test
     */
    public function likeTestOnMandatoryAttributeShouldBeValid()
    {
        $this->ruleAttribute->like('/^foo$/');
        $this->element->setAttribute(self::IRRELEVANT_ATTRIBUTE_NAME, 'foo');

        $isMandatoryAndIsInvalid = $this->ruleAttribute->isMandatoryAndIsInvalid($this->element);

        $this->assertThat($isMandatoryAndIsInvalid, $this->isFalse());
    }

    /**
     * @test
     */
    public function inTestOnOptionalAttributeShouldBeValid()
    {
        $testValues = array('value1', 'value2', 'value3');
        $this->ruleAttribute->in($testValues)->optional();

        foreach ($testValues as $testValue){
            $this->element->setAttribute(self::IRRELEVANT_ATTRIBUTE_NAME, $testValue);

            $isOptionalAndIsInvalid = $this->ruleAttribute->isOptionalAndIsInvalid($this->element);

            $this->assertThat($isOptionalAndIsInvalid, $this->isFalse());
        }
    }

    /**
     * @test
     */
    public function likeTestOnOptionalAttributeShouldBeValid()
    {
        $this->ruleAttribute->like('/^foo$/')->optional();
        $this->element->setAttribute(self::IRRELEVANT_ATTRIBUTE_NAME, 'foo');

        $isOptionalAndIsInvalid = $this->ruleAttribute->isOptionalAndIsInvalid($this->element);

        $this->assertThat($isOptionalAndIsInvalid, $this->isFalse());
    }

    /**
     * @test
     */
    public function endShouldReturnRule()
    {
        $rule = $this->ruleAttribute->end();

        $this->assertThat($rule, $this->identicalTo($this->rule));
    }
}