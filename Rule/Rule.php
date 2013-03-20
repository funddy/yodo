<?php

namespace Funddy\Yodo\Rule;

use Funddy\Yodo\Rule\RuleAttribute;

class Rule
{
    const EMPTY_VALUE = '';

    private $ruleSet;
    private $empty = false;
    private $notEmpty = true;
    private $attributes = array();
    private $allowedChildren = array();

    public function __construct(RuleSet $ruleSet)
    {
        $this->ruleSet = $ruleSet;
    }

    public function allowedChildren(array $allowedChildren)
    {
        $this->allowedChildren = $allowedChildren;
        return $this;
    }

    public function toBeEmpty()
    {
        $this->empty = true;
        $this->notEmpty = false;
        return $this;
    }

    public function toBeEmptyOrNot()
    {
        $this->empty = true;
        $this->notEmpty = true;
        return $this;
    }

    public function attribute($name)
    {
        $attribute = new RuleAttribute($this, $name);
        $this->attributes[$name] = $attribute;
        return $attribute;
    }

    public function isInvalid(\DOMNode $node)
    {
        return
            $this->nodeShouldBeEmptyAndIsNot($node) ||
            $this->nodeShouldNotBeEmptyAndItIs($node) ||
            $this->nodeHasNotAllowedChildren($node) ||
            $this->hasInvalidAttributes($node);
    }

    private function nodeShouldBeEmptyAndIsNot(\DOMNode $node)
    {
        return $this->isEmptyConfigured() && $this->isEmpty() && !$this->nodeIsEmpty($node);
    }

    private function isEmptyConfigured()
    {
        return
            ($this->isEmpty() && !$this->isNotEmpty()) ||
            (!$this->isEmpty() && $this->isNotEmpty());
    }

    private function isEmpty()
    {
        return $this->empty === true;
    }

    private function isNotEmpty()
    {
        return $this->notEmpty === true;
    }

    private function nodeIsEmpty(\DOMNode $node)
    {
        return trim($node->nodeValue) === self::EMPTY_VALUE;
    }

    private function nodeShouldNotBeEmptyAndItIs(\DOMNode $node)
    {
        return $this->isEmptyConfigured() && $this->isNotEmpty() && $this->nodeIsEmpty($node);
    }

    private function nodeHasNotAllowedChildren(\DOMNode $node)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType !== XML_TEXT_NODE && $this->childNodeIsNotAllowed($childNode)) {
                return true;
            }
        }
        return false;
    }

    private function childNodeIsNotAllowed(\DOMNode $node)
    {
        return !in_array($node->nodeName, $this->allowedChildren);
    }

    private function hasInvalidAttributes(\DOMNode $node)
    {
        foreach ($this->attributes as $attributeConfig) {
            if ($attributeConfig->isMandatoryAndIsInvalid($node)) {
                return true;
            }
        }
        return false;
    }

    private function removeNotConfiguredAttributes(\DOMNode $node)
    {
        for ($i = 0; $i < $node->attributes->length;) {
            $attribute = $node->attributes->item($i);

            if ($this->hasNoAttribute($attribute->nodeName)) {
                $node->removeAttributeNode($attribute);
                continue;
            }

            $i++;
        }
    }

    private function hasNoAttribute($name)
    {
        return !isset($this->attributes[$name]);
    }

    public function sanitize(\DOMNode $node)
    {
        if ($node->hasChildNodes()) {
            for ($i = 0; $i < $node->childNodes->length; ) {
                $childNode = $node->childNodes->item($i);

                if (($this->childNodeIsNotAllowed($childNode) && $childNode->nodeType !== XML_TEXT_NODE)) {
                    $node->removeChild($childNode);
                    continue;
                }

                $i++;
            }
        }

        $this->sanitizeAttributes($node);
    }

    private function sanitizeAttributes(\DOMNode $childNode)
    {
        if ($childNode->nodeType === XML_TEXT_NODE) return;

        foreach ($this->attributes as $specAttribute) {
            $specAttribute->sanitize($childNode);

            if ($specAttribute->isOptionalAndIsInvalid($childNode)) {
                $childNode->removeAttributeNode($childNode->getAttributeNode($specAttribute->getName()));
            }
        }

        $this->removeNotConfiguredAttributes($childNode);
    }

    public function end()
    {
        return $this->ruleSet;
    }
}