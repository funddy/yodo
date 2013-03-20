<?php

namespace Funddy\Yodo\Tests\Rule;

use Funddy\Yodo\Rule\RuleSet;

class RuleSetTest extends \PHPUnit_Framework_TestCase
{
    private $ruleSet;

    protected function setUp()
    {
        $this->ruleSet = new RuleSet();
    }

    /**
     * @test
     */
    public function shouldNotHaveRule()
    {
        $hasNoRule = $this->ruleSet->hasNoRule('foo');

        $this->assertThat($hasNoRule, $this->isTrue());
    }

    /**
     * @test
     */
    public function shouldHaveRule()
    {
        $this->ruleSet->rule('foo');

        $hasNoRule = $this->ruleSet->hasNoRule('foo');

        $this->assertThat($hasNoRule, $this->isFalse());
    }

    /**
     * @test
     */
    public function getsRule()
    {
        $this->ruleSet->rule('foo');

        $rule = $this->ruleSet->getRule('foo');

        $this->assertThat($rule, $this->isInstanceOf('Funddy\Yodo\Rule\Rule'));
    }
}