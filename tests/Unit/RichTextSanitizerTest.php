<?php

namespace Tests\Unit;

use App\Services\RichTextSanitizer;
use PHPUnit\Framework\TestCase;

class RichTextSanitizerTest extends TestCase
{
    private RichTextSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new RichTextSanitizer;
    }

    public function test_it_removes_dangerous_tags_attributes_and_urls(): void
    {
        $result = $this->sanitizer->sanitize(
            '<script>alert(1)</script><p style="color:red" onclick="bad()">Aman</p><a href="javascript:bad()">Buruk</a>',
        );

        $this->assertStringNotContainsString('script', $result);
        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringNotContainsString('style=', $result);
        $this->assertStringNotContainsString('javascript:', $result);
    }

    public function test_it_preserves_safe_formatting_and_http_links(): void
    {
        $result = $this->sanitizer->sanitize(
            '<p><strong>Bold</strong> <em>Italic</em></p><ul><li>Item</li></ul><a href="https://example.com">Link</a>',
        );

        $this->assertStringContainsString('<strong>Bold</strong>', $result);
        $this->assertStringContainsString('<ul><li>Item</li></ul>', $result);
        $this->assertStringContainsString('href="https://example.com"', $result);
        $this->assertStringContainsString('target="_blank"', $result);
        $this->assertStringContainsString('rel="noopener noreferrer"', $result);
    }
}
