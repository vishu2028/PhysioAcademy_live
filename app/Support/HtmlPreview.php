<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use RuntimeException;

final class HtmlPreview
{
    public static function half(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorHandling = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="html-preview-root">'.$html.'</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorHandling);
        }

        if (! $loaded) {
            throw new RuntimeException('Unable to parse topic description HTML.');
        }

        $nodes = (new DOMXPath($document))->query('//*[@id="html-preview-root"]');
        $root = $nodes === false ? null : $nodes->item(0);

        if (! $root) {
            throw new RuntimeException('Unable to locate parsed topic description HTML.');
        }

        $remaining = (int) ceil(mb_strlen($root->textContent) / 2);
        $truncated = false;
        $ellipsisAdded = false;
        $preview = $document->createElement('div');

        foreach (iterator_to_array($root->childNodes) as $child) {
            self::appendPreviewNode(
                $child,
                $preview,
                $document,
                $remaining,
                $truncated,
                $ellipsisAdded
            );

            if ($truncated) {
                break;
            }
        }

        if ($truncated && ! $ellipsisAdded) {
            $preview->appendChild($document->createTextNode('...'));
        }

        $result = '';

        foreach ($preview->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    private static function appendPreviewNode(
        DOMNode $source,
        DOMNode $destination,
        DOMDocument $document,
        int &$remaining,
        bool &$truncated,
        bool &$ellipsisAdded
    ): void {
        if ($source instanceof DOMText) {
            $text = $source->nodeValue ?? '';
            $length = mb_strlen($text);

            if ($length > $remaining) {
                $destination->appendChild(
                    $document->createTextNode(mb_substr($text, 0, $remaining).'...')
                );
                $truncated = true;
                $ellipsisAdded = true;

                return;
            }

            $destination->appendChild($source->cloneNode(true));
            $remaining -= $length;

            return;
        }

        if ($source instanceof DOMElement && strtolower($source->tagName) === 'table') {
            $length = mb_strlen($source->textContent);
            $destination->appendChild($source->cloneNode(true));

            if ($length > $remaining) {
                $truncated = true;
                $remaining = 0;

                return;
            }

            $remaining -= $length;

            return;
        }

        if (! $source instanceof DOMElement) {
            $destination->appendChild($source->cloneNode(true));

            return;
        }

        $copy = $source->cloneNode(false);
        $destination->appendChild($copy);

        foreach (iterator_to_array($source->childNodes) as $child) {
            self::appendPreviewNode(
                $child,
                $copy,
                $document,
                $remaining,
                $truncated,
                $ellipsisAdded
            );

            if ($truncated) {
                break;
            }
        }
    }
}
