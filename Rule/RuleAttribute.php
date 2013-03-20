<?php

namespace Funddy\Yodo\Rule;

use Funddy\Yodo\AttributeTest\EqualAttributeTest;
use Funddy\Yodo\AttributeTest\InAttributeTest;
use Funddy\Yodo\AttributeTest\RegexAttributeTest;

class RuleAttribute
{
    private $rule;
    private $name;
    private $test;
    private $mandatory = true;
    private $trim = false;
    private $repair = false;
    private $repairValue;

    public function __construct(Rule $rule, $name)
    {
        $this->rule = $rule;
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function repair($value)
    {
        $this->repair = true;
        $this->repairValue = $value;
        return $this;
    }

    public function like($value)
    {
        $this->test = new RegexAttributeTest($value);
        return $this;
    }

    public function equals($value)
    {
        $this->test = new EqualAttributeTest($value);
        return $this;
    }

    public function in(array $values)
    {
        $this->test = new InAttributeTest($values);
        return $this;
    }

    private function test($value)
    {
        return $this->test->test($value);
    }

    public function optional()
    {
        $this->mandatory = false;
        return $this;
    }

    public function trim()
    {
        $this->trim = true;
        return $this;
    }

    public function isMandatoryAndIsInvalid(\DOMNode $node)
    {
        return
            $this->isMandatory() && (
                !$node->hasAttribute($this->name) ||
                !$this->test($node->getAttribute($this->name))
            );
    }

    private function haveToTrim()
    {
        return $this->trim === true;
    }

    private function isMandatory()
    {
        return $this->mandatory === true;
    }

    public function isOptionalAndIsInvalid(\DOMNode $node)
    {
        return
            !$this->isMandatory() &&
            $node->hasAttribute($this->name) &&
            !$this->test($node->getAttribute($this->name));
    }

    public function sanitize(\DOMNode $node)
    {
        if ($this->haveToTrim() && $node->hasAttribute($this->name)) {
            $node->setAttribute($this->name, trim($node->getAttribute($this->name)));
        }

        if ($this->repair && !$node->hasAttribute($this->name)) {
            $node->setAttribute($this->name, $this->repairValue);
        }
    }

    public function end()
    {
        return $this->rule;
    }
}