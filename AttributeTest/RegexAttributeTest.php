<?php

namespace Funddy\Yodo\AttributeTest;

use Funddy\Yodo\AttributeTest\AttributeTest;

class RegexAttributeTest implements AttributeTest
{
    private $pattern;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function test($value)
    {
        return preg_match($this->pattern, $value) === 1;
    }
}