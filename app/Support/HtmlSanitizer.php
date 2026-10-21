<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{

    private const ALLOWED = [
        'p' => ['style', 'class'],
        'br' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'ol' => [], 'ul' => [],
        'li' => ['style', 'class'],
        'h2' => ['style', 'class'], 'h3' => ['style', 'class'],
        'blockquote' => [],
        'span' => ['style'],
        'a' => ['href'],
    ];

    
    private const DROP = [
        'script', 'style', 'iframe', 'object', 'embed', 'applet', 'form', 'input', 'button',
        'textarea', 'select', 'svg', 'math', 'link', 'meta', 'title', 'head', 'base',
        'noscript', 'template', 'frame', 'frameset',
    ];

    private const COLOR = '/^(#[0-9a-f]{3,8}|rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*[0-9.]+\s*)?\)|[a-z]{3,20})$/i';

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $prev = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $dom->getElementsByTagName('div')->item(0);
        if (! $root) {
            return '';
        }

        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }


            if (! $child instanceof DOMElement) {
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);
                continue;
            }


            if (! isset(self::ALLOWED[$tag])) {
                self::walk($child);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            self::cleanAttributes($child, $tag);
            self::walk($child);
        }
    }

    private static function cleanAttributes(DOMElement $el, string $tag): void
    {
        $allowed = self::ALLOWED[$tag];

        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->nodeName);

            if (! in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }

            $value = trim($attr->nodeValue);
            $safe = match ($name) {
                'style' => self::cleanStyle($value),
                'class' => self::cleanClass($value),
                'href' => self::cleanHref($value),
                default => '',
            };

            if ($safe === '') {
                $el->removeAttribute($attr->nodeName);
            } else {
                $el->setAttribute($attr->nodeName, $safe);
            }
        }

        if ($tag === 'a' && $el->hasAttribute('href')) {
            $el->setAttribute('target', '_blank');
            $el->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }


    private static function cleanStyle(string $value): string
    {
        $keep = [];

        foreach (explode(';', $value) as $decl) {
            if (! str_contains($decl, ':')) {
                continue;
            }
            [$prop, $val] = array_map('trim', explode(':', $decl, 2));
            $prop = strtolower($prop);

            if (in_array($prop, ['color', 'background-color'], true) && preg_match(self::COLOR, $val)) {
                $keep[] = $prop . ': ' . $val;
            }
        }

        return implode('; ', $keep);
    }

    private static function cleanClass(string $value): string
    {
        $keep = [];

        foreach (preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY) as $class) {
            if (preg_match('/^ql-align-(center|right|justify)$/', $class)) {
                $keep[] = $class;
            }
        }

        return implode(' ', $keep);
    }


    private static function cleanHref(string $value): string
    {
        return preg_match('/^(https?:\/\/|mailto:)/i', $value) ? $value : '';
    }
}