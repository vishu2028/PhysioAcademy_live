<?php

namespace Tests\Unit;

use App\Support\HtmlPreview;
use DOMDocument;
use PHPUnit\Framework\TestCase;

class HtmlPreviewTest extends TestCase
{
    public function test_preview_closes_fragment_markup_and_keeps_a_table_whole(): void
    {
        $html = '<p>Start text.</p><figure class="table"><table>'
            .'<thead><tr><th>Example</th><th>Explanation</th></tr></thead>'
            .'<tbody><tr><td>Term one</td><td>First explanation.</td></tr>'
            .'<tr><td>Term two</td><td>Second explanation.</td></tr></tbody>'
            .'</table></figure><p>Content after the table.</p>';

        $preview = HtmlPreview::half($html);

        $this->assertStringContainsString('</table></figure>', $preview);
        $this->assertStringContainsString('Second explanation.', $preview);
        $this->assertStringNotContainsString('Content after the table.', $preview);

        $document = new DOMDocument;
        $previousErrorHandling = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML($preview);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorHandling);
        }

        $this->assertTrue($loaded);
        $this->assertSame(3, $document->getElementsByTagName('tr')->length);
    }
}
