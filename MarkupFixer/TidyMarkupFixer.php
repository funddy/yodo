<?php

namespace Funddy\Yodo\MarkupFixer;

class TidyMarkupFixer implements MarkupFixer
{
    private static $config = array(
        'wrap' => 0,
        'lower-literals' => true,
        'preserve-entities' => true,
        'drop-empty-paras' => false
    );

    public function repair($markup)
    {
        $tidy = new \tidy();

        $tidy->parseString($markup, self::$config, 'utf8');

        $tidy->cleanRepair();

        return $tidy.'';
    }
}