<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use Maatify\Seo\Exception\SeoInvalidArgumentException;
use Maatify\Seo\Shared\DTO\Schema\JsonLdSchemaDTO;
use Maatify\Seo\Web\Page\ContentSeoPresetFactory;
use Maatify\Seo\Web\Page\EcommerceSeoPresetFactory;
use Maatify\Seo\Web\Page\LocalBusinessSeoPresetFactory;
use Maatify\Seo\Web\Page\SeoPagePresetOutputDTO;

final class Batch1CTestFailureCounter { public static int $count = 0; }
function assertSame1C(mixed $expected, mixed $actual, string $message): void { if ($expected !== $actual) { Batch1CTestFailureCounter::$count++; echo "FAIL: $message\nExpected: " . print_r($expected, true) . "\nActual: " . print_r($actual, true) . "\n"; } }
function assertTrue1C(bool $actual, string $message): void { assertSame1C(true, $actual, $message); }
function assertThrowsSeo1C(callable $callback, string $message): void { try { $callback(); } catch (SeoInvalidArgumentException) { return; } Batch1CTestFailureCounter::$count++; echo "FAIL: $message\nExpected SeoInvalidArgumentException.\n"; }

/** @return array<string, mixed> */
function schema1C(SeoPagePresetOutputDTO $output, int $index): array
{
    $schemas = $output->toArray()['schemas'] ?? null;
    if (!is_array($schemas) || !isset($schemas[$index]) || !is_array($schemas[$index])) {
        throw new RuntimeException("Expected schema at index {$index}.");
    }

    $schema = [];
    foreach ($schemas[$index] as $key => $value) {
        if (!is_string($key)) {
            throw new RuntimeException('Schema keys must be strings.');
        }

        $schema[$key] = $value;
    }

    return $schema;
}

/** @return list<string> */
function schemaTypes1C(SeoPagePresetOutputDTO $output): array
{
    $schemas = $output->toArray()['schemas'] ?? null;
    if (!is_array($schemas)) {
        throw new RuntimeException('Expected schemas array.');
    }

    $types = [];
    foreach ($schemas as $schema) {
        if (!is_array($schema) || !is_string($schema['@type'] ?? null)) {
            throw new RuntimeException('Expected schema type string.');
        }

        $types[] = $schema['@type'];
    }

    return $types;
}

echo "Running Batch 1C High-Level Domain SEO Preset Factory Tests...\n\n";

$product = EcommerceSeoPresetFactory::productDetail('Blue Shirt', 'Cotton shirt', ['name' => 'Blue Shirt', 'price' => '29.99', 'currency' => 'USD'], ['canonicalUrl' => 'https://example.com/p/blue-shirt']);
assertSame1C('Product', schema1C($product, 0)['@type'], 'Product detail returns Product schema from lower page preset');
assertSame1C('https://example.com/p/blue-shirt', $product->canonicalUrl, 'Product detail passes canonical option through');

$category = EcommerceSeoPresetFactory::categoryListing('Shirts', 'All shirts', [['url' => 'https://example.com/p/blue-shirt', 'name' => 'Blue Shirt']]);
$search = EcommerceSeoPresetFactory::searchResults('Search shirts', 'Search results', ['https://example.com/p/blue-shirt']);
$brand = EcommerceSeoPresetFactory::brandPage('Brand', 'Brand products', ['https://example.com/p/blue-shirt']);
$offer = EcommerceSeoPresetFactory::offerLanding('Sale', 'Summer sale', ['price' => '19.99', 'currency' => 'USD'], [
    'canonicalBaseUrl' => 'https://example.com',
    'canonicalPath' => '/sale',
    'queryParams' => ['page' => 2, 'utm' => 'x'],
    'allowedQueryParams' => ['page'],
]);
assertSame1C('ItemList', schema1C($category, 0)['@type'], 'Category listing produces ItemList output');
assertSame1C('ItemList', schema1C($search, 0)['@type'], 'Search results produces valid ItemList output');
assertSame1C('noindex, follow', $search->robots, 'Search results defaults to noindex/follow');
assertSame1C('ItemList', schema1C($brand, 0)['@type'], 'Brand page produces valid ItemList output');
assertTrue1C(in_array('Offer', schemaTypes1C($offer), true), 'Offer landing includes Offer schema');
assertSame1C('https://example.com/sale?page=2', schema1C($offer, 1)['url'], 'Offer schema URL matches canonical builder behavior');

$indexedSearch = EcommerceSeoPresetFactory::searchResults('Indexable Search', 'Curated search results', [], ['robots' => ['index', 'follow']]);
assertSame1C('index, follow', $indexedSearch->robots, 'Search results robots override is explicit via robots option');

$article = ContentSeoPresetFactory::article('Article', 'Desc', ['author' => 'Jane', 'datePublished' => '2026-07-04']);
$blog = ContentSeoPresetFactory::blogPost('Blog', 'Desc', ['author' => 'Jane', 'datePublished' => '2026-07-04']);
$news = ContentSeoPresetFactory::newsArticle('News', 'Desc', ['author' => 'Jane', 'datePublished' => '2026-07-04']);
assertSame1C('Article', schema1C($article, 0)['@type'], 'Article preset uses Article type');
assertSame1C('BlogPosting', schema1C($blog, 0)['@type'], 'Blog post preset uses BlogPosting type');
assertSame1C('NewsArticle', schema1C($news, 0)['@type'], 'News article preset uses NewsArticle type');
assertSame1C('ItemList', schema1C(ContentSeoPresetFactory::tagPage('SEO', 'Tagged posts', ['https://example.com/post']), 0)['@type'], 'Tag page produces ItemList output');
assertSame1C('WebPage', schema1C(ContentSeoPresetFactory::authorPage('Jane Doe', 'Author', ['name' => 'Jane Doe']), 0)['@type'], 'Author page produces generic WebPage output');

$extra = new JsonLdSchemaDTO(['@context' => 'https://schema.org', '@type' => 'Thing', 'name' => 'Extra']);
$business = ['name' => 'Example Plumbing', 'telephone' => '+15555550100', 'address' => ['streetAddress' => '1 Main St']];
$home = LocalBusinessSeoPresetFactory::businessHome('Example Plumbing', 'Local plumber', $business, ['canonicalUrl' => 'https://example.com', 'extraSchemas' => [$extra]]);
$location = LocalBusinessSeoPresetFactory::locationPage('Downtown Plumbing', 'Downtown location', $business, ['canonicalUrl' => 'https://example.com/downtown']);
$service = LocalBusinessSeoPresetFactory::servicePage('Drain Cleaning', 'Drain cleaning service', ['name' => 'Drain Cleaning', 'serviceType' => 'Plumbing'], $business, ['canonicalUrl' => 'https://example.com/drain-cleaning']);
$contact = LocalBusinessSeoPresetFactory::contactPage('Contact Us', 'Contact Example Plumbing', $business, ['contactType' => 'customer service'], [
    'canonicalBaseUrl' => 'https://example.com',
    'canonicalPath' => '/contact',
    'queryParams' => ['ref' => 'local', 'utm' => 'x'],
    'allowedQueryParams' => ['ref'],
]);
assertTrue1C(in_array('LocalBusiness', schemaTypes1C($home), true), 'Business home includes LocalBusiness schema');
assertTrue1C(in_array('Thing', schemaTypes1C($home), true), 'Business home preserves extra schemas');
assertTrue1C(in_array('LocalBusiness', schemaTypes1C($location), true), 'Location page includes LocalBusiness schema');
assertTrue1C(in_array('Service', schemaTypes1C($service), true), 'Service page includes Service schema');
assertTrue1C(in_array('ContactPage', schemaTypes1C($contact), true), 'Contact page includes ContactPage schema');
assertSame1C('https://example.com/contact?ref=local', schema1C($contact, 2)['url'], 'ContactPage schema URL matches canonical builder behavior');

$passThrough = EcommerceSeoPresetFactory::categoryListing('Filtered Shirts', 'All shirts', [], ['canonicalBaseUrl' => 'https://example.com', 'canonicalPath' => '/shirts', 'queryParams' => ['page' => 2, 'utm' => 'x'], 'allowedQueryParams' => ['page'], 'robots' => ['index', 'follow'], 'imageUrl' => 'https://example.com/shirts.jpg', 'siteName' => 'Example', 'locale' => 'en_US', 'twitterSite' => '@example', 'twitterCreator' => '@jane', 'breadcrumbs' => [['name' => 'Home', 'url' => 'https://example.com']]]);
assertSame1C('https://example.com/shirts?page=2', $passThrough->canonicalUrl, 'Options pass-through preserves canonical builder options');
assertSame1C('BreadcrumbList', schema1C($passThrough, 1)['@type'], 'Options pass-through preserves breadcrumbs');
assertTrue1C(str_contains($passThrough->socialHtml, '@jane'), 'Options pass-through preserves social preview options');

assertThrowsSeo1C(static fn () => EcommerceSeoPresetFactory::productDetail('Bad', null, []), 'Product detail requires product name');
assertThrowsSeo1C(static fn () => ContentSeoPresetFactory::article('Bad', null, ['datePublished' => '2026-07-04']), 'Article requires author');
assertThrowsSeo1C(static fn () => ContentSeoPresetFactory::blogPost('Bad', null, ['author' => 'Jane']), 'Blog post requires datePublished');
assertThrowsSeo1C(static fn () => LocalBusinessSeoPresetFactory::businessHome('Bad', null, ['name' => 'Business']), 'Local business requires URL/canonical or page schema data');
assertTrue1C(!str_contains($home->html . $service->html . $contact->html, 'Illuminate\\') && !str_contains($home->html . $service->html . $contact->html, 'Symfony\\') && !str_contains($home->html . $service->html . $contact->html, 'Response'), 'Domain presets have no framework or HTTP coupling');

if (Batch1CTestFailureCounter::$count > 0) { echo "FAILED with " . Batch1CTestFailureCounter::$count . " errors.\n"; exit(1); }
echo "SUCCESS: All tests passed.\n";
