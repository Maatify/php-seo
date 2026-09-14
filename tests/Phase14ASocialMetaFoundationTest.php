<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Web/Social/SocialMetaTag.php';
require_once __DIR__ . '/../src/Web/Social/SocialImage.php';
require_once __DIR__ . '/../src/Web/Social/SocialMetaCollection.php';
require_once __DIR__ . '/../src/Web/Social/SocialMetaRenderOutput.php';

use Maatify\Seo\Web\Social\SocialMetaTag;
use Maatify\Seo\Web\Social\SocialImage;
use Maatify\Seo\Web\Social\SocialMetaCollection;
use Maatify\Seo\Web\Social\SocialMetaRenderOutput;

final class Phase14ATestFailureCounter { public static int $count = 0; }

function testPhase14ASocialMetaFoundationTestAssertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        Phase14ATestFailureCounter::$count++;
        echo "FAIL: $message\n";
        echo "  Expected: " . print_r($expected, true) . "\n";
        echo "  Actual:   " . print_r($actual, true) . "\n";
    }
}

function testPhase14ASocialMetaFoundationTestAssertTrueValue(bool $actual, string $message): void
{
    testPhase14ASocialMetaFoundationTestAssertSameValue(true, $actual, $message);
}

function testPhase14ASocialMetaFoundationTestAssertFalseValue(bool $actual, string $message): void
{
    testPhase14ASocialMetaFoundationTestAssertSameValue(false, $actual, $message);
}

// 1. SocialMetaTag tests
$tag = new SocialMetaTag('og:title', 'Test Title');
testPhase14ASocialMetaFoundationTestAssertSameValue('og:title', $tag->getName(), 'SocialMetaTag getName');
testPhase14ASocialMetaFoundationTestAssertSameValue('Test Title', $tag->getContent(), 'SocialMetaTag getContent');
testPhase14ASocialMetaFoundationTestAssertSameValue('property', $tag->getAttribute(), 'SocialMetaTag getAttribute default');

$tagArray = $tag->toArray();
testPhase14ASocialMetaFoundationTestAssertSameValue('og:title', $tagArray['name'], 'SocialMetaTag toArray name');
testPhase14ASocialMetaFoundationTestAssertSameValue('Test Title', $tagArray['content'], 'SocialMetaTag toArray content');
testPhase14ASocialMetaFoundationTestAssertSameValue('property', $tagArray['attribute'], 'SocialMetaTag toArray attribute');

$expectedHtml = '<meta property="og:title" content="Test Title">';
testPhase14ASocialMetaFoundationTestAssertSameValue($expectedHtml, $tag->toHtml(), 'SocialMetaTag toHtml default');

$customTag = new SocialMetaTag('twitter:card', 'summary_large_image', 'name');
testPhase14ASocialMetaFoundationTestAssertSameValue('twitter:card', $customTag->getName(), 'SocialMetaTag custom name');
testPhase14ASocialMetaFoundationTestAssertSameValue('summary_large_image', $customTag->getContent(), 'SocialMetaTag custom content');
testPhase14ASocialMetaFoundationTestAssertSameValue('name', $customTag->getAttribute(), 'SocialMetaTag custom attribute');
testPhase14ASocialMetaFoundationTestAssertSameValue('<meta name="twitter:card" content="summary_large_image">', $customTag->toHtml(), 'SocialMetaTag toHtml custom attribute');

$itempropTag = new SocialMetaTag('image', 'https://example.com/img.jpg', 'itemprop');
testPhase14ASocialMetaFoundationTestAssertSameValue('<meta itemprop="image" content="https://example.com/img.jpg">', $itempropTag->toHtml(), 'SocialMetaTag toHtml itemprop attribute');

$escapedTag = new SocialMetaTag('bad"name', 'bad"content<', 'bad"attr');
$expectedEscapedHtml = '<meta bad&quot;attr="bad&quot;name" content="bad&quot;content&lt;">';
testPhase14ASocialMetaFoundationTestAssertSameValue($expectedEscapedHtml, $escapedTag->toHtml(), 'SocialMetaTag toHtml escaping');

// 2. SocialImage tests
$image = new SocialImage('https://example.com/image.jpg');
testPhase14ASocialMetaFoundationTestAssertSameValue('https://example.com/image.jpg', $image->getUrl(), 'SocialImage getUrl');
testPhase14ASocialMetaFoundationTestAssertSameValue(null, $image->getSecureUrl(), 'SocialImage getSecureUrl default null');
testPhase14ASocialMetaFoundationTestAssertSameValue(null, $image->getType(), 'SocialImage getType default null');
testPhase14ASocialMetaFoundationTestAssertSameValue(null, $image->getWidth(), 'SocialImage getWidth default null');
testPhase14ASocialMetaFoundationTestAssertSameValue(null, $image->getHeight(), 'SocialImage getHeight default null');
testPhase14ASocialMetaFoundationTestAssertSameValue(null, $image->getAlt(), 'SocialImage getAlt default null');

$imageArray = $image->toArray();
testPhase14ASocialMetaFoundationTestAssertSameValue(['url' => 'https://example.com/image.jpg'], $imageArray, 'SocialImage toArray default');

$image->setSecureUrl('https://secure.example.com/image.jpg')
      ->setType('image/jpeg')
      ->setWidth(1200)
      ->setHeight(630)
      ->setAlt('Example Image');

testPhase14ASocialMetaFoundationTestAssertSameValue('https://secure.example.com/image.jpg', $image->getSecureUrl(), 'SocialImage getSecureUrl after set');
testPhase14ASocialMetaFoundationTestAssertSameValue('image/jpeg', $image->getType(), 'SocialImage getType after set');
testPhase14ASocialMetaFoundationTestAssertSameValue(1200, $image->getWidth(), 'SocialImage getWidth after set');
testPhase14ASocialMetaFoundationTestAssertSameValue(630, $image->getHeight(), 'SocialImage getHeight after set');
testPhase14ASocialMetaFoundationTestAssertSameValue('Example Image', $image->getAlt(), 'SocialImage getAlt after set');

$expectedImageArray = [
    'url' => 'https://example.com/image.jpg',
    'secure_url' => 'https://secure.example.com/image.jpg',
    'type' => 'image/jpeg',
    'width' => 1200,
    'height' => 630,
    'alt' => 'Example Image',
];
testPhase14ASocialMetaFoundationTestAssertSameValue($expectedImageArray, $image->toArray(), 'SocialImage toArray with optional fields');

// 3. SocialMetaCollection tests
$collection = new SocialMetaCollection();
testPhase14ASocialMetaFoundationTestAssertTrueValue($collection->isEmpty(), 'SocialMetaCollection isEmpty initial');
testPhase14ASocialMetaFoundationTestAssertSameValue(0, $collection->count(), 'SocialMetaCollection count initial');
testPhase14ASocialMetaFoundationTestAssertSameValue([], $collection->all(), 'SocialMetaCollection all initial');
testPhase14ASocialMetaFoundationTestAssertSameValue([], $collection->toArray(), 'SocialMetaCollection toArray initial');
testPhase14ASocialMetaFoundationTestAssertSameValue('', $collection->toHtml(), 'SocialMetaCollection toHtml initial');

$collection->add(new SocialMetaTag('og:title', 'Collection Title'));
$collection->addTag('twitter:title', 'Twitter Title', 'name');

testPhase14ASocialMetaFoundationTestAssertFalseValue($collection->isEmpty(), 'SocialMetaCollection isEmpty after add');
testPhase14ASocialMetaFoundationTestAssertSameValue(2, $collection->count(), 'SocialMetaCollection count after add');

$tags = $collection->all();
testPhase14ASocialMetaFoundationTestAssertSameValue('og:title', $tags[0]->getName(), 'SocialMetaCollection tags[0] name');
testPhase14ASocialMetaFoundationTestAssertSameValue('twitter:title', $tags[1]->getName(), 'SocialMetaCollection tags[1] name');

$expectedCollectionArray = [
    ['name' => 'og:title', 'content' => 'Collection Title', 'attribute' => 'property'],
    ['name' => 'twitter:title', 'content' => 'Twitter Title', 'attribute' => 'name'],
];
testPhase14ASocialMetaFoundationTestAssertSameValue($expectedCollectionArray, $collection->toArray(), 'SocialMetaCollection toArray');

$expectedCollectionHtml = '<meta property="og:title" content="Collection Title">' . "\n" . '<meta name="twitter:title" content="Twitter Title">';
testPhase14ASocialMetaFoundationTestAssertSameValue($expectedCollectionHtml, $collection->toHtml(), 'SocialMetaCollection toHtml');
testPhase14ASocialMetaFoundationTestAssertSameValue('<meta property="og:title" content="Collection Title">|<meta name="twitter:title" content="Twitter Title">', $collection->toHtml('|'), 'SocialMetaCollection toHtml custom separator');

// Test preserves insertion order and does not deduplicate duplicate tags
$collection->addTag('og:title', 'Second Title');
testPhase14ASocialMetaFoundationTestAssertSameValue(3, $collection->count(), 'SocialMetaCollection count after duplicate add');
$tags = $collection->all();
testPhase14ASocialMetaFoundationTestAssertSameValue('og:title', $tags[0]->getName(), 'SocialMetaCollection tags[0] name (duplicate test)');
testPhase14ASocialMetaFoundationTestAssertSameValue('og:title', $tags[2]->getName(), 'SocialMetaCollection tags[2] name (duplicate test)');
testPhase14ASocialMetaFoundationTestAssertSameValue('Collection Title', $tags[0]->getContent(), 'SocialMetaCollection tags[0] content (duplicate test)');
testPhase14ASocialMetaFoundationTestAssertSameValue('Second Title', $tags[2]->getContent(), 'SocialMetaCollection tags[2] content (duplicate test)');

// 4. SocialMetaRenderOutput tests
$renderOutput = new SocialMetaRenderOutput($collection);
testPhase14ASocialMetaFoundationTestAssertSameValue($collection, $renderOutput->getCollection(), 'SocialMetaRenderOutput getCollection');

$outputTags = $renderOutput->getTags();
testPhase14ASocialMetaFoundationTestAssertSameValue($tags, $outputTags, 'SocialMetaRenderOutput getTags delegates to collection all');

$outputArray = $renderOutput->toArray();
testPhase14ASocialMetaFoundationTestAssertSameValue($collection->toArray(), $outputArray, 'SocialMetaRenderOutput toArray delegates to collection toArray');

$outputHtml = $renderOutput->toHtml();
testPhase14ASocialMetaFoundationTestAssertSameValue($collection->toHtml(), $outputHtml, 'SocialMetaRenderOutput toHtml delegates to collection toHtml');

$outputHtmlPipe = $renderOutput->toHtml('|');
testPhase14ASocialMetaFoundationTestAssertSameValue($collection->toHtml('|'), $outputHtmlPipe, 'SocialMetaRenderOutput toHtml custom separator delegates to collection toHtml');

$outputIsEmpty = $renderOutput->isEmpty();
testPhase14ASocialMetaFoundationTestAssertSameValue($collection->isEmpty(), $outputIsEmpty, 'SocialMetaRenderOutput isEmpty delegates to collection isEmpty');

$emptyCollection = new SocialMetaCollection();
$emptyRenderOutput = new SocialMetaRenderOutput($emptyCollection);
testPhase14ASocialMetaFoundationTestAssertTrueValue($emptyRenderOutput->isEmpty(), 'SocialMetaRenderOutput isEmpty empty collection');

if (Phase14ATestFailureCounter::$count > 0) {
    echo "\nPhase 14A Social Meta Foundation tests failed with " . Phase14ATestFailureCounter::$count . " failures.\n";
    exit(1);
}

echo "Phase 14A Social Meta Foundation tests passed.\n";
