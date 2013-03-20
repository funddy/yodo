<?php

namespace Funddy\Yodo\AttributeTest;

use Funddy\Yodo\AttributeTest\AttributeTest;

class InAttributeTest implements AttributeTest
{
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function test($value)
    {
        return in_array($value, $this->values);
    }
}