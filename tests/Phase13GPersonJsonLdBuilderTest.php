<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Maatify\Seo\Web\JsonLd\Builder\PersonJsonLdBuilder;

/** @return array<string, mixed> */
function phase13GStringKeyedArray(mixed $value): array
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
function phase13GArrayField(array $data, string $key): array
{
    return phase13GStringKeyedArray($data[$key] ?? null);
}

function testPhase13GPersonJsonLdBuilderTestAssertSameValue(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException($message . "\nExpected: " . print_r($expected, true) . "\nActual: " . print_r($actual, true));
    }
}

echo "Testing PersonJsonLdBuilder...\n";

// 1. Initial State
$builder = new PersonJsonLdBuilder();
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue('https://schema.org', $data['@context'], 'Context should be schema.org');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('Person', $data['@type'], 'Type should be Person');

// 2. Setters
$builder->setName('John Doe')
        ->setUrl('https://example.com/johndoe')
        ->setDescription('A software engineer')
        ->setJobTitle('Senior Developer')
        ->setEmail('john@example.com')
        ->setTelephone('+1-555-555-1234');

$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue('John Doe', $data['name'], 'Name should be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('https://example.com/johndoe', $data['url'], 'Url should be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('A software engineer', $data['description'], 'Description should be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('Senior Developer', $data['jobTitle'], 'Job title should be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('john@example.com', $data['email'], 'Email should be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('+1-555-555-1234', $data['telephone'], 'Telephone should be set');

// 3. Image arrays
$builder->setImage('https://example.com/img1.jpg');
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue('https://example.com/img1.jpg', $data['image'], 'Image as string should work');

$builder->setImage(['https://example.com/img1.jpg', 'https://example.com/img2.jpg']);
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue(['https://example.com/img1.jpg', 'https://example.com/img2.jpg'], $data['image'], 'Image as array should work');

// 4. worksFor string becomes Organization array with name
$builder->setWorksFor('Acme Corp');
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue('Organization', phase13GArrayField($data, 'worksFor')['@type'] ?? null, 'worksFor type should be Organization');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('Acme Corp', phase13GArrayField($data, 'worksFor')['name'] ?? null, 'worksFor name should be set from string');

// 5. worksFor array is accepted and defaults @type to Organization if missing
$builder->setWorksFor([
    'name' => 'Tech Corp',
]);
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue('Organization', phase13GArrayField($data, 'worksFor')['@type'] ?? null, 'worksFor type should be accepted and defaulted to Organization');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('Tech Corp', phase13GArrayField($data, 'worksFor')['name'] ?? null, 'worksFor name should be accepted as array');

$builder->setWorksFor([
    '@type' => 'Corporation',
    'name' => 'Big Corp',
]);
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue('Corporation', phase13GArrayField($data, 'worksFor')['@type'] ?? null, 'worksFor type should not be overridden if present');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('Big Corp', phase13GArrayField($data, 'worksFor')['name'] ?? null, 'worksFor name should be present');

// 6. sameAs supports setSameAs and addSameAs
$builder->setSameAs(['https://twitter.com/johndoe']);
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue(['https://twitter.com/johndoe'], $data['sameAs'], 'setSameAs should set array');

$builder->addSameAs('https://linkedin.com/in/johndoe');
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue(['https://twitter.com/johndoe', 'https://linkedin.com/in/johndoe'], $data['sameAs'], 'addSameAs should append to array');

// 7. address array defaults @type to PostalAddress if missing
$builder->setAddress([
    'streetAddress' => '123 Main St',
]);
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue('PostalAddress', phase13GArrayField($data, 'address')['@type'] ?? null, 'address type should default to PostalAddress');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('123 Main St', phase13GArrayField($data, 'address')['streetAddress'] ?? null, 'address field should be present');

$builder->setAddress([
    '@type' => 'SomeAddress',
    'streetAddress' => '456 Main St',
]);
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue('SomeAddress', phase13GArrayField($data, 'address')['@type'] ?? null, 'address type should not be overridden if present');

// 8. setPostalAddress builds a PostalAddress array
$builder->setPostalAddress(
    '789 Main St',
    'Anytown',
    'CA',
    '90210',
    'USA'
);
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue('PostalAddress', phase13GArrayField($data, 'address')['@type'] ?? null, 'setPostalAddress should set @type PostalAddress');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('789 Main St', phase13GArrayField($data, 'address')['streetAddress'] ?? null, 'streetAddress should be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('Anytown', phase13GArrayField($data, 'address')['addressLocality'] ?? null, 'addressLocality should be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('CA', phase13GArrayField($data, 'address')['addressRegion'] ?? null, 'addressRegion should be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('90210', phase13GArrayField($data, 'address')['postalCode'] ?? null, 'postalCode should be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('USA', phase13GArrayField($data, 'address')['addressCountry'] ?? null, 'addressCountry should be set');

// Partial postal address
$builder->setPostalAddress(
    addressLocality: 'City',
    addressCountry: 'Country'
);
$data = $builder->toArray();
testPhase13GPersonJsonLdBuilderTestAssertSameValue('PostalAddress', phase13GArrayField($data, 'address')['@type'] ?? null, 'setPostalAddress should set @type PostalAddress');
testPhase13GPersonJsonLdBuilderTestAssertSameValue(false, isset(phase13GArrayField($data, 'address')['streetAddress']), 'streetAddress should not be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('City', phase13GArrayField($data, 'address')['addressLocality'] ?? null, 'addressLocality should be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue(false, isset(phase13GArrayField($data, 'address')['addressRegion']), 'addressRegion should not be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue(false, isset(phase13GArrayField($data, 'address')['postalCode']), 'postalCode should not be set');
testPhase13GPersonJsonLdBuilderTestAssertSameValue('Country', phase13GArrayField($data, 'address')['addressCountry'] ?? null, 'addressCountry should be set');

// 9. Output remains compatible with JSON-LD rendering
$json = $builder->toJson();
$decoded = json_decode($json, true);
testPhase13GPersonJsonLdBuilderTestAssertSameValue('John Doe', phase13GStringKeyedArray($decoded)['name'] ?? null, 'JSON rendering should work');

echo "PersonJsonLdBuilder passed all tests!\n";
