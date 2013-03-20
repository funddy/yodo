Yodo
====

[![Build Status](https://secure.travis-ci.org/funddy/yodo.png?branch=master)](http://travis-ci.org/funddy/yodo)

Simple, fast and customizable HTML sanitizer.

Setup and Configuration
-----------------------
Add the following to your composer.json file:
```json
{
    "require": {
        "funddy/yodo": "1.0.*"
    }
}
```

Update the vendor libraries:

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

Usage
-----

```php
<?php

require 'vendor/autoload.php';

use Funddy\Yodo\MarkupFixer\TidyMarkupFixer;
use Funddy\Yodo\Rule\RuleSet;
use Funddy\Yodo\Sanitizer\HtmlSanitizer;

$rules = new RuleSet();
$rules
    ->rule('p')
        ->attribute('class')
            ->in(array('class1', 'class2'))
            ->optional()
            ->trim()
            ->end()
        ->allowedChildren(array('a'))
        ->end()
    ->rule('br')
        ->toBeEmpty()
        ->end()
    ->rule('a')
        ->attribute('href')->like('/^http:\/\/.*?$/')->end()
        ->attribute('rel')->equals('nofollow')->optional()->end()
        ->end();

$sanitizer = new HtmlSanitizer($rules, new TidyMarkupFixer());

$html = <<<HTML
<p>This an awesome paragraph!<a href="javascript:alert('oh')">with evil links inside!</a></p>
<h3>This tag is not allowed!</h3>
<br/>
<a href="http://example.com/">Valid link</a>
<script>
    alert('Supa evil!')
</script>
<p class=" class1 ">Paragraph with <a href="http://example.com/">valid link</a></p>
Awesome!
HTML;

echo $sanitizer->sanitize($html);
```
The output will be
```html
<p>This an awesome paragraph!</p><br><a href="http://example.com/">Valid link</a><p class="class1">Paragraph with <a href="http://example.com/">valid link</a></p>
```