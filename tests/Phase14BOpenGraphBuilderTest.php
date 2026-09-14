<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Web/Social/SocialMetaTag.php';
require_once __DIR__ . '/../src/Web/Social/SocialImage.php';
require_once __DIR__ . '/../src/Web/Social/SocialMetaCollection.php';
require_once __DIR__ . '/../src/Web/Social/SocialMetaRenderOutput.php';
require_once __DIR__ . '/../src/Web/Social/OpenGraphBuilder.php';

use Maatify\Seo\Web\Social\SocialMetaTag;
use Maatify\Seo\Web\Social\SocialImage;
use Maatify\Seo\Web\Social\SocialMetaCollection;
use Maatify\Seo\Web\Social\SocialMetaRenderOutput;
use Maatify\Seo\Web\Social\OpenGraphBuilder;

function phase14BIsInstanceOf(mixed $value, string $class): bool
{
    return $value instanceof $class;
}

final class Phase14BTestFailureCounter { public static int $count = 0; }

function testPhase14BOpenGraphBuilderTestAssertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        Phase14BTestFailureCounter::$count++;
        echo "FAIL: $message\n";
        echo "  Expected: " . print_r($expected, true) . "\n";
        echo "  Actual:   " . print_r($actual, true) . "\n";
    }
}

// 1. Scalar Open Graph fields tests
$builder = new OpenGraphBuilder();
$builder->setTitle('Test Title')
        ->setDescription('Test Description')
        ->setType('website')
        ->setUrl('https://example.com/page')
        ->setSiteName('Example Site')
        ->setLocale('en_US')
        ->setDeterminer('the')
        ->setAudio('https://example.com/audio.mp3')
        ->setVideo('https://example.com/video.mp4');

$expectedArray = [
    ['name' => 'og:title', 'content' => 'Test Title', 'attribute' => 'property'],
    ['name' => 'og:description', 'content' => 'Test Description', 'attribute' => 'property'],
    ['name' => 'og:type', 'content' => 'website', 'attribute' => 'property'],
    ['name' => 'og:url', 'content' => 'https://example.com/page', 'attribute' => 'property'],
    ['name' => 'og:site_name', 'content' => 'Example Site', 'attribute' => 'property'],
    ['name' => 'og:locale', 'content' => 'en_US', 'attribute' => 'property'],
    ['name' => 'og:determiner', 'content' => 'the', 'attribute' => 'property'],
    ['name' => 'og:audio', 'content' => 'https://example.com/audio.mp3', 'attribute' => 'property'],
    ['name' => 'og:video', 'content' => 'https://example.com/video.mp4', 'attribute' => 'property'],
];

testPhase14BOpenGraphBuilderTestAssertSameValue($expectedArray, $builder->toArray(), 'OpenGraphBuilder scalar tags toArray');

$expectedHtml = '<meta property="og:title" content="Test Title">' . "\n" .
                '<meta property="og:description" content="Test Description">' . "\n" .
                '<meta property="og:type" content="website">' . "\n" .
                '<meta property="og:url" content="https://example.com/page">' . "\n" .
                '<meta property="og:site_name" content="Example Site">' . "\n" .
                '<meta property="og:locale" content="en_US">' . "\n" .
                '<meta property="og:determiner" content="the">' . "\n" .
                '<meta property="og:audio" content="https://example.com/audio.mp3">' . "\n" .
                '<meta property="og:video" content="https://example.com/video.mp4">';

testPhase14BOpenGraphBuilderTestAssertSameValue($expectedHtml, $builder->toHtml(), 'OpenGraphBuilder scalar tags toHtml');


// 2. Image behavior tests
$builder = new OpenGraphBuilder();

// setImage(string)
$builder->setImage('https://example.com/image1.jpg');
$array = $builder->toArray();
testPhase14BOpenGraphBuilderTestAssertSameValue('og:image', $array[0]['name'], 'setImage(string) creates og:image');
testPhase14BOpenGraphBuilderTestAssertSameValue('https://example.com/image1.jpg', $array[0]['content'], 'setImage(string) correct URL');
testPhase14BOpenGraphBuilderTestAssertSameValue(1, count($array), 'setImage(string) creates exactly one tag');

// setImage(SocialImage) replaces existing images
$image2 = new SocialImage('https://example.com/image2.jpg');
$builder->setImage($image2);
$array = $builder->toArray();
testPhase14BOpenGraphBuilderTestAssertSameValue('https://example.com/image2.jpg', $array[0]['content'], 'setImage(SocialImage) replaces URL');
testPhase14BOpenGraphBuilderTestAssertSameValue(1, count($array), 'setImage(SocialImage) created exactly one tag');

// addImage(string) and addImage(SocialImage)
$builder->addImage('https://example.com/image3.jpg');
$image4 = new SocialImage('https://example.com/image4.jpg');
$image4->setSecureUrl('https://secure.example.com/image4.jpg')->setType('image/jpeg')->setWidth(800)->setHeight(600)->setAlt('Alt Text');
$builder->addImage($image4);

$array = $builder->toArray();
testPhase14BOpenGraphBuilderTestAssertSameValue('https://example.com/image2.jpg', $array[0]['content'], 'Image 1 URL correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('https://example.com/image3.jpg', $array[1]['content'], 'Image 2 URL correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('https://example.com/image4.jpg', $array[2]['content'], 'Image 3 URL correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('og:image:secure_url', $array[3]['name'], 'Image 3 secure_url name correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('https://secure.example.com/image4.jpg', $array[3]['content'], 'Image 3 secure_url content correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('og:image:type', $array[4]['name'], 'Image 3 type name correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('image/jpeg', $array[4]['content'], 'Image 3 type content correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('og:image:width', $array[5]['name'], 'Image 3 width name correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('800', $array[5]['content'], 'Image 3 width content correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('og:image:height', $array[6]['name'], 'Image 3 height name correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('600', $array[6]['content'], 'Image 3 height content correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('og:image:alt', $array[7]['name'], 'Image 3 alt name correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('Alt Text', $array[7]['content'], 'Image 3 alt content correct');

// duplicate images are not deduplicated
$builder->addImage('https://example.com/image3.jpg');
$array = $builder->toArray();
testPhase14BOpenGraphBuilderTestAssertSameValue('https://example.com/image3.jpg', $array[8]['content'], 'Duplicate image URL correct');
testPhase14BOpenGraphBuilderTestAssertSameValue(9, count($array), 'Duplicate images are kept');

// setImages(array) replaces all images
$builder->setImages([
    'https://example.com/new1.jpg',
    new SocialImage('https://example.com/new2.jpg')
]);
$array = $builder->toArray();
testPhase14BOpenGraphBuilderTestAssertSameValue(2, count($array), 'setImages replaces array');
testPhase14BOpenGraphBuilderTestAssertSameValue('https://example.com/new1.jpg', $array[0]['content'], 'setImages item 1 correct');
testPhase14BOpenGraphBuilderTestAssertSameValue('https://example.com/new2.jpg', $array[1]['content'], 'setImages item 2 correct');


// 3. Output formats and escaping
$builder = new OpenGraphBuilder();
$builder->setTitle('Title "with" quotes <&>')
        ->setImage('https://example.com/img.jpg');

$expectedArray = [
    ['name' => 'og:title', 'content' => 'Title "with" quotes <&>', 'attribute' => 'property'],
    ['name' => 'og:image', 'content' => 'https://example.com/img.jpg', 'attribute' => 'property'],
];
testPhase14BOpenGraphBuilderTestAssertSameValue($expectedArray, $builder->toArray(), 'OpenGraphBuilder escaping array format');

$expectedHtmlEscaped = '<meta property="og:title" content="Title &quot;with&quot; quotes &lt;&amp;&gt;">' . "\n" .
                       '<meta property="og:image" content="https://example.com/img.jpg">';
testPhase14BOpenGraphBuilderTestAssertSameValue($expectedHtmlEscaped, $builder->toHtml(), 'OpenGraphBuilder escaping HTML format');

$collection = $builder->toCollection();
testPhase14BOpenGraphBuilderTestAssertSameValue(true, phase14BIsInstanceOf($collection, SocialMetaCollection::class), 'toCollection returns SocialMetaCollection');
testPhase14BOpenGraphBuilderTestAssertSameValue($expectedHtmlEscaped, $collection->toHtml(), 'toCollection HTML matches');

$renderOutput = $builder->toRenderOutput();
testPhase14BOpenGraphBuilderTestAssertSameValue(true, phase14BIsInstanceOf($renderOutput, SocialMetaRenderOutput::class), 'toRenderOutput returns SocialMetaRenderOutput');
testPhase14BOpenGraphBuilderTestAssertSameValue($expectedHtmlEscaped, $renderOutput->toHtml(), 'toRenderOutput HTML matches');


if (Phase14BTestFailureCounter::$count > 0) {
    echo "\nPhase 14B Open Graph Builder tests failed with " . Phase14BTestFailureCounter::$count . " failures.\n";
    exit(1);
}

echo "Phase 14B Open Graph Builder tests passed.\n";
