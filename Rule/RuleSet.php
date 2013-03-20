<?php

namespace Funddy\Yodo\Rule;

class RuleSet
{
    private $rules = array();

    public function rule($tag)
    {
        $rule = new Rule($this);
        $this->rules[$tag] = $rule;
        return $rule;
    }

    public function hasNoRule($tag)
    {
        return !isset($this->rules[$tag]);
    }

    public function getRule($tag)
    {
        return $this->rules[$tag];
    }
}