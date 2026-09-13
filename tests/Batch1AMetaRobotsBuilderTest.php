<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../src/Web/Robots/MetaRobotsBuilder.php';
require_once __DIR__ . '/../src/Exception/SeoErrorCode.php';
require_once __DIR__ . '/../src/Exception/SeoExceptionInterface.php';
require_once __DIR__ . '/../src/Exception/SeoInvalidArgumentException.php';

use Maatify\Seo\Web\Robots\MetaRobotsBuilder;
use Maatify\Seo\Exception\SeoInvalidArgumentException;

/**
 * Asserts that two values are exactly identical.
 */
function testBatch1AMetaRobotsBuilderTestAssertSameValue(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        $expectedStr = is_scalar($expected) ? (string) $expected : print_r($expected, true);
        $actualStr = is_scalar($actual) ? (string) $actual : print_r($actual, true);
        throw new \RuntimeException("Assertion failed: $message. Expected: $expectedStr, Actual: $actualStr");
    }
}

/**
 * Asserts that an exception is thrown.
 */
function testBatch1AMetaRobotsBuilderTestAssertThrowsException(callable $callback, string $expectedExceptionClass, string $message = ''): void
{
    try {
        $callback();
    } catch (\Throwable $e) {
        if (!($e instanceof $expectedExceptionClass)) {
            $actualClass = get_class($e);
            throw new \RuntimeException("Assertion failed: Expected exception $expectedExceptionClass, but got $actualClass. $message");
        }
        return;
    }
    throw new \RuntimeException("Assertion failed: Expected exception $expectedExceptionClass to be thrown. $message");
}

echo "Running Batch 1A MetaRobotsBuilder tests...\n";

// Test: All public methods and insertion order
$builder = new MetaRobotsBuilder();
$builder->index()
        ->follow()
        ->noArchive()
        ->noSnippet()
        ->noImageIndex()
        ->noTranslate()
        ->maxSnippet(50)
        ->maxImagePreview('large')
        ->maxVideoPreview(10)
        ->unavailableAfter('2023-12-31')
        ->add('custom-directive');

testBatch1AMetaRobotsBuilderTestAssertSameValue(
    'index, follow, noarchive, nosnippet, noimageindex, notranslate, max-snippet:50, max-image-preview:large, max-video-preview:10, unavailable_after:2023-12-31, custom-directive',
    $builder->build(),
    'All public methods and insertion order should be correct'
);

// Test: No duplicates
$builder->clear();
$builder->index()
        ->index()
        ->add('custom')
        ->add('custom');

testBatch1AMetaRobotsBuilderTestAssertSameValue(
    'index, custom',
    $builder->build(),
    'Directives should not be duplicated'
);

// Test: index/noindex exclusivity
$builder->clear();
$builder->index();
testBatch1AMetaRobotsBuilderTestAssertSameValue('index', $builder->build());
$builder->noIndex();
testBatch1AMetaRobotsBuilderTestAssertSameValue('noindex', $builder->build(), 'noIndex should replace index');
$builder->index();
testBatch1AMetaRobotsBuilderTestAssertSameValue('index', $builder->build(), 'index should replace noindex');
$builder->add('noindex');
testBatch1AMetaRobotsBuilderTestAssertSameValue('noindex', $builder->build(), 'add(noindex) should replace index');

// Test: follow/nofollow exclusivity
$builder->clear();
$builder->follow();
testBatch1AMetaRobotsBuilderTestAssertSameValue('follow', $builder->build());
$builder->noFollow();
testBatch1AMetaRobotsBuilderTestAssertSameValue('nofollow', $builder->build(), 'noFollow should replace follow');
$builder->follow();
testBatch1AMetaRobotsBuilderTestAssertSameValue('follow', $builder->build(), 'follow should replace nofollow');
$builder->add('nofollow');
testBatch1AMetaRobotsBuilderTestAssertSameValue('nofollow', $builder->build(), 'add(nofollow) should replace follow');

// Test: max-* replacement
$builder->clear();
$builder->maxSnippet(10)->maxSnippet(20);
testBatch1AMetaRobotsBuilderTestAssertSameValue('max-snippet:20', $builder->build(), 'max-snippet should replace previous value');
$builder->maxImagePreview('standard')->maxImagePreview('none');
testBatch1AMetaRobotsBuilderTestAssertSameValue('max-snippet:20, max-image-preview:none', $builder->build(), 'max-image-preview should replace previous value');
$builder->maxVideoPreview(5)->maxVideoPreview(15);
testBatch1AMetaRobotsBuilderTestAssertSameValue('max-snippet:20, max-image-preview:none, max-video-preview:15', $builder->build(), 'max-video-preview should replace previous value');
$builder->add('max-snippet:30');
testBatch1AMetaRobotsBuilderTestAssertSameValue('max-image-preview:none, max-video-preview:15, max-snippet:30', $builder->build(), 'add(max-snippet:*) should replace previous value and move to end');

// Test: unavailable_after replacement
$builder->clear();
$builder->unavailableAfter('date1')->unavailableAfter('date2');
testBatch1AMetaRobotsBuilderTestAssertSameValue('unavailable_after:date2', $builder->build(), 'unavailable_after should replace previous value');
$builder->add('unavailable_after:date3');
testBatch1AMetaRobotsBuilderTestAssertSameValue('unavailable_after:date3', $builder->build(), 'add(unavailable_after:*) should replace previous value');

// Test: values below Google's -1 lower bound throw SeoInvalidArgumentException
testBatch1AMetaRobotsBuilderTestAssertThrowsException(
    fn() => (new MetaRobotsBuilder())->maxSnippet(-2),
    SeoInvalidArgumentException::class,
    'max-snippet values below -1 should throw'
);
testBatch1AMetaRobotsBuilderTestAssertThrowsException(
    fn() => (new MetaRobotsBuilder())->maxVideoPreview(-2),
    SeoInvalidArgumentException::class,
    'max-video-preview values below -1 should throw'
);
testBatch1AMetaRobotsBuilderTestAssertSameValue('max-snippet:-1', (new MetaRobotsBuilder())->maxSnippet(-1)->build(), 'max-snippet -1 should be valid');
testBatch1AMetaRobotsBuilderTestAssertSameValue('max-video-preview:-1', (new MetaRobotsBuilder())->maxVideoPreview(-1)->build(), 'max-video-preview -1 should be valid');

// Test: invalid max-image-preview throws SeoInvalidArgumentException
testBatch1AMetaRobotsBuilderTestAssertThrowsException(
    fn() => (new MetaRobotsBuilder())->maxImagePreview('invalid'),
    SeoInvalidArgumentException::class,
    'Invalid max-image-preview should throw'
);

// Test: Output methods (build, __toString, toArray, toHtml escaping)
$builder->clear();
$builder->index()->add('bad"char>');

testBatch1AMetaRobotsBuilderTestAssertSameValue('index, bad"char>', $builder->build(), 'build() works');
testBatch1AMetaRobotsBuilderTestAssertSameValue('index, bad"char>', (string) $builder, '__toString() works');
testBatch1AMetaRobotsBuilderTestAssertSameValue(['index', 'bad"char>'], $builder->toArray(), 'toArray() works');
testBatch1AMetaRobotsBuilderTestAssertSameValue(
    '<meta name="robots" content="index, bad&quot;char&gt;">',
    $builder->toHtml(),
    'toHtml() escapes correctly'
);

// Test: has(), remove(), clear()
$builder->clear();
$builder->index()->follow();
testBatch1AMetaRobotsBuilderTestAssertSameValue(true, $builder->has('index'), 'has() works for existing');
testBatch1AMetaRobotsBuilderTestAssertSameValue(false, $builder->has('noindex'), 'has() works for missing');

$builder->remove('index');
testBatch1AMetaRobotsBuilderTestAssertSameValue('follow', $builder->build(), 'remove() works');

$builder->clear();
testBatch1AMetaRobotsBuilderTestAssertSameValue('', $builder->build(), 'clear() works');

echo "All MetaRobotsBuilder tests passed!\n";
