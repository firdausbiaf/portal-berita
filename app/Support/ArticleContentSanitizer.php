<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class ArticleContentSanitizer
{
    /**
     * Allowed HTML tag names in lower-case.
     *
     * @var array<string>
     */
    protected const ALLOWED_TAGS = [
        'p', 'br', 'h2', 'h3', 'strong', 'b', 'em', 'i', 'u',
        'ul', 'ol', 'li', 'blockquote', 'a',
    ];

    /**
     * Disallowed dangerous tags whose text content must also be purged.
     *
     * @var array<string>
     */
    protected const DANGEROUS_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form',
        'input', 'button', 'svg', 'audio', 'video', 'canvas',
    ];

    /**
     * Allowed attributes specifically for <a> tags.
     *
     * @var array<string>
     */
    protected const ALLOWED_ANCHOR_ATTRIBUTES = [
        'href', 'title', 'target', 'rel',
    ];

    /**
     * Sanitize article HTML content.
     */
    public static function sanitize(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        $previousEntityLoader = libxml_use_internal_errors(true);

        $dom = new DOMDocument;
        $wrapped = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>'.$html.'</body></html>';

        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previousEntityLoader);

        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }

        self::cleanNode($body);

        $result = '';
        foreach ($body->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return trim($result);
    }

    /**
     * Recursively clean a DOM node.
     */
    protected static function cleanNode(DOMNode $node): void
    {
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof DOMElement) {
                $tagName = strtolower($child->tagName);

                if (! in_array($tagName, self::ALLOWED_TAGS, true)) {
                    if (in_array($tagName, self::DANGEROUS_TAGS, true)) {
                        $node->removeChild($child);
                    } else {
                        self::cleanNode($child);
                        while ($child->hasChildNodes()) {
                            $node->insertBefore($child->firstChild, $child);
                        }
                        $node->removeChild($child);
                    }

                    continue;
                }

                self::cleanAttributes($child);
                self::cleanNode($child);
            }
        }
    }

    /**
     * Clean attributes of an allowed element.
     */
    protected static function cleanAttributes(DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);
        $attributesToRemove = [];

        foreach ($element->attributes as $attr) {
            $attrName = strtolower($attr->nodeName);

            // Strip any on* attribute
            if (str_starts_with($attrName, 'on')) {
                $attributesToRemove[] = $attr->nodeName;

                continue;
            }

            // Strip style, class, id
            if (in_array($attrName, ['style', 'class', 'id'], true)) {
                $attributesToRemove[] = $attr->nodeName;

                continue;
            }

            if ($tagName === 'a') {
                if (! in_array($attrName, self::ALLOWED_ANCHOR_ATTRIBUTES, true)) {
                    $attributesToRemove[] = $attr->nodeName;
                }
            } else {
                $attributesToRemove[] = $attr->nodeName;
            }
        }

        foreach ($attributesToRemove as $attrName) {
            $element->removeAttribute($attrName);
        }

        // Special validation for <a>
        if ($tagName === 'a') {
            if ($element->hasAttribute('href')) {
                $href = trim($element->getAttribute('href'));
                if (! self::isSafeUrl($href)) {
                    $element->removeAttribute('href');
                }
            }

            if (strtolower($element->getAttribute('target')) === '_blank') {
                $rel = $element->getAttribute('rel');
                $relParts = preg_split('/\s+/', strtolower($rel), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                if (! in_array('noopener', $relParts, true)) {
                    $relParts[] = 'noopener';
                }
                if (! in_array('noreferrer', $relParts, true)) {
                    $relParts[] = 'noreferrer';
                }
                $element->setAttribute('rel', implode(' ', $relParts));
            }
        }
    }

    /**
     * Determine if a URL is safe for an href attribute.
     */
    protected static function isSafeUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        // Relative URLs starting with / or # or ?
        if (str_starts_with($url, '/') || str_starts_with($url, '#') || str_starts_with($url, '?')) {
            return true;
        }

        // Reject if scheme is javascript, data, vbscript, or any non-http(s)
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme === false || $scheme === null) {
            if (preg_match('/^[a-z0-9+.-]+:/i', $url)) {
                return false;
            }

            return true;
        }

        return in_array(strtolower($scheme), ['http', 'https'], true);
    }
}
