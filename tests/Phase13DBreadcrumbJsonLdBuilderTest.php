<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/../src/Exception/SeoExceptionInterface.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/JsonLdBuildException.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/JsonLdBuilderInterface.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/JsonLdBuilderTrait.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/AbstractJsonLdBuilder.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/BreadcrumbJsonLdBuilder.php';

use Maatify\Seo\Web\JsonLd\Builder\BreadcrumbJsonLdBuilder;

/**
 * @param array<string, mixed> $array
 * @return array{'@context': string, '@type': string, itemListElement: list<array{'@type': string, position: int, name: string, item: string}>}
 */
function phase13DBreadcrumbArray(array $array): array
{
    $context = $array['@context'] ?? null;
    $type = $array['@type'] ?? null;
    $items = $array['itemListElement'] ?? null;
    if (!is_string($context) || !is_string($type) || !is_array($items)) {
        throw new \RuntimeException('Breadcrumb output has an invalid top-level shape.');
    }

    $typedItems = [];
    foreach ($items as $item) {
        if (!is_array($item)
            || !is_string($item['@type'] ?? null)
            || !is_int($item['position'] ?? null)
            || !is_string($item['name'] ?? null)
            || !is_string($item['item'] ?? null)) {
            throw new \RuntimeException('Breadcrumb item has an invalid shape.');
        }

        $typedItems[] = [
            '@type' => $item['@type'],
            'position' => $item['position'],
            'name' => $item['name'],
            'item' => $item['item'],
        ];
    }

    return ['@context' => $context, '@type' => $type, 'itemListElement' => $typedItems];
}

function testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException("Test Failed: $message\nExpected: " . print_r($expected, true) . "\nActual: " . print_r($actual, true));
    }
}

function testEmptyBreadcrumbs(): void
{
    $builder = new BreadcrumbJsonLdBuilder();
    $array = phase13DBreadcrumbArray($builder->toArray());

    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue('https://schema.org', $array['@context'], 'Context should be schema.org');
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue('BreadcrumbList', $array['@type'], 'Type should be BreadcrumbList');
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue([], $array['itemListElement'], 'itemListElement should be empty initially');
}

function testAddSingleItem(): void
{
    $builder = new BreadcrumbJsonLdBuilder();
    $builder->addItem('Home', 'https://example.com');

    $array = phase13DBreadcrumbArray($builder->toArray());
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue(1, count($array['itemListElement']), 'Should have 1 item');
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue('ListItem', $array['itemListElement'][0]['@type'], 'Item type should be ListItem');
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue(1, $array['itemListElement'][0]['position'], 'Position should be 1');
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue('Home', $array['itemListElement'][0]['name'], 'Name should be Home');
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue('https://example.com', $array['itemListElement'][0]['item'], 'Item url should be correct');
}

function testAddMultipleItemsAndOrder(): void
{
    $builder = new BreadcrumbJsonLdBuilder();
    $builder->addItems([
        ['name' => 'Home', 'url' => 'https://example.com'],
        ['name' => 'Category', 'url' => 'https://example.com/category'],
    ]);
    $builder->addBreadcrumb('Product', 'https://example.com/category/product');

    $array = phase13DBreadcrumbArray($builder->toArray());
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue(3, count($array['itemListElement']), 'Should have 3 items');

    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue(1, $array['itemListElement'][0]['position'], 'Item 1 position should be 1');
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue('Home', $array['itemListElement'][0]['name'], 'Item 1 name should be Home');

    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue(2, $array['itemListElement'][1]['position'], 'Item 2 position should be 2');
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue('Category', $array['itemListElement'][1]['name'], 'Item 2 name should be Category');

    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue(3, $array['itemListElement'][2]['position'], 'Item 3 position should be 3');
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue('Product', $array['itemListElement'][2]['name'], 'Item 3 name should be Product');
}

function testClearItems(): void
{
    $builder = new BreadcrumbJsonLdBuilder();
    $builder->addItem('Home', 'https://example.com');
    $builder->clearItems();

    $array = phase13DBreadcrumbArray($builder->toArray());
    testPhase13DBreadcrumbJsonLdBuilderTestAssertSameValue([], $array['itemListElement'], 'itemListElement should be empty after clear');
}

try {
    testEmptyBreadcrumbs();
    testAddSingleItem();
    testAddMultipleItemsAndOrder();
    testClearItems();
    echo "BreadcrumbJsonLdBuilderTest passed successfully.\n";
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
