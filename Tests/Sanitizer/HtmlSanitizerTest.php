<?php

namespace Funddy\Yodo\Tests\Sanitizer;

use Funddy\Yodo\MarkupFixer\TidyMarkupFixer;
use Funddy\Yodo\Rule\RuleSet;
use Funddy\Yodo\Sanitizer\HtmlSanitizer;
use Mockery as m;

class HtmlSanitizerTest extends \PHPUnit_Framework_TestCase
{
    private $htmlSanitizer;

    protected function setUp()
    {
        $ruleSet = new RuleSet();
        $ruleSet
            ->rule('h1')
                ->allowedChildren(array('a', 'br', 'strong'))
                ->end()
            ->rule('h2')
                ->allowedChildren(array('a', 'br', 'strong'))
                ->end()
            ->rule('br')
                ->toBeEmpty()
                ->end()
            ->rule('a')
                ->attribute('target')->equals('_blank')->repair('_blank')->end()
                ->attribute('href')->like('#^http://.*$#')->end()
                ->attribute('rel')->equals('nofollow')->repair('nofollow')->end()
                ->attribute('style')->equals('color: #f00')->optional()->end()
                ->allowedChildren(array('strong', 'br'))
                ->end()
            ->rule('strong')
                ->allowedChildren(array('a', 'br'))
                ->end()
            ->rule('p')
                ->allowedChildren(array('h1', 'h2', 'a', 'br'))
                ->end();

        $this->htmlSanitizer = new HtmlSanitizer($ruleSet, new TidyMarkupFixer());
    }

    /**
     * @test
     * @dataProvider repairedAttributes
     */
    public function repairsAttributes($html, $expected)
    {
        $sanitizedHtml = $this->htmlSanitizer->sanitize($html);

        $this->assertThat($sanitizedHtml, $this->identicalTo($expected));
    }

    public function repairedAttributes()
    {
        return array(
            array(
                '<a href="http://google.es/">test</a>',
                '<a href="http://google.es/" target="_blank" rel="nofollow">test</a>'
            )
        );
    }

    /**
     * @test
     * @dataProvider allowedHtml
     */
    public function htmlShouldBeSanitizedLikeOriginal($html)
    {
        $sanitizedHtml = $this->htmlSanitizer->sanitize($html);

        $this->assertThat($sanitizedHtml, $this->identicalTo($html));
    }

    public function allowedHtml()
    {
        return array(
            array('<h1>Test</h1>'),
            array('<h1>Test<a href="http://google.es/" rel="nofollow" target="_blank">test</a><br><strong>test</strong>'."\n".'</h1>'),
            array('<h2>Test</h2>'),
            array('<h2>Test<a href="http://google.es/" rel="nofollow" target="_blank">test</a><br><strong>test</strong>'."\n".'</h2>'),
            array('<br>'),
            array('<a href="http://google.es/" rel="nofollow" target="_blank">test</a>'),
            array('<a href="http://google.es/" rel="nofollow" target="_blank" style="color: #f00">test</a>'),
            array('<a href="http://google.es/" rel="nofollow" target="_blank">test<br><strong>test</strong></a>'),
            array('<strong>test</strong>'),
            array('<strong>test<a href="http://google.es/" rel="nofollow" target="_blank">test</a><br></strong>'),
            array('<p>test</p>'),
        );
    }

    /**
     * @test
     * @dataProvider notAllowedHtml
     */
    public function shouldRemoveNotAllowedTags($html)
    {
        $sanitizedHtml = $this->htmlSanitizer->sanitize($html);

        $this->assertThat($sanitizedHtml, $this->isEmpty());
    }

    public function notAllowedHtml()
    {
        return array(
            array('<h3></h3>')
        );
    }

    /**
     * @test
     * @dataProvider notConfiguredChildren
     */
    public function shouldRemoveNotConfiguredChildren($html, $expected)
    {
        $sanitizedHtml = $this->htmlSanitizer->sanitize($html);

        $this->assertThat($sanitizedHtml, $this->identicalTo($expected));
    }

    public function notConfiguredChildren()
    {
        return array(
            array('<h1>test<em>foo</em></h1>', '<h1>test</h1>'),
            array('<strong>test<em>foo</em></strong>', '<strong>test</strong>')
        );
    }

    /**
     * @test
     * @dataProvider tagsWithInvalidAttributes
     */
    public function shouldRemoveElementsWithInvalidAttributes($html)
    {
        $sanitizedHtml = $this->htmlSanitizer->sanitize($html);

        $this->assertThat($sanitizedHtml, $this->isEmpty());
    }

    public function tagsWithInvalidAttributes()
    {
        return array(
            array('<a style="color: #f00">test</a>'),
            array('<a href="http://google.es/" rel="nofollow" target="INVALID">test</a>'),
            array('<a href="http://google.es/" rel="nofollow" target="">test</a>'),
        );
    }

    /**
     * @test
     */
    public function shouldRemoveInvalidOptionalAttributes()
    {
        $html = '<a href="http://google.es/" rel="nofollow" target="_blank" style="INVALID">test</a>';

        $sanitizedHtml = $this->htmlSanitizer->sanitize($html);

        $this->assertThat($sanitizedHtml, $this->identicalTo('<a href="http://google.es/" rel="nofollow" target="_blank">test</a>'));
    }

    /**
     * @test
     */
    public function shouldNotRemoveValidOptionalAttributes()
    {
        $html = '<a href="http://google.es/" rel="nofollow" target="_blank" style="color: #f00">test</a>';

        $sanitizedHtml = $this->htmlSanitizer->sanitize($html);

        $this->assertThat($sanitizedHtml, $this->identicalTo('<a href="http://google.es/" rel="nofollow" target="_blank" style="color: #f00">test</a>'));
    }

    /**
     * @test
     */
    public function shouldRemoveAllNotConfiguredAttributes()
    {
        $html = '<a href="http://google.es/" rel="nofollow" target="_blank" style="color: #f00" data-foo="foo" data-foo2="foo2">test</a>';

        $sanitizedHtml = $this->htmlSanitizer->sanitize($html);

        $this->assertThat($sanitizedHtml, $this->identicalTo('<a href="http://google.es/" rel="nofollow" target="_blank" style="color: #f00">test</a>'));
    }

    /**
     * @test
     */
    public function evilCodeShouldBeRemoved()
    {
        $html = <<<HTML
<h1>This is a heading<b>with a not allowed b!</b></h1>
<a href="http://google.es/" rel="nofollow" target="_blank" style="color: #f00" data-foo="foo" data-foo2="foo2">test</a> sample text
<p onclick="javascript:alert('oooh')">Irrelevant text<script type="text/javascript">document.write('evil!')</script></p>
<script type="text/javascript">
    alert("this is some evil script!");
</script>
<a href="javascript:alert('oooh')" onclick="alert('ahaha!')">evil link!</a>
HTML;

        $expected = <<<HTML
<h1>This is a heading</h1>
<a href="http://google.es/" rel="nofollow" target="_blank" style="color: #f00">test</a> sample text
<p>Irrelevant text</p>
HTML;

        $sanitizedHtml = $this->htmlSanitizer->sanitize($html);

        $this->assertThat($sanitizedHtml, $this->identicalTo($expected));
    }

    /**
     * @test
     */
    public function sanitizesWithUtf8Charset()
    {
        $input = 'áéíóúñ';

        $output = $this->htmlSanitizer->sanitize($input);

        $this->assertThat($output, $this->identicalTo('áéíóúñ'));
    }
}