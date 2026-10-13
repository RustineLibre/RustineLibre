<?php

declare(strict_types=1);

namespace App\Tests\Repairer\RichText;

use App\Repairers\Service\RichTextSanitizer;
use PHPUnit\Framework\TestCase;

class RichTextSanitizerTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function dangerousPayloads(): iterable
    {
        yield 'script' => ['<p>ok</p><script>alert(1)</script>'];
        yield 'img onerror' => ['<img src=x onerror=alert(1)>'];
        yield 'svg onload' => ['<svg onload=alert(1)></svg>'];
        yield 'p handler' => ['<p onclick="alert(1)">text</p>'];
        yield 'javascript link' => ['<a href="javascript:alert(1)">link</a>'];
        yield 'obfuscated javascript link' => ['<a href="jav&#x09;ascript:alert(1)">link</a>'];
        yield 'data link' => ['<a href="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==">link</a>'];
        yield 'iframe' => ['<iframe src="https://evil.example"></iframe>'];
        yield 'malformed tag' => ['<scr<script>ipt>alert(1)</scr</script>ipt>'];
        yield 'style' => ['<p style="background:url(javascript:alert(1))">x</p>'];
        yield 'form' => ['<form action="https://evil.example"><input name="password"></form>'];
    }

    /**
     * @dataProvider dangerousPayloads
     */
    public function testDangerousMarkupIsRemoved(string $payload): void
    {
        $result = (new RichTextSanitizer())->sanitize($payload);

        self::assertNotNull($result);
        self::assertDoesNotMatchRegularExpression('/<\s*(script|img|svg|iframe|form|input)|\son\w+\s*=|javascript:|data:|style\s*=/i', $result);
    }

    public function testEditorMarkupIsKept(): void
    {
        $html = '<h2>Titre</h2><p>Un <strong>texte</strong> en <i>italique</i><br />sur deux lignes</p><blockquote><p>Citation</p></blockquote><ul><li>un</li></ul><ol><li>deux</li></ol>';

        self::assertSame($html, (new RichTextSanitizer())->sanitize($html));
    }

    public function testSafeLinkIsKeptWithRel(): void
    {
        $result = (new RichTextSanitizer())->sanitize('<a href="https://example.com/page">site</a>');

        self::assertStringContainsString('href="https://example.com/page"', (string) $result);
        self::assertStringContainsString('rel="noopener noreferrer"', (string) $result);
    }

    public function testNullAndEmptyAreUnchanged(): void
    {
        $sanitizer = new RichTextSanitizer();

        self::assertNull($sanitizer->sanitize(null));
        self::assertSame('', $sanitizer->sanitize(''));
    }
}
