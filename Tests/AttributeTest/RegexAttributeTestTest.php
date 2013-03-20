<?php

namespace Funddy\Yodo\Tests\Rule;

use Funddy\Yodo\AttributeTest\RegexAttributeTest;

class RegexAttributeTestTest extends \PHPUnit_Framework_TestCase
{
    private $test;

    protected function setUp()
    {
        $this->test = new RegexAttributeTest('/^foo$/');
    }

    /**
     * @test
     */
    public function shouldPassRegex()
    {
        $passes = $this->test->test('foo');

        $this->assertThat($passes, $this->isTrue());
    }

    /**
     * @test
     */
    public function shouldNotPassRegex()
    {
        $passes = $this->test->test('var');

        $this->assertThat($passes, $this->isFalse());
    }
}