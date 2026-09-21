<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Cleans the rich text that admins and merchants write for product listings.
 *
 * Descriptions are written in an editor and rendered as HTML rather than
 * escaped, which makes them the one place on the storefront where somebody
 * else's markup reaches a customer's browser. Merchants sign themselves up, so
 * that markup is not trustworthy: without this, a supplier could put a script
 * tag in a description and have it run for every customer who opened the page.
 *
 * Sanitising happens on the way in, at the point the text is saved, so what is
 * stored is already safe to render.
 */
class RichText
{
    /**
     * Tags an editor legitimately produces. Anything else is dropped, and every
     * attribute not listed against a tag goes with it — which is what removes
     * event handlers such as `onerror`.
     */
    private const ALLOWED = [
        'p' => [],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'h5' => [],
        'h6' => [],
        'blockquote' => [],
        'code' => [],
        'pre' => [],
        'span' => [],
        'a' => ['href', 'title'],
        'table' => [],
        'thead' => [],
        'tbody' => [],
        'tr' => [],
        'th' => [],
        'td' => [],
    ];

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        return self::sanitizer()->sanitize($html);
    }

    private static function sanitizer(): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig)
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            // No images: product photography is uploaded and served by us, so a
            // description has no reason to pull one in from somewhere else.
            ->blockElement('img')
            ->forceHttpsUrls(false);

        foreach (self::ALLOWED as $tag => $attributes) {
            $config = $config->allowElement($tag, $attributes);
        }

        return new HtmlSanitizer($config);
    }
}
