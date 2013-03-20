<?php

namespace Funddy\Yodo\Sanitizer;

use Funddy\Yodo\MarkupFixer\MarkupFixer;
use Funddy\Yodo\Rule\RuleAttribute;
use Funddy\Yodo\Rule\RuleSet;

class HtmlSanitizer
{
    private $ruleSet;
    private $markupFixer;

    public function __construct(RuleSet $ruleSet, MarkupFixer $markupFixer)
    {
        $this->ruleSet = $ruleSet;
        $this->markupFixer = $markupFixer;
    }

    public function sanitize($markup)
    {
        $doc = $this->createDocumentAndLoad($markup);

        $xpath = new \DOMXPath($doc);
        $rootNode = $xpath->query('/html/body')->item(0);

        if ($rootNode->hasChildNodes()) {
            $this->sanitizeChildNode($rootNode, true);
        }

        return $this->exportNode($rootNode);
    }

    private function exportNode($rootNode)
    {
        $output = '';
        foreach ($rootNode->childNodes as $childNode) {
            $output .= $childNode->ownerDocument->saveHtml($childNode);
        }
        return trim($output);
    }

    public function createDocumentAndLoad($html)
    {
        $layout = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title></title>
    </head>
    <body>
    $html
    </body>
</html>
HTML;

        $layout = $this->markupFixer->repair($layout);

        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadHTML($layout);

        return $doc;
    }

    private function sanitizeChildNode(\DOMNode $node, $isRootNode)
    {
        if (!$isRootNode && $this->isNotTextNode($node)) {
            if (!$this->ruleSet->hasNoRule($node->nodeName))
                $this->ruleSet->getRule($node->nodeName)->sanitize($node);
        }

        if (!$node->hasChildNodes()) return;

        for ($i = 0; $i < $node->childNodes->length; ) {
            $childNode = $node->childNodes->item($i);

            $this->sanitizeChildNode($childNode, false);

            if ($this->isInvalidNode($childNode)) {
                $node->removeChild($childNode);
                continue;
            }

            $i++;
        }
    }

    private function isNotTextNode($node)
    {
        return $node->nodeType !== XML_TEXT_NODE;
    }

    private function isInvalidNode($node)
    {
        return $this->isNotTextNode($node) && (
            $this->ruleSet->hasNoRule($node->nodeName) ||
            $this->ruleSet->getRule($node->nodeName)->isInvalid($node)
        );
    }
}