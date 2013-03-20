<?php

namespace Funddy\Yodo\Tests\Rule;

use Funddy\Yodo\AttributeTest\InAttributeTest;

class InAttributeTestTest extends \PHPUnit_Framework_TestCase
{
    private $test;

    protected function setUp()
    {
        $this->test = new InAttributeTest(array('foo', 'var', 'test'));
    }

    /**
     * @test
     * @dataProvider inValues
     */
    public function shouldBeIn($value)
    {
        $passes = $this->test->test($value);

        $this->assertThat($passes, $this->isTrue());
    }

    public function inValues()
    {
        return array(
            array('foo'),
            array('var'),
            array('test')
        );
    }

    /**
     * @test
     */
    public function shouldNotBeIn()
    {
        $passes = $this->test->test('fooo');

        $this->assertThat($passes, $this->isFalse());
    }
}