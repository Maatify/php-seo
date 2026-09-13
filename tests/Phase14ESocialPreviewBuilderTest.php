<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Web/Social/SocialMetaTag.php';
require_once __DIR__ . '/../src/Web/Social/SocialImage.php';
require_once __DIR__ . '/../src/Web/Social/SocialMetaCollection.php';
require_once __DIR__ . '/../src/Web/Social/SocialMetaRenderOutput.php';
require_once __DIR__ . '/../src/Web/Social/OpenGraphBuilder.php';
require_once __DIR__ . '/../src/Web/Social/TwitterCardBuilder.php';
require_once __DIR__ . '/../src/Web/Social/SocialPreviewBuilder.php';

use Maatify\Seo\Web\Social\OpenGraphBuilder;
use Maatify\Seo\Web\Social\SocialPreviewBuilder;
use Maatify\Seo\Web\Social\TwitterCardBuilder;

function phase14EIsInstanceOf(mixed $value, string $class): bool
{
    return $value instanceof $class;
}

final class Phase14ETestFailureCounter { public static int $count = 0; }

function testPhase14ESocialPreviewBuilderTestAssertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        Phase14ETestFailureCounter::$count++;
        echo "FAIL: $message\n";
        echo "  Expected: " . print_r($expected, true) . "\n";
        echo "  Actual:   " . print_r($actual, true) . "\n";
    }
}

// 1. Internal builder access tests
$builder = new SocialPreviewBuilder();
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, phase14EIsInstanceOf($builder->openGraph(), OpenGraphBuilder::class), 'openGraph() should return OpenGraphBuilder');
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, phase14EIsInstanceOf($builder->twitter(), TwitterCardBuilder::class), 'twitter() should return TwitterCardBuilder');

$builder->openGraph()->setType('article');
$builder->twitter()->setPlayer('https://example.com/player');

$array = $builder->toArray();
$ogTypeFound = false;
$twitterPlayerFound = false;
foreach ($array as $tag) {
    if ($tag['attribute'] === 'property' && $tag['name'] === 'og:type' && $tag['content'] === 'article') {
        $ogTypeFound = true;
    }
    if ($tag['attribute'] === 'name' && $tag['name'] === 'twitter:player' && $tag['content'] === 'https://example.com/player') {
        $twitterPlayerFound = true;
    }
}
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $ogTypeFound, 'Advanced customization through openGraph() should apply');
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $twitterPlayerFound, 'Advanced customization through twitter() should apply');


// 2. Shared setters
$builder = new SocialPreviewBuilder();
$builder->setTitle('My Title')
        ->setDescription('My Description')
        ->setImage('https://example.com/image.jpg');

$array = $builder->toArray();
$ogTitle = $ogDesc = $ogImage = $twTitle = $twDesc = $twImage = false;
foreach ($array as $tag) {
    if ($tag['name'] === 'og:title' && $tag['content'] === 'My Title') $ogTitle = true;
    if ($tag['name'] === 'og:description' && $tag['content'] === 'My Description') $ogDesc = true;
    if ($tag['name'] === 'og:image' && $tag['content'] === 'https://example.com/image.jpg') $ogImage = true;

    if ($tag['name'] === 'twitter:title' && $tag['content'] === 'My Title') $twTitle = true;
    if ($tag['name'] === 'twitter:description' && $tag['content'] === 'My Description') $twDesc = true;
    if ($tag['name'] === 'twitter:image' && $tag['content'] === 'https://example.com/image.jpg') $twImage = true;
}

testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $ogTitle && $twTitle, 'setTitle() should apply to both OG and Twitter');
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $ogDesc && $twDesc, 'setDescription() should apply to both OG and Twitter');
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $ogImage && $twImage, 'setImage() should apply to both OG and Twitter');


// 3. Open Graph-only setters
$builder = new SocialPreviewBuilder();
$builder->setUrl('https://example.com/')
        ->setSiteName('My Site')
        ->setLocale('en_US');

$array = $builder->toArray();
$ogUrl = $ogSiteName = $ogLocale = false;
$twUrl = $twSiteName = $twLocale = false;
foreach ($array as $tag) {
    if ($tag['name'] === 'og:url') $ogUrl = true;
    if ($tag['name'] === 'og:site_name') $ogSiteName = true;
    if ($tag['name'] === 'og:locale') $ogLocale = true;

    if ($tag['name'] === 'twitter:url') $twUrl = true;
    if ($tag['name'] === 'twitter:site_name') $twSiteName = true;
    if ($tag['name'] === 'twitter:locale') $twLocale = true;
}

testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $ogUrl, 'setUrl() applies to OG');
testPhase14ESocialPreviewBuilderTestAssertSameValue(false, $twUrl, 'setUrl() does not apply to Twitter');
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $ogSiteName, 'setSiteName() applies to OG');
testPhase14ESocialPreviewBuilderTestAssertSameValue(false, $twSiteName, 'setSiteName() does not apply to Twitter');
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $ogLocale, 'setLocale() applies to OG');
testPhase14ESocialPreviewBuilderTestAssertSameValue(false, $twLocale, 'setLocale() does not apply to Twitter');


// 4. Twitter-only setters
$builder = new SocialPreviewBuilder();
$builder->setTwitterCard('summary_large_image')
        ->setTwitterSite('@mysite')
        ->setTwitterCreator('@mycreator');

$array = $builder->toArray();
$twCard = $twSite = $twCreator = false;
$ogCard = $ogSite = $ogCreator = false;

foreach ($array as $tag) {
    if ($tag['name'] === 'twitter:card' && $tag['content'] === 'summary_large_image') $twCard = true;
    if ($tag['name'] === 'twitter:site' && $tag['content'] === '@mysite') $twSite = true;
    if ($tag['name'] === 'twitter:creator' && $tag['content'] === '@mycreator') $twCreator = true;

    if ($tag['name'] === 'og:card') $ogCard = true;
    if ($tag['name'] === 'og:site') $ogSite = true;
    if ($tag['name'] === 'og:creator') $ogCreator = true;
}

testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $twCard, 'setTwitterCard() applies to Twitter');
testPhase14ESocialPreviewBuilderTestAssertSameValue(false, $ogCard, 'setTwitterCard() does not apply to OG');
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $twSite, 'setTwitterSite() applies to Twitter');
testPhase14ESocialPreviewBuilderTestAssertSameValue(false, $ogSite, 'setTwitterSite() does not apply to OG');
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, $twCreator, 'setTwitterCreator() applies to Twitter');
testPhase14ESocialPreviewBuilderTestAssertSameValue(false, $ogCreator, 'setTwitterCreator() does not apply to OG');


// 5. Output behavior
$builder = new SocialPreviewBuilder();
$builder->setTitle('Test Title');
$builder->setTwitterCard('summary');
$builder->setUrl('https://test.com');
$builder->setDescription('Desc < > & "');

// Collection and RenderOutput
$collection = $builder->toCollection();
testPhase14ESocialPreviewBuilderTestAssertSameValue('Maatify\Seo\Web\Social\SocialMetaCollection', get_class($collection), 'toCollection returns SocialMetaCollection');

$renderOutput = $builder->toRenderOutput();
testPhase14ESocialPreviewBuilderTestAssertSameValue('Maatify\Seo\Web\Social\SocialMetaRenderOutput', get_class($renderOutput), 'toRenderOutput returns SocialMetaRenderOutput');

// Deduplication avoidance and order
$array = $builder->toArray();
// We expect: og:title, og:description, og:url, twitter:card, twitter:title, twitter:description
$expectedNames = ['og:title', 'og:description', 'og:url', 'twitter:card', 'twitter:title', 'twitter:description'];
$actualNames = array_column($array, 'name');

testPhase14ESocialPreviewBuilderTestAssertSameValue($expectedNames, $actualNames, 'Open Graph tags should appear before Twitter tags and no deduplication across them');

// Output uses correct attributes
foreach ($array as $tag) {
    if (str_starts_with($tag['name'], 'og:')) {
        testPhase14ESocialPreviewBuilderTestAssertSameValue('property', $tag['attribute'], 'Open graph should use property attribute');
    }
    if (str_starts_with($tag['name'], 'twitter:')) {
        testPhase14ESocialPreviewBuilderTestAssertSameValue('name', $tag['attribute'], 'Twitter should use name attribute');
    }
}

// HTML escaping is delegated
$html = $builder->toHtml();
testPhase14ESocialPreviewBuilderTestAssertSameValue(true, str_contains($html, 'Desc &lt; &gt; &amp; &quot;'), 'HTML escaping should work correctly in toHtml');


if (Phase14ETestFailureCounter::$count > 0) {
    echo "\n" . Phase14ETestFailureCounter::$count . " test(s) failed.\n";
    exit(1);
}

echo "All Phase 14E Social Preview Builder tests passed successfully.\n";
exit(0);
