<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Web/Social/SocialImage.php';
require_once __DIR__ . '/../src/Web/Social/SocialImageFactory.php';

use Maatify\Seo\Web\Social\SocialImage;
use Maatify\Seo\Web\Social\SocialImageFactory;

function phase14DIsInstanceOf(mixed $value, string $class): bool
{
    return $value instanceof $class;
}

final class Phase14DTestFailureCounter { public static int $count = 0; }

function testPhase14DSocialImageFactoryTestAssertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        Phase14DTestFailureCounter::$count++;
        echo "FAIL: $message\n";
        echo "  Expected: " . print_r($expected, true) . "\n";
        echo "  Actual:   " . print_r($actual, true) . "\n";
    }
}

// 1. Test fromUrl()
$image = SocialImageFactory::fromUrl('https://example.com/img.jpg');
testPhase14DSocialImageFactoryTestAssertSameValue(true, phase14DIsInstanceOf($image, SocialImage::class), 'fromUrl returns SocialImage');
testPhase14DSocialImageFactoryTestAssertSameValue('https://example.com/img.jpg', $image->getUrl(), 'fromUrl sets url');
testPhase14DSocialImageFactoryTestAssertSameValue(null, $image->getAlt(), 'fromUrl alt is null');

// 2. Test fromUrlWithAlt()
$image = SocialImageFactory::fromUrlWithAlt('https://example.com/img.jpg', 'Image Alt');
testPhase14DSocialImageFactoryTestAssertSameValue('https://example.com/img.jpg', $image->getUrl(), 'fromUrlWithAlt sets url');
testPhase14DSocialImageFactoryTestAssertSameValue('Image Alt', $image->getAlt(), 'fromUrlWithAlt sets alt');

// 3. Test openGraph()
$image = SocialImageFactory::openGraph('https://example.com/og.jpg');
testPhase14DSocialImageFactoryTestAssertSameValue('https://example.com/og.jpg', $image->getUrl(), 'openGraph sets url');
testPhase14DSocialImageFactoryTestAssertSameValue(null, $image->getAlt(), 'openGraph alt is optional');

$image = SocialImageFactory::openGraph('https://example.com/og.jpg', 'OG Alt');
testPhase14DSocialImageFactoryTestAssertSameValue('OG Alt', $image->getAlt(), 'openGraph sets alt');

// 4. Test twitterLargeImage()
$image = SocialImageFactory::twitterLargeImage('https://example.com/tw.jpg');
testPhase14DSocialImageFactoryTestAssertSameValue('https://example.com/tw.jpg', $image->getUrl(), 'twitterLargeImage sets url');
testPhase14DSocialImageFactoryTestAssertSameValue(null, $image->getAlt(), 'twitterLargeImage alt is optional');

$image = SocialImageFactory::twitterLargeImage('https://example.com/tw.jpg', 'TW Alt');
testPhase14DSocialImageFactoryTestAssertSameValue('TW Alt', $image->getAlt(), 'twitterLargeImage sets alt');

// 5. Test jpeg()
$image = SocialImageFactory::jpeg('https://example.com/img.jpg', 800, 600);
testPhase14DSocialImageFactoryTestAssertSameValue('https://example.com/img.jpg', $image->getUrl(), 'jpeg sets url');
testPhase14DSocialImageFactoryTestAssertSameValue('image/jpeg', $image->getType(), 'jpeg sets type');
testPhase14DSocialImageFactoryTestAssertSameValue(800, $image->getWidth(), 'jpeg sets width');
testPhase14DSocialImageFactoryTestAssertSameValue(600, $image->getHeight(), 'jpeg sets height');
testPhase14DSocialImageFactoryTestAssertSameValue(null, $image->getAlt(), 'jpeg alt is optional');

$image = SocialImageFactory::jpeg('https://example.com/img.jpg', 800, 600, 'JPG Alt');
testPhase14DSocialImageFactoryTestAssertSameValue('JPG Alt', $image->getAlt(), 'jpeg sets alt');

// 6. Test png()
$image = SocialImageFactory::png('https://example.com/img.png', 400, 300);
testPhase14DSocialImageFactoryTestAssertSameValue('https://example.com/img.png', $image->getUrl(), 'png sets url');
testPhase14DSocialImageFactoryTestAssertSameValue('image/png', $image->getType(), 'png sets type');
testPhase14DSocialImageFactoryTestAssertSameValue(400, $image->getWidth(), 'png sets width');
testPhase14DSocialImageFactoryTestAssertSameValue(300, $image->getHeight(), 'png sets height');
testPhase14DSocialImageFactoryTestAssertSameValue(null, $image->getAlt(), 'png alt is optional');

$image = SocialImageFactory::png('https://example.com/img.png', 400, 300, 'PNG Alt');
testPhase14DSocialImageFactoryTestAssertSameValue('PNG Alt', $image->getAlt(), 'png sets alt');

// 7. Test webp()
$image = SocialImageFactory::webp('https://example.com/img.webp', 1024, 768);
testPhase14DSocialImageFactoryTestAssertSameValue('https://example.com/img.webp', $image->getUrl(), 'webp sets url');
testPhase14DSocialImageFactoryTestAssertSameValue('image/webp', $image->getType(), 'webp sets type');
testPhase14DSocialImageFactoryTestAssertSameValue(1024, $image->getWidth(), 'webp sets width');
testPhase14DSocialImageFactoryTestAssertSameValue(768, $image->getHeight(), 'webp sets height');
testPhase14DSocialImageFactoryTestAssertSameValue(null, $image->getAlt(), 'webp alt is optional');

$image = SocialImageFactory::webp('https://example.com/img.webp', 1024, 768, 'WEBP Alt');
testPhase14DSocialImageFactoryTestAssertSameValue('WEBP Alt', $image->getAlt(), 'webp sets alt');

// 8. Test withDimensions()
$image = SocialImageFactory::withDimensions('https://example.com/img.jpg', 640, 480);
testPhase14DSocialImageFactoryTestAssertSameValue('https://example.com/img.jpg', $image->getUrl(), 'withDimensions sets url');
testPhase14DSocialImageFactoryTestAssertSameValue(640, $image->getWidth(), 'withDimensions sets width');
testPhase14DSocialImageFactoryTestAssertSameValue(480, $image->getHeight(), 'withDimensions sets height');
testPhase14DSocialImageFactoryTestAssertSameValue(null, $image->getAlt(), 'withDimensions alt is optional');

$image = SocialImageFactory::withDimensions('https://example.com/img.jpg', 640, 480, 'Dim Alt');
testPhase14DSocialImageFactoryTestAssertSameValue('Dim Alt', $image->getAlt(), 'withDimensions sets alt');

// 9. Test withSecureUrl()
$image = SocialImageFactory::withSecureUrl('http://example.com/img.jpg', 'https://example.com/img.jpg');
testPhase14DSocialImageFactoryTestAssertSameValue('http://example.com/img.jpg', $image->getUrl(), 'withSecureUrl sets url');
testPhase14DSocialImageFactoryTestAssertSameValue('https://example.com/img.jpg', $image->getSecureUrl(), 'withSecureUrl sets secure url');
testPhase14DSocialImageFactoryTestAssertSameValue(null, $image->getAlt(), 'withSecureUrl alt is optional');

$image = SocialImageFactory::withSecureUrl('http://example.com/img.jpg', 'https://example.com/img.jpg', 'Sec Alt');
testPhase14DSocialImageFactoryTestAssertSameValue('Sec Alt', $image->getAlt(), 'withSecureUrl sets alt');

if (Phase14DTestFailureCounter::$count > 0) {
    echo "\nPhase 14D Social Image Factory tests failed with " . Phase14DTestFailureCounter::$count . " failures.\n";
    exit(1);
}

echo "Phase 14D Social Image Factory tests passed.\n";
