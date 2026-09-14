<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Maatify\Seo\Web\Hreflang\HreflangLinkBuilder;
use Maatify\Seo\Web\Hreflang\HreflangLinkDTO;
use Maatify\Seo\Web\Hreflang\HreflangLinkRenderer;

echo "--- Hreflang Link Generation ---\n";

$builder = new HreflangLinkBuilder();

// Add specific languages/regions
$builder->add('en', 'https://example.com/en/page');
$builder->add('en-US', 'https://example.com/en-us/page');
$builder->add('en-GB', 'https://example.com/en-gb/page');
$builder->add('fr', 'https://example.com/fr/page');

// Add the x-default fallback for unmatched languages
$builder->xDefault('https://example.com/en/page');

// The builder exposes normalized DTOs and can use the renderer directly.
$links = $builder->all();
$dto = new HreflangLinkDTO('de', 'https://example.com/de/page');
$html = (new HreflangLinkRenderer())->render($links);

echo "DTO example: " . json_encode($dto, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
echo $html . "\n";

echo "Done.\n";
