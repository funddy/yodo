<?php

namespace Funddy\Yodo\Tests\Rule;

use Funddy\Yodo\Rule\Rule;
use Mockery as m;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    private $ruleSet;
    private $rule;
    private $element;

    protected function setUp()
    {
        $this->ruleSet = m::mock('Funddy\Yodo\Rule\RuleSet');
        $this->rule = new Rule($this->ruleSet);

        $dom = new \DOMDocument('1.0', 'utf-8');
        $this->element = new \DOMElement('element', 'content');
        $dom->appendChild($this->element);
    }

    /**
     * @test
     */
    public function shouldBeInvalidBecauseNodeIsEmpty()
    {
        $this->element->nodeValue = '';

        $isInvalid = $this->rule->isInvalid($this->element);

        $this->assertThat($isInvalid, $this->isTrue());
    }

    /**
     * @test
     */
    public function shouldBeValidBecauseNodeIsNotEmpty()
    {
        $isInvalid = $this->rule->isInvalid($this->element);

        $this->assertThat($isInvalid, $this->isFalse());
    }

    /**
     * @test
     */
    public function shouldBeInvalidBecauseNodeIsNotEmpty()
    {
        $this->rule->toBeEmpty();

        $isInvalid = $this->rule->isInvalid($this->element);

        $this->assertThat($isInvalid, $this->isTrue());
    }

    /**
     * @test
     */
    public function shouldBeValidBecauseNodeIsEmpty()
    {
        $this->element->nodeValue = '';
        $this->rule->toBeEmpty();

        $isInvalid = $this->rule->isInvalid($this->element);

        $this->assertThat($isInvalid, $this->isFalse());
    }

    /**
     * @test
     */
    public function emptyOrNotWithEmptyValueShouldBeValid()
    {
        $this->element->nodeValue = '';
        $this->rule->toBeEmptyOrNot();

        $isInvalid = $this->rule->isInvalid($this->element);

        $this->assertThat($isInvalid, $this->isFalse());
    }

    /**
     * @test
     */
    public function emptyOrNotWithValueShouldBeValid()
    {
        $this->rule->toBeEmptyOrNot();

        $isInvalid = $this->rule->isInvalid($this->element);

        $this->assertThat($isInvalid, $this->isFalse());
    }

    /**
     * @test
     */
    public function shouldBeInvalidBecauseNotAllowedChildren()
    {
        $child = new \DOMElement('element', 'content');
        $this->element->appendChild($child);

        $isInvalid = $this->rule->isInvalid($this->element);

        $this->assertThat($isInvalid, $this->isTrue());
    }

    /**
     * @test
     */
    public function childShouldBeAllowed()
    {
        $this->rule->allowedChildren(array('element'));
        $child = new \DOMElement('element', 'content');
        $this->element->appendChild($child);

        $isInvalid = $this->rule->isInvalid($this->element);

        $this->assertThat($isInvalid, $this->isFalse());
    }

    /**
     * @test
     */
    public function endShouldReturnRuleSet()
    {
        $ruleSet = $this->rule->end();

        $this->assertThat($ruleSet, $this->identicalTo($this->ruleSet));
    }
}