<?php

namespace Funddy\Yodo\Tests\Rule;

use Funddy\Yodo\AttributeTest\EqualAttributeTest;

class EqualAttributeTestTest extends \PHPUnit_Framework_TestCase
{
    private $test;

    protected function setUp()
    {
        $this->test = new EqualAttributeTest('foo');
    }

    /**
     * @test
     */
    public function shouldBeEqual()
    {
        $passes = $this->test->test('foo');

        $this->assertThat($passes, $this->isTrue());
    }

    /**
     * @test
     */
    public function shouldNotBeEqual()
    {
        $passes = $this->test->test('var');

        $this->assertThat($passes, $this->isFalse());
    }
}