<?php

namespace Funddy\Yodo\AttributeTest;

use Funddy\Yodo\AttributeTest\AttributeTest;

class EqualAttributeTest implements AttributeTest
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function test($value)
    {
        return $this->value === $value;
    }
}