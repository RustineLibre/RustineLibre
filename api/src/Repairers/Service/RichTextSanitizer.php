<?php

declare(strict_types=1);

namespace App\Repairers\Service;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

/**
 * Keeps only the markup produced by the PWA rich text editor (see pwa/components/form/Editor.tsx).
 */
final readonly class RichTextSanitizer
{
    private const ALLOWED_ELEMENTS = ['h2', 'h3', 'h4', 'p', 'br', 'strong', 'i', 'em', 'blockquote', 'ul', 'ol', 'li'];

    private HtmlSanitizerInterface $sanitizer;

    public function __construct()
    {
        $config = new HtmlSanitizerConfig();
        foreach (self::ALLOWED_ELEMENTS as $element) {
            $config = $config->allowElement($element);
        }

        $this->sanitizer = new HtmlSanitizer(
            $config
                ->allowElement('a', ['href'])
                ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
                ->allowRelativeLinks(false)
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
        );
    }

    public function sanitize(?string $html): ?string
    {
        if (null === $html || '' === $html) {
            return $html;
        }

        return $this->sanitizer->sanitize($html);
    }
}
