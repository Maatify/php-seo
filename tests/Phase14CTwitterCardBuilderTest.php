<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Web/Social/SocialMetaTag.php';
require_once __DIR__ . '/../src/Web/Social/SocialImage.php';
require_once __DIR__ . '/../src/Web/Social/SocialMetaCollection.php';
require_once __DIR__ . '/../src/Web/Social/SocialMetaRenderOutput.php';
require_once __DIR__ . '/../src/Web/Social/TwitterCardBuilder.php';

use Maatify\Seo\Web\Social\SocialMetaTag;
use Maatify\Seo\Web\Social\SocialImage;
use Maatify\Seo\Web\Social\SocialMetaCollection;
use Maatify\Seo\Web\Social\SocialMetaRenderOutput;
use Maatify\Seo\Web\Social\TwitterCardBuilder;

function phase14CIsInstanceOf(mixed $value, string $class): bool
{
    return $value instanceof $class;
}

final class Phase14CTestFailureCounter { public static int $count = 0; }

function testPhase14CTwitterCardBuilderTestAssertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        Phase14CTestFailureCounter::$count++;
        echo "FAIL: $message\n";
        echo "  Expected: " . print_r($expected, true) . "\n";
        echo "  Actual:   " . print_r($actual, true) . "\n";
    }
}

// 1. Scalar Twitter/X fields tests
$builder = new TwitterCardBuilder();
$builder->setCard('summary_large_image')
        ->setSite('@site_handle')
        ->setCreator('@creator_handle')
        ->setTitle('Test Title')
        ->setDescription('Test Description')
        ->setPlayer('https://example.com/player')
        ->setPlayerWidth(800)
        ->setPlayerHeight(600)
        ->setAppNameIphone('App iPhone')
        ->setAppIdIphone('id_iphone')
        ->setAppUrlIphone('url_iphone')
        ->setAppNameIpad('App iPad')
        ->setAppIdIpad('id_ipad')
        ->setAppUrlIpad('url_ipad')
        ->setAppNameGoogleplay('App GooglePlay')
        ->setAppIdGoogleplay('id_googleplay')
        ->setAppUrlGoogleplay('url_googleplay');

$expectedArray = [
    ['name' => 'twitter:card', 'content' => 'summary_large_image', 'attribute' => 'name'],
    ['name' => 'twitter:site', 'content' => '@site_handle', 'attribute' => 'name'],
    ['name' => 'twitter:creator', 'content' => '@creator_handle', 'attribute' => 'name'],
    ['name' => 'twitter:title', 'content' => 'Test Title', 'attribute' => 'name'],
    ['name' => 'twitter:description', 'content' => 'Test Description', 'attribute' => 'name'],
    ['name' => 'twitter:player', 'content' => 'https://example.com/player', 'attribute' => 'name'],
    ['name' => 'twitter:player:width', 'content' => '800', 'attribute' => 'name'],
    ['name' => 'twitter:player:height', 'content' => '600', 'attribute' => 'name'],
    ['name' => 'twitter:app:name:iphone', 'content' => 'App iPhone', 'attribute' => 'name'],
    ['name' => 'twitter:app:id:iphone', 'content' => 'id_iphone', 'attribute' => 'name'],
    ['name' => 'twitter:app:url:iphone', 'content' => 'url_iphone', 'attribute' => 'name'],
    ['name' => 'twitter:app:name:ipad', 'content' => 'App iPad', 'attribute' => 'name'],
    ['name' => 'twitter:app:id:ipad', 'content' => 'id_ipad', 'attribute' => 'name'],
    ['name' => 'twitter:app:url:ipad', 'content' => 'url_ipad', 'attribute' => 'name'],
    ['name' => 'twitter:app:name:googleplay', 'content' => 'App GooglePlay', 'attribute' => 'name'],
    ['name' => 'twitter:app:id:googleplay', 'content' => 'id_googleplay', 'attribute' => 'name'],
    ['name' => 'twitter:app:url:googleplay', 'content' => 'url_googleplay', 'attribute' => 'name'],
];

testPhase14CTwitterCardBuilderTestAssertSameValue($expectedArray, $builder->toArray(), 'TwitterCardBuilder scalar tags toArray');

$expectedHtml = '<meta name="twitter:card" content="summary_large_image">' . "\n" .
                '<meta name="twitter:site" content="@site_handle">' . "\n" .
                '<meta name="twitter:creator" content="@creator_handle">' . "\n" .
                '<meta name="twitter:title" content="Test Title">' . "\n" .
                '<meta name="twitter:description" content="Test Description">' . "\n" .
                '<meta name="twitter:player" content="https://example.com/player">' . "\n" .
                '<meta name="twitter:player:width" content="800">' . "\n" .
                '<meta name="twitter:player:height" content="600">' . "\n" .
                '<meta name="twitter:app:name:iphone" content="App iPhone">' . "\n" .
                '<meta name="twitter:app:id:iphone" content="id_iphone">' . "\n" .
                '<meta name="twitter:app:url:iphone" content="url_iphone">' . "\n" .
                '<meta name="twitter:app:name:ipad" content="App iPad">' . "\n" .
                '<meta name="twitter:app:id:ipad" content="id_ipad">' . "\n" .
                '<meta name="twitter:app:url:ipad" content="url_ipad">' . "\n" .
                '<meta name="twitter:app:name:googleplay" content="App GooglePlay">' . "\n" .
                '<meta name="twitter:app:id:googleplay" content="id_googleplay">' . "\n" .
                '<meta name="twitter:app:url:googleplay" content="url_googleplay">';

testPhase14CTwitterCardBuilderTestAssertSameValue($expectedHtml, $builder->toHtml(), 'TwitterCardBuilder scalar tags toHtml');

// 2. Image behavior tests
$builder = new TwitterCardBuilder();

// setImage(string)
$builder->setImage('https://example.com/image1.jpg');
$array = $builder->toArray();
testPhase14CTwitterCardBuilderTestAssertSameValue('twitter:image', $array[0]['name'], 'setImage(string) creates twitter:image');
testPhase14CTwitterCardBuilderTestAssertSameValue('https://example.com/image1.jpg', $array[0]['content'], 'setImage(string) correct URL');
testPhase14CTwitterCardBuilderTestAssertSameValue(1, count($array), 'setImage(string) creates exactly one tag (no alt)');

// setImage(SocialImage) replaces existing images and uses alt from SocialImage
$image2 = new SocialImage('https://example.com/image2.jpg');
$image2->setAlt('Image 2 Alt');
$builder->setImage($image2);
$array = $builder->toArray();
testPhase14CTwitterCardBuilderTestAssertSameValue('twitter:image', $array[0]['name'], 'setImage(SocialImage) correct tag name');
testPhase14CTwitterCardBuilderTestAssertSameValue('https://example.com/image2.jpg', $array[0]['content'], 'setImage(SocialImage) replaces URL');
testPhase14CTwitterCardBuilderTestAssertSameValue('twitter:image:alt', $array[1]['name'], 'setImage(SocialImage) populates alt tag');
testPhase14CTwitterCardBuilderTestAssertSameValue('Image 2 Alt', $array[1]['content'], 'setImage(SocialImage) correct alt content');
testPhase14CTwitterCardBuilderTestAssertSameValue(2, count($array), 'setImage(SocialImage) with alt creates two tags');

// setImageAlt() overrides SocialImage alt
$builder->setImageAlt('Override Alt');
$array = $builder->toArray();
testPhase14CTwitterCardBuilderTestAssertSameValue('Override Alt', $array[1]['content'], 'setImageAlt() overrides existing image alt');

// setImageAlt() before setImage() still takes precedence
$builder = new TwitterCardBuilder();
$builder->setImageAlt('Precedence Alt');
$image3 = new SocialImage('https://example.com/image3.jpg');
$image3->setAlt('SocialImage Alt');
$builder->setImage($image3);
$array = $builder->toArray();
testPhase14CTwitterCardBuilderTestAssertSameValue('Precedence Alt', $array[1]['content'], 'setImageAlt() before setImage() takes precedence over SocialImage alt');

// no multiple image support - setImage() overwrites completely
$builder = new TwitterCardBuilder();
$builder->setImage('https://example.com/first.jpg');
$builder->setImage('https://example.com/second.jpg');
$array = $builder->toArray();
testPhase14CTwitterCardBuilderTestAssertSameValue(1, count($array), 'setImage() only supports one image (replaces previous)');
testPhase14CTwitterCardBuilderTestAssertSameValue('https://example.com/second.jpg', $array[0]['content'], 'setImage() replaced URL');

// 3. Output formats, tag ordering and escaping
$builder = new TwitterCardBuilder();
$builder->setTitle('Title "with" quotes <&>')
        ->setImage('https://example.com/img.jpg');

$expectedArray = [
    ['name' => 'twitter:title', 'content' => 'Title "with" quotes <&>', 'attribute' => 'name'],
    ['name' => 'twitter:image', 'content' => 'https://example.com/img.jpg', 'attribute' => 'name'],
];
testPhase14CTwitterCardBuilderTestAssertSameValue($expectedArray, $builder->toArray(), 'TwitterCardBuilder escaping array format');

$expectedHtmlEscaped = '<meta name="twitter:title" content="Title &quot;with&quot; quotes &lt;&amp;&gt;">' . "\n" .
                       '<meta name="twitter:image" content="https://example.com/img.jpg">';
testPhase14CTwitterCardBuilderTestAssertSameValue($expectedHtmlEscaped, $builder->toHtml(), 'TwitterCardBuilder escaping HTML format');

$collection = $builder->toCollection();
testPhase14CTwitterCardBuilderTestAssertSameValue(true, phase14CIsInstanceOf($collection, SocialMetaCollection::class), 'toCollection returns SocialMetaCollection');
testPhase14CTwitterCardBuilderTestAssertSameValue($expectedHtmlEscaped, $collection->toHtml(), 'toCollection HTML matches');

$renderOutput = $builder->toRenderOutput();
testPhase14CTwitterCardBuilderTestAssertSameValue(true, phase14CIsInstanceOf($renderOutput, SocialMetaRenderOutput::class), 'toRenderOutput returns SocialMetaRenderOutput');
testPhase14CTwitterCardBuilderTestAssertSameValue($expectedHtmlEscaped, $renderOutput->toHtml(), 'toRenderOutput HTML matches');

if (Phase14CTestFailureCounter::$count > 0) {
    echo "\nPhase 14C Twitter/X Card Builder tests failed with " . Phase14CTestFailureCounter::$count . " failures.\n";
    exit(1);
}

echo "Phase 14C Twitter/X Card Builder tests passed.\n";
