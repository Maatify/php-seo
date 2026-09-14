<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Maatify\Seo\Web\JsonLd\Builder\WebSiteJsonLdBuilder;

/** @return array<string, mixed> */
function phase13FStringKeyedArray(mixed $value): array
{
    if (!is_array($value)) {
        throw new \RuntimeException('Expected JSON-LD object array.');
    }

    $array = [];
    foreach ($value as $key => $item) {
        if (!is_string($key)) {
            throw new \RuntimeException('Expected string keys in JSON-LD object array.');
        }

        $array[$key] = $item;
    }

    return $array;
}

/**
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function phase13FArrayField(array $data, string $key): array
{
    return phase13FStringKeyedArray($data[$key] ?? null);
}

function testPhase13FWebSiteJsonLdBuilderTestAssertSameValue(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException($message . "\nExpected: " . print_r($expected, true) . "\nActual: " . print_r($actual, true));
    }
}

echo "Testing WebSiteJsonLdBuilder...\n";

// 1. Initial State
$builder = new WebSiteJsonLdBuilder();
$data = $builder->toArray();
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('https://schema.org', $data['@context'], 'Context should be schema.org');
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('WebSite', $data['@type'], 'Type should be WebSite');

// 2. Setters
$builder->setName('Example Site')
        ->setUrl('https://example.com')
        ->setDescription('An example website for testing');

$data = $builder->toArray();
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('Example Site', $data['name'], 'Name should be set');
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('https://example.com', $data['url'], 'Url should be set');
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('An example website for testing', $data['description'], 'Description should be set');

// 3. publisher string becomes Organization array with name
$builder->setPublisher('Example Publisher');
$data = $builder->toArray();
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('Organization', phase13FArrayField($data, 'publisher')['@type'] ?? null, 'Publisher type should be Organization');
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('Example Publisher', phase13FArrayField($data, 'publisher')['name'] ?? null, 'Publisher name should be set from string');

// 4. publisher array is accepted as provided
$builder->setPublisher([
    '@type' => 'Person',
    'name' => 'Jane Doe',
]);
$data = $builder->toArray();
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('Person', phase13FArrayField($data, 'publisher')['@type'] ?? null, 'Publisher type should be accepted as array');
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('Jane Doe', phase13FArrayField($data, 'publisher')['name'] ?? null, 'Publisher name should be accepted as array');

// 5. SearchAction includes @type, target, and query-input
$builder->setSearchAction('https://example.com/search?q={search_term_string}', 'search_term_string');
$data = $builder->toArray();
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('SearchAction', phase13FArrayField($data, 'potentialAction')['@type'] ?? null, 'SearchAction type should be SearchAction');
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('https://example.com/search?q={search_term_string}', phase13FArrayField($data, 'potentialAction')['target'] ?? null, 'SearchAction target should match');
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('required name=search_term_string', phase13FArrayField($data, 'potentialAction')['query-input'] ?? null, 'SearchAction query-input should match');

// 6. setPotentialAction adds @type SearchAction if missing
$builder->setPotentialAction([
    'target' => 'https://example.com/search2?q={query}',
    'query-input' => 'required name=query'
]);
$data = $builder->toArray();
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('SearchAction', phase13FArrayField($data, 'potentialAction')['@type'] ?? null, 'PotentialAction should default to SearchAction');
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('https://example.com/search2?q={query}', phase13FArrayField($data, 'potentialAction')['target'] ?? null, 'Target should match from array');

// 7. Output remains compatible with JSON-LD rendering
$json = $builder->toJson();
$decoded = json_decode($json, true);
testPhase13FWebSiteJsonLdBuilderTestAssertSameValue('https://example.com/search2?q={query}', phase13FArrayField(phase13FStringKeyedArray($decoded), 'potentialAction')['target'] ?? null, 'JSON rendering should work');

echo "WebSiteJsonLdBuilder passed all tests!\n";
