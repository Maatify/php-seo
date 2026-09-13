<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../src/Exception/SeoErrorCode.php';
require_once __DIR__ . '/../src/Exception/SeoExceptionInterface.php';
require_once __DIR__ . '/../src/Exception/SeoInvalidArgumentException.php';
require_once __DIR__ . '/../src/Web/Hreflang/HreflangLinkBuilder.php';
require_once __DIR__ . '/../src/Web/Hreflang/HreflangLinkDTO.php';
require_once __DIR__ . '/../src/Web/Hreflang/HreflangLinkRenderer.php';
require_once __DIR__ . '/../src/Shared/Service/Internal/HreflangTagNormalizer.php';

use Maatify\Seo\Exception\SeoInvalidArgumentException;
use Maatify\Seo\Web\Hreflang\HreflangLinkBuilder;
use Maatify\Seo\Web\Hreflang\HreflangLinkDTO;
use Maatify\Seo\Web\Hreflang\HreflangLinkRenderer;

function testBatch3HreflangHeadLinkBuilderTestAssertSameValue(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException("Assertion failed: {$message}. Expected: " . print_r($expected, true) . ' Actual: ' . print_r($actual, true));
    }
}

function testBatch3HreflangHeadLinkBuilderTestAssertThrowsException(callable $callback, string $expectedExceptionClass, string $message = ''): void
{
    try {
        $callback();
    } catch (Throwable $exception) {
        if ($exception instanceof $expectedExceptionClass) {
            return;
        }

        throw new RuntimeException("Assertion failed: {$message}. Expected {$expectedExceptionClass}, got " . get_class($exception));
    }

    throw new RuntimeException("Assertion failed: {$message}. Expected {$expectedExceptionClass} to be thrown.");
}

echo "Running Batch 3 Hreflang Head Link Builder tests...\n";

$link = new HreflangLinkDTO(' EN_us ', 'https://example.com/en/page');
testBatch3HreflangHeadLinkBuilderTestAssertSameValue(['hreflang' => 'en-US', 'url' => 'https://example.com/en/page'], $link->toArray(), 'DTO should normalize and serialize');
testBatch3HreflangHeadLinkBuilderTestAssertSameValue($link->toArray(), $link->jsonSerialize(), 'jsonSerialize should match toArray');

$builder = new HreflangLinkBuilder();
$builder->add('en', 'https://example.com/en/page');
testBatch3HreflangHeadLinkBuilderTestAssertSameValue([['hreflang' => 'en', 'url' => 'https://example.com/en/page']], $builder->toArray(), 'Single link creation');

$builder = new HreflangLinkBuilder();
$builder->addMany([
    ['hreflang' => 'en-US', 'url' => 'https://example.com/en/page'],
    ['hreflang' => 'ar-eg', 'url' => 'https://example.com/ar/page'],
    'fr' => 'https://example.com/fr/page',
    new HreflangLinkDTO('de', 'https://example.com/de/page'),
]);
testBatch3HreflangHeadLinkBuilderTestAssertSameValue(
    [
        ['hreflang' => 'en-US', 'url' => 'https://example.com/en/page'],
        ['hreflang' => 'ar-EG', 'url' => 'https://example.com/ar/page'],
        ['hreflang' => 'fr', 'url' => 'https://example.com/fr/page'],
        ['hreflang' => 'de', 'url' => 'https://example.com/de/page'],
    ],
    $builder->toArray(),
    'Many links creation'
);

$builder->xDefault('https://example.com/page');
testBatch3HreflangHeadLinkBuilderTestAssertSameValue('x-default', $builder->all()[4]->hreflang, 'x-default creation');

$builder = new HreflangLinkBuilder();
$builder->add('en', 'https://example.com/first')->add('en', 'https://example.com/ignored');
testBatch3HreflangHeadLinkBuilderTestAssertSameValue('https://example.com/first', $builder->all()[0]->url, 'Duplicate add should keep first value');
$builder->replace('en', 'https://example.com/replaced');
testBatch3HreflangHeadLinkBuilderTestAssertSameValue('https://example.com/replaced', $builder->all()[0]->url, 'Explicit replace should replace duplicate hreflang');

testBatch3HreflangHeadLinkBuilderTestAssertThrowsException(fn() => (new HreflangLinkBuilder())->add('', 'https://example.com'), SeoInvalidArgumentException::class, 'Empty hreflang should throw');
testBatch3HreflangHeadLinkBuilderTestAssertThrowsException(fn() => (new HreflangLinkBuilder())->add('en', ''), SeoInvalidArgumentException::class, 'Empty URL should throw');
testBatch3HreflangHeadLinkBuilderTestAssertThrowsException(fn() => (new HreflangLinkBuilder())->addMany([['hreflang' => 'en']]), SeoInvalidArgumentException::class, 'Invalid addMany row should throw');

$renderer = new HreflangLinkRenderer();
$escaped = $renderer->render(new HreflangLinkDTO('en', 'https://example.com/en/page?q="test"&sort=asc'));
testBatch3HreflangHeadLinkBuilderTestAssertSameValue('<link rel="alternate" hreflang="en" href="https://example.com/en/page?q=&quot;test&quot;&amp;sort=asc">', $escaped, 'Rendered HTML should be escaped');

$builder = new HreflangLinkBuilder();
$builder->add('en', 'https://example.com/en/page')->add('ar', 'https://example.com/ar/page');
testBatch3HreflangHeadLinkBuilderTestAssertSameValue(
    '<link rel="alternate" hreflang="en" href="https://example.com/en/page">' . "\n" . '<link rel="alternate" hreflang="ar" href="https://example.com/ar/page">',
    $builder->render(),
    'Builder render should output head link tags'
);

testBatch3HreflangHeadLinkBuilderTestAssertSameValue(true, trim($builder->render()) !== "" && !class_exists('Illuminate\\Http\\Response') && !class_exists('Symfony\\Component\\HttpFoundation\\Response'), 'No framework/HTTP coupling');

echo "SUCCESS: All tests passed.\n";
exit(0);
