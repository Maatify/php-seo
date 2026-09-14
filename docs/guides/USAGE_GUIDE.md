# Maatify SEO Library Usage Guide

## 1. Overview

The Maatify SEO library provides robust, framework-agnostic tools to manage SEO metadata, schema generation (JSON-LD), redirects, slug history, and sitemaps.

For the current package contract and public runtime inventory, see the
[canonical Package Reference](../../SEO_PACKAGE_REFERENCE.md).

**What it provides:**
*   Value Objects/DTOs for SEO data structures (e.g., `MetaTagsDTO`, `JsonLdSchemaDTO`).
*   Core logic for schema generation, resolving redirects, and generating in-memory sitemaps.
*   Optional rendering helpers to convert DTOs into plain HTML/XML strings.
*   Admin services and DTOs for overriding SEO metadata, managing redirects, and tracking slug changes.

**What it does NOT provide:**
*   It does **not** handle HTTP requests or responses.
*   It does **not** provide framework routing, controllers, or middlewares.
*   It does **not** couple to any specific templating engine (like Twig or Blade).
*   It does **not** enforce ORM patterns. The Host supplies PDO; persistence uses package-provided PDO repositories and shipped schemas.

**Host Application Responsibility:**
The host application is strictly responsible for managing all HTTP interactions (requests, responses, controllers, routes), utilizing preferred template engines, and providing implementations for the necessary host contracts (like `HostEntityProviderInterface` and `HostUrlGeneratorInterface`). You integrate the SEO library by using its builders and renderers within your existing architecture to get the SEO output, and then you send that output in your own responses.

## Capability decision map

Use this map to find the smallest API surface for the task. The links lead to a
runnable example or the guide section that explains the returned data. The
[canonical Package Reference](../../SEO_PACKAGE_REFERENCE.md) remains the
package-level contract; this guide is practical usage guidance.

| If you need to… | Start here | Runnable example |
| --- | --- | --- |
| render a complete HTML `<head>` | [Basic head rendering](#2-basic-seo-head-rendering-example) | [`basic-head-render.php`](../../examples/basic-head-render.php) |
| keep metadata as reusable DTO data or render separate head sections | [Rendered output DTO](#3-rendered-output-dto-example) | [`observable-output-showcase.php`](../../examples/observable-output-showcase.php) |
| build metadata fluently | [Fluent builder](#4-fluentseobuilder-example) | [`basic-head-render.php`](../../examples/basic-head-render.php) |
| resolve defaults, SEO overrides, and a Host-generated canonical | [Meta generation](#metadata-orchestration-with-metageneratorservice) | [`seo-override-meta-generation.php`](../../examples/seo-override-meta-generation.php) |
| generate JSON-LD or Product/Offer/variant structures | [JSON-LD](#5-json-ld-examples), [advanced product data](#6-advanced-product-structured-data) | [`jsonld-builders.php`](../../examples/jsonld-builders.php), [`advanced-product-structured-data.php`](../../examples/advanced-product-structured-data.php) |
| adapt optional Spatie schema objects | [Spatie adapter](#7-optional-spatie-schema-adapter-example) | [`schema-output.php`](../../examples/schema-output.php) |
| render sitemap URL-set XML with extensions | [Sitemap URL-set](#8-sitemap-xml-string-example) | [`sitemap-output.php`](../../examples/sitemap-output.php) |
| render sitemap index XML | [Sitemap index](#9-sitemap-index-xml-string-example) | [`sitemap-output.php`](../../examples/sitemap-output.php) |
| produce `robots.txt` or a robots meta directive | [Robots output](#10-robotstxt-string-output-example), [robots meta](#meta-robots-directives) | [`robots-output.php`](../../examples/robots-output.php), [`meta-robots-canonical.php`](../../examples/meta-robots-canonical.php) |
| build canonical URLs | [Canonical and hreflang](#canonical-urls-and-hreflang) | [`meta-robots-canonical.php`](../../examples/meta-robots-canonical.php) |
| compose hreflang links | [Hreflang generation](#canonical-urls-and-hreflang) | [`hreflang-generation.php`](../../examples/hreflang-generation.php) |
| render Open Graph and Twitter-compatible tags | [Social metadata](#social-metadata) | [`social-builders.php`](../../examples/social-builders.php) |
| compose common page metadata and schemas | [Page presets](#page-presets) | [`seo-page-presets.php`](../../examples/seo-page-presets.php) |
| compose one page payload and then render it | [Page rendering](#page-rendering-orchestration) | [`seo-page-render.php`](../../examples/seo-page-render.php) |
| resolve redirects, record slug changes, or manage SEO overrides | [Admin-domain utilities](#admin-domain-utilities) | [`redirect-slug-history.php`](../../examples/redirect-slug-history.php), [`seo-override-meta-generation.php`](../../examples/seo-override-meta-generation.php) |
| build search-result or social preview data | [Admin previews](#admin-domain-utilities) | [`admin-previews.php`](../../examples/admin-previews.php) |
| import or export package metadata | [Metadata import/export](#admin-domain-utilities) | [`import-export.php`](../../examples/import-export.php) |
| validate, score, or report on metadata | [Validation](#11-seo-metadata-validation-example) | [`seo-validation.php`](../../examples/seo-validation.php), [`product-seo-audit.php`](../../examples/product-seo-audit.php) |
| run protocol/provider companion diagnostics | [Companion diagnostics](#companion-protocol-and-provider-profile-diagnostics) | [`protocol-provider-diagnostics.php`](../../examples/protocol-provider-diagnostics.php) |
| map Search Console inspection evidence | [Search Console](#15-optional-search-console-indexed-result-verification) | [`search-console-response-mapping.php`](../../examples/search-console-response-mapping.php) |
| map Merchant Center product diagnostics | [Merchant Center](#16-optional-merchant-center-eligibility-diagnostics) | [`merchant-center-diagnostics.php`](../../examples/merchant-center-diagnostics.php) |

The examples are self-contained illustrations. Samples returned from provider
fixtures below demonstrate mapping shape only; they are not live provider
responses or evidence about a real site/account.

Each walkthrough identifies the consumer input and public call, shows an
observed result, and states the package guarantee, boundary/Host next step,
and representative failure or diagnostic behavior when that affects fit.

---

## 2. Basic SEO Head Rendering Example

You can generate SEO head tags by creating a `MetaTagsDTO` and rendering it with the `SeoHeadHtmlRenderer`. This helper outputs standard HTML strings.

```php
use Maatify\Seo\Shared\DTO\MetaTagsDTO;
use Maatify\Seo\Web\Render\SeoHeadHtmlRenderer;

// 1. Prepare your data
$metaTags = new MetaTagsDTO(
    title: 'My Awesome Product',
    description: 'The best product you will ever buy.',
    canonicalUrl: 'https://example.com/products/awesome-product',
    robots: 'index,follow',
    openGraphTitle: 'My Awesome Product',
    openGraphDescription: 'The best product you will ever buy.',
    openGraphUrl: 'https://example.com/products/awesome-product',
    openGraphType: 'product',
    openGraphImage: 'https://cdn.example.com/images/awesome-product.jpg',
    twitterCard: 'summary_large_image',
    twitterTitle: 'My Awesome Product',
    twitterDescription: 'The best product you will ever buy.',
    twitterImage: 'https://cdn.example.com/images/awesome-product-twitter.jpg'
);

$schemas = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => 'My Awesome Product',
    ]
];

// 2. Render to a full HTML string
$renderer = new SeoHeadHtmlRenderer();
$headHtml = $renderer->render($metaTags, $schemas);

// 3. Inject $headHtml into your template (e.g., in `<head>`)
echo $headHtml;
```

Running [`basic-head-render.php`](../../examples/basic-head-render.php) produces
the following representative tags from those inputs. The renderer escapes HTML
attribute/text values; line breaks and attribute ordering are not an API
contract.

```html
<title>My Basic Webpage - Example.com</title>
<meta name="description" content="This is a basic example of rendering SEO head tags.">
<link rel="canonical" href="https://example.com/basic-page">
<meta name="robots" content="index,follow">
<meta property="og:title" content="My Basic Webpage">
<meta property="og:description" content="This is a basic example of rendering SEO head tags via OpenGraph.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://example.com/basic-page">
<meta property="og:image" content="https://example.com/images/basic-page.jpg">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="My Basic Webpage">
<meta name="twitter:description" content="This is a basic example of rendering SEO head tags.">
<meta name="twitter:image" content="https://example.com/images/basic-page.jpg">
<script type="application/ld+json">{"@context":"https://schema.org","@type":"WebPage","name":"My Basic Webpage","description":"This is a basic example of rendering SEO head tags."}</script>
```

The renderer returns a string. Put it in the Host template and let the Host
send the final HTTP response; it does not write to the response itself.

---

## 3. Rendered Output DTO Example

If you prefer to inject individual sections of the SEO markup into different parts of your template layout, you can use the `renderDto()` method to obtain a `SeoHeadHtmlDTO`.

```php
use Maatify\Seo\Web\Render\SeoHeadHtmlRenderer;

$renderer = new SeoHeadHtmlRenderer();

// Assuming $metaTags and $schemas are already created:
$dto = $renderer->renderDto($metaTags, $schemas);

// The DTO provides access to specific sections:
echo $dto->metaHtml;         // Outputs <title>, <meta name="description">, <link rel="canonical">, <meta name="robots">
echo $dto->openGraphHtml;    // Outputs the rendered Open Graph section
echo $dto->twitterCardHtml;  // Outputs the rendered Twitter compatibility section
echo $dto->jsonLdHtml;       // Outputs the rendered JSON-LD script section
echo $dto->fullHtml;         // Output the concatenated complete head HTML
```

For [`basic-head-render.php`](../../examples/basic-head-render.php), the
serialized DTO returns these four section values:

```json
{
  "meta_html": "<title>My Basic Webpage - Example.com</title>\n<meta name=\"description\" content=\"This is a basic example of rendering SEO head tags.\">\n<link rel=\"canonical\" href=\"https://example.com/basic-page\">\n<meta name=\"robots\" content=\"index,follow\">",
  "open_graph_html": "<meta property=\"og:title\" content=\"My Basic Webpage\">\n<meta property=\"og:description\" content=\"This is a basic example of rendering SEO head tags via OpenGraph.\">\n<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:url\" content=\"https://example.com/basic-page\">\n<meta property=\"og:image\" content=\"https://example.com/images/basic-page.jpg\">",
  "twitter_card_html": "<meta name=\"twitter:card\" content=\"summary_large_image\">\n<meta name=\"twitter:title\" content=\"My Basic Webpage\">\n<meta name=\"twitter:description\" content=\"This is a basic example of rendering SEO head tags.\">\n<meta name=\"twitter:image\" content=\"https://example.com/images/basic-page.jpg\">",
  "json_ld_html": "<script type=\"application/ld+json\">{\"@context\":\"https://schema.org\",\"@type\":\"WebPage\",\"name\":\"My Basic Webpage\",\"description\":\"This is a basic example of rendering SEO head tags.\"}</script>"
}
```

`fullHtml` is the complete concatenated HTML string shown in section 2. Use this
DTO when the Host template places sections independently;
use `render()` when the Host wants one complete head string. The runnable
example prints the complete field values.

> **Twitter/X compatibility boundary:** Twitter Card builders and rendering remain
> available for compatibility. The architecture audit source-verified Open Graph
> behavior but did not source-verify Twitter/X provider conformance; this guide does
> not make a stronger provider claim.

## Social metadata

Use `OpenGraphBuilder`, `TwitterCardBuilder`, or `SocialPreviewBuilder` when the
Host wants social fields without composing them into the complete HTML head.
Inputs are page title, description, URL, image, and optional site/type/card
values. The builders return plain DTO-shaped values or rendered tag strings;
they do not submit metadata to a social platform or prove platform-specific
card acceptance.

[`social-builders.php`](../../examples/social-builders.php) renders tags such as:

```html
<meta property="og:title" content="Independent OpenGraph Title">
<meta property="og:description" content="Independent description.">
<meta property="og:type" content="article">
<meta property="og:url" content="https://example.com/article">
<meta property="og:image" content="https://example.com/og-image.jpg">
<meta property="og:site_name" content="My Awesome Blog">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Independent Twitter Title">
<meta name="twitter:description" content="Twitter specific description.">
<meta name="twitter:image" content="https://example.com/twitter-image.jpg">
<meta name="twitter:site" content="@my_twitter_handle">
```

These are generated compatibility tags. In particular, the package does not
claim verified Twitter/X provider conformance.

---

## 4. FluentSeoBuilder Example

The `FluentSeoBuilder` provides a convenient, chainable API for constructing your SEO data without needing to instantiate DTOs manually upfront.

```php
use Maatify\Seo\Web\Builder\FluentSeoBuilder;

$builder = (new FluentSeoBuilder())
    ->title('About Us')
    ->description('Learn more about our company.')
    ->canonical('https://example.com/about')
    ->robots('index,follow')
    ->openGraphTitle('About Us - Example Co.')
    ->openGraphDescription('Discover the history of our company.')
    ->openGraphUrl('https://example.com/about')
    ->openGraphType('website')
    ->openGraphImage('https://example.com/og-about.jpg')
    ->twitterCard('summary_large_image')
    ->twitterTitle('About Us')
    ->twitterDescription('Discover the history of our company.')
    ->schema([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Example Co.',
        'url' => 'https://example.com',
    ]);

// Render a complete HTML string
$fullHtml = $builder->render();

// Or get a SeoHeadHtmlDTO
$dto = $builder->renderDto();
```

Use `FluentSeoBuilder` when one call site owns a compact per-page chain. Use a
`MetaTagsDTO` and individual renderers when the Host already has DTO data or
needs to place head sections separately. The executed
[`observable-output-showcase.php`](../../examples/observable-output-showcase.php)
renders this builder result:

```html
<title>Builder Output Showcase</title>
<meta name="description" content="Built through FluentSeoBuilder.">
<link rel="canonical" href="https://example.test/builder">
<meta name="robots" content="index,follow">
<meta property="og:title" content="Builder OG title">
<meta property="og:description" content="Builder OG description">
<meta property="og:type" content="website">
<meta property="og:url" content="https://example.test/builder">
<meta property="og:image" content="https://cdn.example.test/builder-og.png">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Builder Twitter title">
<meta name="twitter:description" content="Builder Twitter description">
<meta name="twitter:image" content="https://cdn.example.test/builder-twitter.png">
<script type="application/ld+json">{"@context":"https://schema.org","@type":"Organization","name":"Maatify SEO"}</script>
<script type="application/ld+json">{"@context":"https://schema.org","@type":"BreadcrumbList","name":"Builder breadcrumbs"}</script>
```

### Homepage SEO Example

The following end-to-end example uses the current public builders to prepare a
homepage's metadata, social tags, and JSON-LD. Run it from the project root after
installing dependencies; the host application remains responsible for placing the
rendered string in the document `<head>` and returning the HTTP response.

```php
<?php

declare(strict_types=1);

require getcwd() . '/vendor/autoload.php';

use Maatify\Seo\Web\Builder\FluentSeoBuilder;
use Maatify\Seo\Web\JsonLd\Builder\OrganizationJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\WebSiteJsonLdBuilder;

$organizationSchema = (new OrganizationJsonLdBuilder())
    ->setName('Maatify SEO')
    ->setUrl('https://example.com')
    ->toArray();

$websiteSchema = (new WebSiteJsonLdBuilder())
    ->setName('Example Homepage')
    ->setUrl('https://example.com')
    ->setSearchAction('https://example.com/search?q={search_term_string}')
    ->toArray();

$homepageSeo = (new FluentSeoBuilder())
    ->title('Example Homepage')
    ->description('Discover the latest updates from Example.')
    ->canonical('https://example.com')
    ->robots('index,follow')
    ->openGraphTitle('Example Homepage')
    ->openGraphDescription('Discover the latest updates from Example.')
    ->openGraphType('website')
    ->openGraphUrl('https://example.com')
    ->openGraphImage('https://example.com/images/homepage.jpg')
    ->twitterCard('summary_large_image')
    ->twitterTitle('Example Homepage')
    ->twitterDescription('Discover the latest updates from Example.')
    ->twitterImage('https://example.com/images/homepage.jpg')
    ->schemas([$websiteSchema, $organizationSchema]);

$headHtml = $homepageSeo->render();

// The host template places $headHtml inside <head>.
// The host controller/framework sends the final HTTP response.
echo $headHtml;
```

---

## 5. JSON-LD Examples

The library can generate structured data script tags using either raw associative arrays or the strictly typed `JsonLdSchemaDTO`.

### Using JSON-LD Builders (Recommended):

The library provides fluent builders for common schema types (e.g., `Article`, `Product`, `Organization`, `WebSite`).

```php
use Maatify\Seo\Web\JsonLd\Builder\WebSiteJsonLdBuilder;
use Maatify\Seo\Web\Render\JsonLdScriptRenderer;

$builder = new WebSiteJsonLdBuilder();
$schemaArray = $builder
    ->setName('Maatify Demo')
    ->setUrl('https://example.com')
    ->setSearchAction('https://example.com/search?q={search_term_string}')
    ->toArray();

$renderer = new JsonLdScriptRenderer();
echo $renderer->render($schemaArray);
```

### Using a raw associative array:

```php
use Maatify\Seo\Web\Render\JsonLdScriptRenderer;

$renderer = new JsonLdScriptRenderer();

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => 'Understanding JSON-LD',
];

echo $renderer->render($schema);
```

### Using `JsonLdSchemaDTO`:

```php
use Maatify\Seo\Shared\DTO\Schema\JsonLdSchemaDTO;
use Maatify\Seo\Web\Render\JsonLdScriptRenderer;

$renderer = new JsonLdScriptRenderer();

$schemaDto = new JsonLdSchemaDTO([
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => 'Homepage',
]);

echo $renderer->render($schemaDto);
```

The renderer returns a `<script type="application/ld+json">` string. For
example, the public builder example emits a Product node with a nested Offer
and AggregateRating:

```html
<script type="application/ld+json">{"@context":"https://schema.org","@type":"Product","name":"Maatify Demo Product","description":"A demo product showcasing the JSON-LD Builder.","sku":"DEMO-PROD-01","brand":{"@type":"Brand","name":"Maatify"},"image":"https://example.com/images/product.jpg","category":"Software","url":"https://example.com/products/demo-product","offers":{"@type":"Offer","priceCurrency":"USD","price":"29.99","availability":"https://schema.org/InStock","itemCondition":"https://schema.org/NewCondition"},"aggregateRating":{"@type":"AggregateRating","ratingValue":4.9,"reviewCount":150}}</script>
```

Builders produce schema-shaped data; `toArray()` exposes that data,
`JsonLdSchemaDTO` is the package's serializable wrapper, and
`JsonLdScriptRenderer` encodes and HTML-safe renders it. Generation and the
package's scoped structural/property-range checks do not establish Google Rich
Results eligibility or Merchant Center status.

---

## 6. Advanced Product Structured Data

Product structured data supports both scalar offer setters for simple cases and explicit offer composition for more complex e-commerce schemas.

### Scalar Offers and Explicit Offers API

Setting price and currency directly on `ProductJsonLdBuilder` (for example, `setPrice('10.00')`) maintains an implicit `Offer` array and remains supported.

However, if you need typed `Offer` objects, multiple offers, or an `AggregateOffer`, use the **Explicit Offers API**: `setOffers()` and `addOffer()`.

*Warning: Passing a non-empty value to `setOffers()` or using `addOffer()` places the builder into explicit state. Once in explicit state, calling legacy implicit offer methods (like `setPrice()`) will immediately throw a `JsonLdBuildException`. (Note: `setOffers()` with no arguments or an empty array `[]` is a no-op and does not trigger explicit state).*

### Product with Explicit Offer

Using `setOffers()` allows you to inject fully typed builders:

```php
use Maatify\Seo\Web\JsonLd\Builder\ProductJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\OfferJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\OrganizationJsonLdBuilder;

$seller = (new OrganizationJsonLdBuilder())->setName('My Store');
$offer = (new OfferJsonLdBuilder())
    ->setPrice('19.99')
    ->setPriceCurrency('USD')
    ->setSeller($seller);

$product = (new ProductJsonLdBuilder())
    ->setName('Widget')
    ->setOffers($offer);
```

### Product with AggregateOffer

To indicate a price range, use the `AggregateOfferJsonLdBuilder`:

```php
use Maatify\Seo\Web\JsonLd\Builder\ProductJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\AggregateOfferJsonLdBuilder;

$aggregateOffer = (new AggregateOfferJsonLdBuilder())
    ->setLowPrice('10.00')
    ->setHighPrice('50.00')
    ->setPriceCurrency('USD')
    ->setOfferCount(5);

$product = (new ProductJsonLdBuilder())
    ->setName('Widget Collection')
    ->setOffers($aggregateOffer);
```

### Multiple Offers

You can use `addOffer()` to build a list, or pass variadic arguments/arrays to `setOffers()`:

```php
$product->setOffers($offer1, $offer2);
// or
$product->addOffer($offer1)->addOffer($offer2);
// Raw arrays are also accepted. Their keys (including explicit @context) are preserved,
// but resolution remains recursive for any nested JsonLdBuilderInterface instances inside them:
$product->addOffer(['@type' => 'Offer', 'price' => '5.00', 'priceCurrency' => 'USD']);
```

### ProductGroup and Product Variants

To represent a parent product containing multiple variants, use `ProductGroupJsonLdBuilder` and link `ProductJsonLdBuilder` variants.

```php
use Maatify\Seo\Web\JsonLd\Builder\ProductGroupJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\ProductJsonLdBuilder;

$redVariant = (new ProductJsonLdBuilder())
    ->setSku('TS-RED-L')
    ->setColor('Red')
    ->setSize('L');

$blueVariant = (new ProductJsonLdBuilder())
    ->setSku('TS-BLU-M')
    ->setColor('Blue')
    ->setSize('M');

$productGroup = (new ProductGroupJsonLdBuilder())
    ->setName('T-Shirt Line')
    ->setProductGroupID('TSHIRT-BASE')
    ->setVariesBy(['https://schema.org/color', 'https://schema.org/size'])
    ->setHasVariant($redVariant, $blueVariant);
```

### Linking Child to Parent (Variant Relationship)

If you are rendering the child `Product` schema page, you can declare its relationship back to the parent `ProductGroup` using the `setIsVariantOf()` and `setInProductGroupWithID()` APIs:

```php
use Maatify\Seo\Web\JsonLd\Builder\ProductJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\ProductGroupJsonLdBuilder;

// Using a typed ProductGroup Builder as the parent
$parentGroup = (new ProductGroupJsonLdBuilder())
    ->setProductGroupID('TSHIRT-BASE')
    ->setName('T-Shirt Line');

// Example writing the `isVariantOf` property
$childVariant1 = (new ProductJsonLdBuilder())
    ->setName('Red T-Shirt')
    ->setSku('TS-RED-L')
    ->setIsVariantOf($parentGroup); // Embeds the typed parent node (or if given a string, it becomes a ProductGroup node with productGroupID)

// Example writing the `inProductGroupWithID` property
$childVariant2 = (new ProductJsonLdBuilder())
    ->setName('Blue T-Shirt')
    ->setSku('TS-BLU-L')
    ->setInProductGroupWithID('TSHIRT-BASE'); // Writes the string ID directly
```

*Note: The builders ensure that nested `@context` tags are automatically stripped from typed builders during output, while the root builder retains its context. Raw array contexts are not touched. Builders and `SchemaGeneratorService` provide generic Schema.org generation; they are independent of Google Rich Results and Merchant eligibility. The current validation boundary is scoped structural and property-range semantic validation for selected types, not complete provider eligibility proof.*

The executed advanced example renders the composed values, including these
nested shapes:

```json
{"@context":"https://schema.org","@type":"Product","name":"Premium Widget","description":"A very nice widget.","gtin":"0123456789012","mpn":"PW-01","offers":{"@type":"Offer","price":"29.99","priceCurrency":"USD","availability":"https://schema.org/InStock","seller":{"@type":"Organization","name":"My Awesome Store"}}}
```

```json
{"@context":"https://schema.org","@type":"Product","name":"Widget Collection","offers":{"@type":"AggregateOffer","lowPrice":"10.00","highPrice":"50.00","priceCurrency":"USD","offerCount":15,"offers":[{"@type":"Offer","price":"29.99","priceCurrency":"USD"},{"@type":"Offer","price":"34.99","priceCurrency":"CAD"}]}}
```

```json
{"@context":"https://schema.org","@type":"ProductGroup","name":"Classic T-Shirt Line","productGroupID":"TSHIRT-BASE","brand":{"@type":"Brand","name":"Maatify Apparel"},"variesBy":["https://schema.org/color","https://schema.org/size"],"hasVariant":[{"@type":"Product","sku":"TS-RED-L","color":"Red","size":"L"},{"@type":"Product","sku":"TS-BLU-M","color":"Blue","size":"M"}]}
```

Both are JSON bodies inside the renderer's JSON-LD script element. See
[`advanced-product-structured-data.php`](../../examples/advanced-product-structured-data.php)
for the complete Product + Offer, multiple-offer, AggregateOffer, and variant
rendered samples.

---

## 7. Optional Spatie Schema Adapter Example

If your project utilizes the popular `spatie/schema-org` package, the SEO library provides an optional adapter (`SpatieSchemaAdapter`) to convert Spatie schema objects into native `JsonLdSchemaDTO` objects.

**Note:** The `spatie/schema-org` dependency is strictly optional and not required by the Maatify SEO library. It is provided via `composer suggest`.

```php
use Maatify\Seo\Web\Builder\FluentSeoBuilder;
use Maatify\Seo\Web\Schema\SpatieSchemaAdapter;
use Spatie\SchemaOrg\Schema; // Only if you have installed spatie/schema-org in your host app

// Assuming you have a Spatie schema object
// (We use a fake local object structure here for demonstration)
$localSchemaObject = new class {
    public function toArray(): array {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => 'Adapted Product'
        ];
    }
};

$adapter = new SpatieSchemaAdapter();

// Use the adapter with the fluent builder:
$builder = (new FluentSeoBuilder())
    ->title('Product View')
    ->spatieSchema($localSchemaObject, $adapter);

echo $builder->render();
```

The adapter converts a supported Spatie object to the package's native
`JsonLdSchemaDTO`; the normal package renderer then returns the same kind of
JSON-LD script string as for package builders. The runnable
[`schema-output.php`](../../examples/schema-output.php) uses a local fake object
with `toArray()` so it remains runnable without installing Spatie; an
application that passes an actual Spatie object must install the optional
dependency itself.

For the local `toArray()`-shaped object used by
[`schema-output.php`](../../examples/schema-output.php), the adapter yields a
native `JsonLdSchemaDTO` containing `@type: Person` and `name: John Doe`; the
renderer returns:

```html
<script type="application/ld+json">{"@context":"https://schema.org","@type":"Person","name":"John Doe"}</script>
```

---

## 8. Sitemap XML String Example

To easily render sitemap entries to XML strings without modifying core services, the library provides the `SitemapXmlStringRenderer`. It supports rendering basic URLs, alternate hreflang tags for multi-language indexing, Google image sitemap definitions, Google video sitemap definitions, and Google news sitemap definitions.

```php
use Maatify\Seo\Shared\DTO\Sitemap\SitemapAlternateUrlDTO;
use Maatify\Seo\Shared\DTO\Sitemap\SitemapImageDTO;
use Maatify\Seo\Shared\DTO\Sitemap\SitemapVideoDTO;
use Maatify\Seo\Shared\DTO\Sitemap\SitemapNewsDTO;
use Maatify\Seo\Shared\DTO\Sitemap\SitemapUrlDTO;
use Maatify\Seo\Web\Sitemap\SitemapXmlStringRenderer;

$renderer = new SitemapXmlStringRenderer();

// Example with SitemapUrlDTO, Alternate URLs (Hreflang), Images, and Videos
$urlDto = new SitemapUrlDTO(
    loc: 'https://example.com/en/page-1',
    lastmod: '2023-10-01',
    changefreq: 'monthly',
    priority: 0.5,
    alternates: [
        new SitemapAlternateUrlDTO('en', 'https://example.com/en/page-1'),
        new SitemapAlternateUrlDTO('es', 'https://example.com/es/page-1'),
    ],
    images: [
        new SitemapImageDTO(
            loc: 'https://example.com/image.jpg',
        )
    ],
    videos: [
        new SitemapVideoDTO(
            thumbnailLoc: 'https://example.com/thumbnail.jpg',
            title: 'Sample Video',
            description: 'A sample video description',
            contentLoc: 'https://example.com/video.mp4',
            playerLoc: 'https://example.com/player',
            duration: 600,
            publicationDate: '2023-10-01T12:00:00+00:00'
        )
    ],
    news: [
        new SitemapNewsDTO(
            publicationName: 'Example Daily',
            publicationLanguage: 'en',
            publicationDate: '2023-10-01',
            title: 'Breaking News'
        )
    ]
);
echo $renderer->renderUrlEntry($urlDto);
// Output includes local xmlns:xhtml, xmlns:image, xmlns:video, and xmlns:news:
// <url xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
//   <loc>https://example.com/en/page-1</loc>
//   <lastmod>2023-10-01</lastmod>
//   <changefreq>monthly</changefreq>
//   <priority>0.5</priority>
//   <xhtml:link rel="alternate" hreflang="en" href="https://example.com/en/page-1"/>
//   <xhtml:link rel="alternate" hreflang="es" href="https://example.com/es/page-1"/>
//   <image:image>
//     <image:loc>https://example.com/image.jpg</image:loc>
//   </image:image>
//   <video:video>
//     <video:thumbnail_loc>https://example.com/thumbnail.jpg</video:thumbnail_loc>
//     <video:title>Sample Video</video:title>
//     <video:description>A sample video description</video:description>
//     <video:content_loc>https://example.com/video.mp4</video:content_loc>
//     <video:player_loc>https://example.com/player</video:player_loc>
//     <video:duration>600</video:duration>
//     <video:publication_date>2023-10-01T12:00:00+00:00</video:publication_date>
//   </video:video>
//   <news:news>
//     <news:publication>
//       <news:name>Example Daily</news:name>
//       <news:language>en</news:language>
//     </news:publication>
//     <news:publication_date>2023-10-01</news:publication_date>
//     <news:title>Breaking News</news:title>
//   </news:news>
// </url>

// Example with associative array
$arrayEntry = [
    'loc' => 'https://example.com/page-2',
    'lastmod' => '2023-10-02',
    'changefreq' => 'weekly',
    'priority' => '0.8',
    'alternates' => [
        ['hreflang' => 'x-default', 'url' => 'https://example.com/page-2'],
        ['hreflang' => 'de', 'url' => 'https://example.com/de/page-2'],
    ],
    'images' => [
        ['loc' => 'https://example.com/image2.jpg']
    ],
    'videos' => [
        [
            'thumbnailLoc' => 'https://example.com/thumbnail2.jpg',
            'title' => 'Video 2',
            'description' => 'Description 2',
            'contentLoc' => 'https://example.com/video2.mp4'
        ]
    ],
    'news' => [
        [
            'publicationName' => 'Tech Weekly',
            'publicationLanguage' => 'en',
            'publicationDate' => '2023-10-02',
            'title' => 'New SEO Tools'
        ]
    ]
];
echo $renderer->renderUrlEntry($arrayEntry);

// Rendering an entire URL Set (passing multiple URLs)
$xmlOutput = $renderer->renderUrlSet([$urlDto, $arrayEntry]);
// For the minimal URL-set example, the executed renderer returns:
// <?xml version="1.0" encoding="UTF-8"?>
// <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>https://example.com/page-1</loc><lastmod>2023-11-01</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url><url><loc>https://example.com/page-2</loc><lastmod>2023-11-02</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url></urlset>
```

> **Note:** The `xmlns:xhtml="http://www.w3.org/1999/xhtml"` namespace is dynamically added to the root `<urlset>` (or `<url>` if rendering a single entry) only when `alternates` are present. Similarly, `xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"` is added only when `images` exist, `xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"` is added only when `videos` exist, and `xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"` is added only when `news` exists. They are included together when alternate URLs, images, videos, and news exist together. If none are supplied, the sitemap output remains clean and unchanged. Existing URL-set, hreflang, and image sitemap output without videos or news remains exactly as it was.
>
> **News Fields Handling:** When using `SitemapNewsDTO`, the `publicationDate` is accepted as-is and rendered exactly as provided. Required fields are trimmed, and providing empty required values throws a `SeoInvalidArgumentException`. Optional empty strings are normalized to `null` and are entirely omitted from the XML output. All XML values are safely escaped by `XMLWriter`.

> **Google Image compatibility:** `SitemapImageDTO` still accepts and renders `title`, `caption`, `geoLocation`, and `license` for public/output compatibility. Google-deprecates these fields; they are not presented here as current indexing/search enhancements and have no Stack 4 runtime diagnostic. Current examples therefore use `loc` only.

> **URL validation:** `SitemapUrlDTO::isValidLastmod()` and the Web URL/Index rendering contracts accept `YYYY-MM-DD`, full-seconds date-times, and fractional-seconds date-times with a required `Z` or numeric offset, while rejecting invalid calendar/time values and zone-less or partial date-times. Strict `SitemapVideoDTO` and raw-video `publicationDate` remain limited to `YYYY-MM-DD` and full-seconds date-times; fractional seconds are rejected there. News `publicationDate` intentionally remains an emitted-as-provided, non-empty string. Stack 4 candidate validators separately apply the fixed provider lexical forms and caller-supplied evidence to Sitemap/Google extension inputs; they do not alter rendering output or infer remote facts.

The executed extended-entry example shows the generated structure for alternate
languages, image, video, and news children (one complete `<url>` element):

```xml
<?xml version="1.0" encoding="UTF-8"?>
<url xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"><loc>https://example.com/en/article</loc><lastmod>2026-07-01T10:00:00+00:00</lastmod><changefreq>weekly</changefreq><priority>0.7</priority><xhtml:link rel="alternate" hreflang="en" href="https://example.com/en/article"/><xhtml:link rel="alternate" hreflang="x-default" href="https://example.com/article"/><image:image><image:loc>https://cdn.example.com/article.jpg</image:loc></image:image><video:video><video:thumbnail_loc>https://cdn.example.com/article-video.jpg</video:thumbnail_loc><video:title>Article video</video:title><video:description>A representative article video</video:description><video:content_loc>https://cdn.example.com/article-video.mp4</video:content_loc><video:duration>120</video:duration><video:publication_date>2026-07-01</video:publication_date></video:video><news:news><news:publication><news:name>Example Daily</news:name><news:language>en</news:language></news:publication><news:publication_date>2026-07-01</news:publication_date><news:title>Example article</news:title></news:news></url>
```

This is XML generation from supplied DTOs. The Host serves it from a route or
file and handles HTTP headers, sitemap discovery, and provider submission.

---

## 9. Sitemap Index XML String Example

To render a sitemap index directly to an XML string, use the `SitemapIndexXmlStringRenderer`.

```php
use Maatify\Seo\Web\Sitemap\DTO\SitemapIndexEntryDTO;
use Maatify\Seo\Web\Sitemap\SitemapIndexXmlStringRenderer;

$renderer = new SitemapIndexXmlStringRenderer();

// Example with SitemapIndexEntryDTO
$dto = new SitemapIndexEntryDTO('https://example.com/sitemap-products.xml', '2023-10-01');
echo $renderer->renderEntry($dto);

// Example with associative array
$arrayEntry = [
    'loc' => 'https://example.com/sitemap-articles.xml',
    'lastmod' => '2023-10-02',
];
echo $renderer->renderEntry($arrayEntry);

// Render the full index
echo $renderer->renderIndex([$dto, $arrayEntry]);
```

The executed example returns this complete sitemap index XML:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>https://example.com/sitemap-pages.xml</loc><lastmod>2026-07-01</lastmod></sitemap><sitemap><loc>https://example.com/sitemap-news.xml</loc><lastmod>2026-07-02</lastmod></sitemap></sitemapindex>
```

The renderer serializes the entries it receives. It does not fetch the files,
publish the index, or confirm that any search engine has processed it.

---

## 10. Robots.txt String Output Example

To quickly render a `robots.txt` string dynamically, you can use the `RobotsTxtRenderer`.

```php
use Maatify\Seo\Web\Robots\RobotsTxtRenderer;
use Maatify\Seo\Web\Robots\DTO\RobotsTxtDTO;
use Maatify\Seo\Web\Robots\DTO\RobotsRuleDTO;

$renderer = new RobotsTxtRenderer();

$txt = new RobotsTxtDTO(
    rules: [
        new RobotsRuleDTO(
            userAgent: '*',
            allow: ['/'],
            disallow: ['/admin/', '/private/'],
            crawlDelay: 10, // Non-standard crawler extension; not RFC core or Google-supported.
            comments: ['Global rule for all bots']
        ),
        new RobotsRuleDTO(
            userAgent: 'BadBot',
            disallow: ['/']
        )
    ],
    sitemaps: [
        'https://example.com/sitemap.xml',
        'https://example.com/sitemap-images.xml'
    ],
    comments: [
        'Welcome to my robots.txt',
        'Created dynamically'
    ]
);

// Returns a correctly formatted robots.txt plain string.
// You must output this string and set the Content-Type: text/plain
// header in your host application controller.
echo $renderer->render($txt);
```

For the fixture in [`robots-output.php`](../../examples/robots-output.php),
the complete text is:

```text
# Generated by the Maatify SEO robots.txt example

# Crawler access rules for the public site
User-agent: *
Crawl-delay: 2
Allow: /public/
Disallow: /admin/
Disallow: /private/

Sitemap: https://example.com/sitemap.xml
```

`Crawl-delay` is emitted as supplied for compatibility. It is not part of the
RFC 9309 core rules or Google's supported robots directives. The separate RFC
and Google candidate validators report their own profile diagnostics; rendering
does not claim that either profile accepts every emitted line.

### Robots validation profiles

Robots validation uses raw candidate input, so malformed or provider-specific values can be diagnosed without weakening the strict generation DTOs:

```php
use Maatify\Seo\Web\Validation\Input\RobotsTxtValidationInputDTO;
use Maatify\Seo\Web\Validation\Profile\GoogleRobotsTxtValidator;
use Maatify\Seo\Web\Validation\Profile\Rfc9309RobotsValidator;

$candidate = new RobotsTxtValidationInputDTO($robotsTxtContent);
$rfc = (new Rfc9309RobotsValidator())->validate($candidate);
$google = (new GoogleRobotsTxtValidator())->validate($candidate);
```

Both validators return `SeoCompanionValidationResultDTO`. RFC 9309 protocol outcomes and Google provider outcomes remain separate, and companion diagnostics do not enter the legacy validation result or score. The existing strict `RobotsTxtDTO` `FILTER_VALIDATE_URL` behavior for Sitemap URLs is preserved, as are its generation/render compatibility behaviors; Stack 2 intentionally adds hard structured-input rejection for control-character injection. The Google profile accepts valid raw Unicode absolute `Sitemap:` URLs and rejects relative, malformed, fragmented, or `data:` values. `crawl-delay` remains a non-standard compatibility extension rather than RFC or Google behavior.

### Meta robots directives

Use `MetaRobotsBuilder` to compose the comma-separated robots content value or
an escaped `<meta>` element. It keeps `index`/`noindex` and
`follow`/`nofollow` mutually exclusive and deduplicates exact directives.
`unavailableAfter()` prefixes the supplied string with `unavailable_after:`;
the builder does not validate or normalize a date format. A separate Google
robots-meta diagnostic can report whether the caller supplied evidence that the
directive is recognized.

```php
$robots = (new MetaRobotsBuilder())
    ->noIndex()
    ->noFollow()
    ->maxSnippet(50)
    ->unavailableAfter('31-Dec-2026 23:59:59 GMT');

$content = $robots->build();
$html = $robots->toHtml();
```

The executed example's restricted directive has these exact values:

```text
content: noindex, nofollow, noarchive, max-snippet:50
html:    <meta name="robots" content="noindex, nofollow, noarchive, max-snippet:50">
```

The raw compatibility call above builds `noindex, nofollow, max-snippet:50,
unavailable_after:31-Dec-2026 23:59:59 GMT`; it is the caller's responsibility
to choose a value understood by the target crawler. See
[`meta-robots-canonical.php`](../../examples/meta-robots-canonical.php).

---

## 11. SEO Metadata Validation Example

The `SeoMetaValidator` allows you to audit generated SEO metadata (arrays or objects) to verify that essential tags and formats are correctly configured. It does not output HTML or throw exceptions for bad SEO data; instead, it returns an aggregated `SeoValidationResultDTO`. This is extremely useful for pre-flight checks, automated tests, or admin dashboard warnings.

The validator natively covers:
* **Title:** Presence, minimum length, maximum length.
* **Description:** Presence, minimum length, maximum length.
* **Canonical:** Presence (optional), and absolute URL format.
* **Robots Conflicts:** Checks if both `index` and `noindex` (or `follow` and `nofollow`) are concurrently set.
* **OpenGraph Missing Fields:** Validates `og:title`, `og:description`, and `og:image` are provided when an OpenGraph context is requested.
* **Twitter Missing Fields:** Validates `card`, `title`, and `description` are provided when a Twitter context is requested.
* **JSON-LD structural validation:** Checks JSON-LD nodes and numeric node lists,
  including `@graph` wrappers and recursive graph nodes, while preserving
  deterministic issue fields.
* **JSON-LD scoped validation:** Performs scoped structural and property-range semantic
  validation only for `Product`, `Offer`, `AggregateOffer`, and `ProductGroup`.
  JSON-LD can be supplied through the existing `jsonLd`, `json_ld`, `schema`, or
  `schemas` aliases.

The validator does not provide complete Schema.org semantic or lexical proof. In the
current property-range boundaries, non-empty strings shaped as `URL`, `Date`,
`DateTime`, `ItemAvailability`, or `OfferItemCondition` remain accepted
representations; URL/date grammar, enumeration membership, provider-vocabulary
lookup, reachability, and DNS/network checks are not performed. Google
required/recommended properties are not implemented as an eligibility profile, and
Merchant eligibility remains a separate provider boundary.

> **Note:** The validator expects data in an array or object format, typically generated before final HTML string rendering. Invalid `$options` configuration (such as passing a string where an integer is expected) will throw a `SeoInvalidArgumentException`. Normal SEO warnings and errors *do not* throw exceptions.

### Basic Validation

```php
use Maatify\Seo\Web\Validation\SeoMetaValidator;

$metaData = [
    'title' => 'My Page',
    'description' => 'A short description.',
    'canonical' => 'not-a-valid-url',
    'robots' => 'index, noindex', // Conflict!
    'openGraph' => [
        'title' => 'OG Title',
        // Missing og:description and og:image
    ]
];

$result = SeoMetaValidator::validate($metaData);

// Check overall status
if (!$result->isValid) {
    echo "There are SEO errors.\n";
}
if ($result->hasWarnings) {
    echo "There are SEO warnings.\n";
}

// Inspect specific issues
foreach ($result->errors as $error) {
    // e.g., invalid_canonical: "Canonical URL must be a valid absolute URL."
    echo "[{$error->severity}] {$error->code}: {$error->message} (Field: {$error->field})\n";
}

foreach ($result->warnings as $warning) {
    // e.g., robots_index_conflict, missing_og_description, missing_og_image, title_too_short
    echo "[{$warning->severity}] {$warning->code}: {$warning->message}\n";
}

// Access all issues together
$allIssues = $result->issues;
```

### Calculating a Validation Score

Once you have a `SeoValidationResultDTO` from `SeoMetaValidator::validate()`, you can calculate a score from 0 to 100 using the `SeoValidationScoreCalculator`. The score helper does not mutate the original validation result, and does not change the `SeoMetaValidator` behavior.

The default scoring works as follows:
- Starts at 100.
- `error` issues deduct 25 points each.
- `warning` issues deduct 5 points each.
- `info` issues deduct 0 points each.
- The score is clamped to always remain between 0 and 100.

The calculator assigns a letter grade based on the score:
- **A**: 90–100
- **B**: 80–89
- **C**: 70–79
- **D**: 60–69
- **F**: below 60

It also computes an `isHealthy` boolean (defaulting to true if the score is $\ge$ 80), and maps the deductions directly into an array of arrays (`code`, `severity`, `field`, `points`).

```php
use Maatify\Seo\Web\Validation\SeoMetaValidator;
use Maatify\Seo\Web\Validation\SeoValidationScoreCalculator;

$metaData = [
    'title' => 'Missing Description',
];

// 1. Get the validation result
$result = SeoMetaValidator::validate($metaData);

// 2. Compute the score
$scoreDto = SeoValidationScoreCalculator::score($result);

// 3. Read the score attributes
echo "Score: {$scoreDto->score}\n";           // e.g. 95 (100 - 5 points for missing description warning)
echo "Grade: {$scoreDto->grade}\n";           // e.g. A
echo "Healthy: {$scoreDto->isHealthy}\n";     // true

echo "Errors: {$scoreDto->errorCount}\n";
echo "Warnings: {$scoreDto->warningCount}\n";
echo "Info: {$scoreDto->infoCount}\n";

// Inspect point deductions
foreach ($scoreDto->deductions as $deduction) {
    echo "- Lost {$deduction['points']} points for {$deduction['code']} ({$deduction['severity']}) on field: {$deduction['field']}\n";
}
```

### Validation and Score Presets

Instead of manually building options arrays, you can use the `SeoValidationPreset` helper which provides ready-made options for both validation and scoring. Invalid preset names throw `SeoInvalidArgumentException`. The presets do not mutate external state or use static mutable cache.

* `minimal()`: Does not require canonical. Uses default scoring (errorPenalty 25, warningPenalty 5, infoPenalty 0, healthyMinimumScore 80).
* `standard()`: Requires canonical. Title limits 10-60. Description limits 50-160. Uses default scoring.
* `strict()`: Requires canonical. Title limits 20-60. Description limits 80-155. errorPenalty 30, warningPenalty 10, healthyMinimumScore 90.

```php
use Maatify\Seo\Web\Validation\SeoMetaValidator;
use Maatify\Seo\Web\Validation\SeoValidationScoreCalculator;
use Maatify\Seo\Web\Validation\SeoValidationPreset;

$metaData = [
    'title' => 'Title Here',
    'description' => 'Description Here',
];

// Returns an independent plain array with 'validationOptions' and 'scoreOptions'
$preset = SeoValidationPreset::standard();
// Or dynamically: $preset = SeoValidationPreset::for('standard');

$result = SeoMetaValidator::validate(
    meta: $metaData,
    options: $preset['validationOptions']
);

$score = SeoValidationScoreCalculator::score(
    result: $result,
    options: $preset['scoreOptions']
);
```

### Validation and Score Options

You can also customize the validator rules by passing an `$options` array as the second argument. Invalid configurations (e.g. non-integers for lengths) will throw a `SeoInvalidArgumentException`.

```php
use Maatify\Seo\Web\Validation\SeoMetaValidator;
use Maatify\Seo\Web\Validation\SeoValidationScoreCalculator;

$metaData = [
    'title' => 'Perfect Title Length Here',
    'description' => 'This description is long enough to pass the custom validation threshold we set.',
];

$options = [
    'requireCanonical' => true,      // Defaults to false
    'titleMinLength' => 15,          // Defaults to 10
    'titleMaxLength' => 60,          // Defaults to 60
    'descriptionMinLength' => 50,    // Defaults to 50
    'descriptionMaxLength' => 160,   // Defaults to 160
];

$result = SeoMetaValidator::validate($metaData, $options);

// Because 'canonical' is missing and requireCanonical is true,
// a 'missing_canonical' warning will be generated.

// Calculate score with customized penalties:
$scoreOptions = [
    'errorPenalty' => 50,         // Custom: Errors cost 50 points
    'warningPenalty' => 10,       // Custom: Warnings cost 10 points
    'infoPenalty' => 0,           // Custom: Info costs 0 points
    'healthyMinimumScore' => 90,  // Custom: Must score at least 90 to be isHealthy
];

$customScoreDto = SeoValidationScoreCalculator::score($result, $scoreOptions);
```

> **Note:** Providing invalid options (like a string instead of an integer penalty, or a penalty below 0) to `SeoValidationScoreCalculator::score()` will throw a `SeoInvalidArgumentException`. The score helper is strictly framework-neutral and emits no headers, responses, routes, or controllers.

The maintained examples show that validation, warnings, and scoring are separate
values. [`seo-validation.php`](../../examples/seo-validation.php) returns a
valid result with one heuristic warning:

```text
status=warning  valid=true  score=95  grade=A  warnings=1  errors=0
description_too_short (warning, field: description), deduction: -5
```

[`product-seo-audit.php`](../../examples/product-seo-audit.php) intentionally
supplies malformed nested Product offer data; the package report returns:

```text
status=fail  valid=false  score=75  grade=C  warnings=0  errors=1
json_ld_missing_type (error, field: jsonLd.0.offers.price.@type), deduction: -25
```

`isValid` answers whether errors exist; warnings can coexist with a valid
result. The score is a separate configurable calculation. Companion/provider
diagnostics are separate again and do not mutate either value.

### Companion protocol and provider-profile diagnostics

`SeoMetaValidator` handles the package's generic metadata and scoped structured-data checks. Protocol and provider-profile checks are separate calls that return `SeoCompanionValidationResultDTO`; they do not silently change the generic result or score. `SeoMetaValidator::validateWithCompanion()` does not run every profile validator, so select the profiles that match the input and evidence you have.

| Concern | Current profile entry points |
| --- | --- |
| Robots | `Rfc9309RobotsValidator`, `GoogleRobotsTxtValidator`, `GoogleRobotsMetaValidator` |
| Sitemap | `SitemapProtocolValidator`, `GoogleSitemapValidator`, `GoogleImageSitemapValidator`, `GoogleVideoSitemapValidator`, `GoogleNewsSitemapValidator` |
| Canonical and hreflang | `GoogleCanonicalValidator`, `GoogleHreflangClusterValidator` |
| Open Graph | `OpenGraphProtocolValidator` |

Use the matching raw-input or cluster DTO for each profile. Results identify their diagnostic origin/profile and remain distinct from core validation and scoring. These checks are scoped protocol/profile diagnostics; they do not imply remote inspection, complete standards-registry membership, or provider eligibility.

[`protocol-provider-diagnostics.php`](../../examples/protocol-provider-diagnostics.php)
runs representative checks and prints each diagnostic's `code`, `severity`,
`origin`, `profile`, `field`, and `evidence_state`. The executed fixture reports:

| Input/profile | Returned diagnostic |
| --- | --- |
| RFC 9309 robots, `Disallow: *` | `robots_rfc9309_leading_wildcard_compatibility` / warning / protocol / `rfc9309` |
| Google robots, same raw text | `robots_google_present_path_leading_slash` / warning / provider / `google` |
| `unavailable_after` evidence `recognized` | `robots_meta_unavailable_after_recognizability` / info / evidence state `recognized` |
| `unavailable_after` evidence `unrecognized` | same diagnostic / warning / evidence state `unrecognized` |
| `unavailable_after` evidence `unknown` | same diagnostic / info / evidence state `unknown` |
| Google sitemap host evidence `unknown` | `google_sitemap_host_context` / info / evidence state `unknown` |
| Relative canonical candidate | `canonical_relative_provider_best_practice` / warning |
| Open Graph with title only | warnings for missing `og:description`, `og:image`, `og:type`, and `og:url` |

Evidence is supplied by the caller, not discovered by these validators. For
`unavailable_after_recognizability`, allowed values are `recognized`,
`unrecognized`, and `unknown`; `unknown` means evidence is missing and is an
informational diagnostic, not an invented pass or failure. The sitemap sample
has no `SitemapProtocolValidator` diagnostics for its selected candidate; the
Google profile still reports that host verification evidence is unknown. A
profile result is limited to that input and the defined checks for that profile.

The package exposes Google image, video, and news sitemap diagnostics through
`GoogleImageSitemapValidator`, `GoogleVideoSitemapValidator`, and
`GoogleNewsSitemapValidator`. The runnable sample includes the image profile;
the complete entry-point matrix is listed above. Supply the matching candidate
DTOs and evidence when using video/news profiles.

---

## 12. Existing SitemapGeneratorService Example

The core `SitemapGeneratorService` remains available. It is responsible for orchestrating sitemap generation logic and returning structured DTOs (`SitemapGenerationResultDTO`), which represents a structural abstraction over the XML data.

The core service outputs objects intended for further processing or structured output handling, while the `SitemapXmlStringRenderer` (demonstrated above) is specifically a presentation-layer helper designed to quickly output standard XML strings for web consumption.

`generateUrlSitemap()` accepts strict `SitemapUrlDTO` entries and preserves their alternates, images, videos, and news children. For the same DTO URL entries, its `result->xml` uses the same canonical serialization and matches `SitemapXmlStringRenderer` output; the generator remains typed-DTO-only while the Web renderer also supports raw associative URL entries. `generateSitemapIndex()` likewise remains limited to shared sitemap-index DTOs.

```php
use Maatify\Seo\Shared\DTO\Sitemap\SitemapUrlDTO;
use Maatify\Seo\Shared\Service\SitemapGeneratorService;

$generator = new SitemapGeneratorService();

$urls = [
    new SitemapUrlDTO('https://example.com/generated-1'),
    new SitemapUrlDTO('https://example.com/generated-2'),
];

// Returns a SitemapGenerationResultDTO containing structured XML content.
$result = $generator->generateUrlSitemap($urls);

// You must take the result output and stream it or respond with it in your host application controller.
// The service itself does not emit an HTTP response.
$xmlContent = $result->xml;
```

For those two entries, the executed service returns a
`SitemapGenerationResultDTO` whose observable values include `entry_count = 2`,
`type = urlset`, and this XML string:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>https://example.com/generated-1</loc></url><url><loc>https://example.com/generated-2</loc></url></urlset>
```

---

## 13. Recommended Host Application Usage

The Maatify SEO library is designed to integrate cleanly into any PHP framework without introducing hard dependencies on the framework itself.

### Plain PHP
Use the provided builders or renderers directly within your PHP views.

```php
<?php
// my-page.php
require 'vendor/autoload.php';
$builder = (new \Maatify\Seo\Web\Builder\FluentSeoBuilder())->title('Plain PHP');
?>
<!DOCTYPE html>
<html>
<head>
    <?= $builder->render() ?>
</head>
<body>
    <h1>Hello World</h1>
</body>
</html>
```

### Slim Framework
Instantiate a builder within your route handler and pass the rendered string to your template.

```php
$app->get('/products/{id}', function ($request, $response, $args) {
    // 1. Fetch product data
    // 2. Build SEO output
    $seoHtml = (new \Maatify\Seo\Web\Builder\FluentSeoBuilder())
        ->title('Slim Product')
        ->render();

    // 3. Render template
    // This assumes a generic template rendering engine integration
    return $this->get('view')->render($response, 'product.twig', [
        'seoHtml' => $seoHtml
    ]);
});
```

### Laravel
Construct the SEO logic in your controllers or dedicated view composers and pass the resulting string to Blade.

```php
class ProductController extends Controller {
    public function show($id) {
        $builder = (new \Maatify\Seo\Web\Builder\FluentSeoBuilder())
            ->title('Laravel Product');

        return view('product', ['seoHtml' => $builder->render()]);
    }
}
```

In your Blade layout (`product.blade.php`):
```blade
<head>
    {!! $seoHtml !!}
</head>
```

### Template Rendering Tips
Always pass the pre-rendered HTML string (or the `SeoHeadHtmlDTO`) to your templating engine (like Twig, Blade, or Smarty) for output, avoiding calling the builder methods directly within the template files whenever possible. Keep the construction logic in the controller.

### Metadata orchestration with `MetaGeneratorService`

`MetaGeneratorService` turns Host-supplied page defaults and the active SEO override for an entity/language into a `MetaTagsDTO`. Missing active overrides leave the defaults in place. When configured with a Host implementation of `HostUrlGeneratorInterface`, it can use the Host-generated entity URL if the command has no non-blank explicit canonical. The Host owns route and URL policy; the service contract documents exact precedence, fallback, exception, and output semantics.

```php
use Maatify\Seo\Shared\Command\GenerateMetaTagsCommand;

$metaTags = $metaGeneratorService->generate(new GenerateMetaTagsCommand(
    entityType: 'product',
    entityId: 'sku-123',
    languageId: 1,
    defaultTitle: 'Blue Shirt',
    defaultDescription: 'Cotton shirt',
    slug: 'blue-shirt',
));

// $metaTags is a MetaTagsDTO ready for a package renderer or Host template.
```

See the maintained [MetaGeneratorService contract](../SEO/library/META_GENERATOR_SERVICE_CONTRACT.md) for the complete service semantics and the [SEO_PACKAGE_REFERENCE.md](../../SEO_PACKAGE_REFERENCE.md) for the package-level contract.

The Host first loads the product through its own repository/service; the
`$hostProduct` fixture in
[`seo-override-meta-generation.php`](../../examples/seo-override-meta-generation.php)
stands in for that already-loaded record. The package does not perform this
entity lookup. It receives the Host's default title, description, identity, and
slug, queries the active override, and asks the optional
`HostUrlGeneratorInterface` for a canonical only when no non-blank explicit
canonical was supplied. Relevant values from the executed serialized `MetaTagsDTO`
show that the manual override wins
for title and description and the explicit canonical wins over the configured
Host generator:

```json
{
  "title": "Manual Product Title | Example Store",
  "description": "Manual product description supplied by the SEO override workflow.",
  "canonical_url": "https://example.com/products/super-widget-pro",
  "robots": "index,follow",
  "open_graph_title": "Manual Product Title | Example Store",
  "twitter_title": "Manual Product Title | Example Store"
}
```

With no active override, the example keeps `Default Article Title` and
`Default article description used by the fallback path.` and obtains
`https://example.com/en/article/seo-library-integration` from the Host URL
generator. Only `SeoNotFoundException` from the override lookup is treated as
absence; other failures propagate. See the narrower maintained contract for
exact trim, blank, fallback, and exception rules.

### Page presets

Page preset factories are package-provided composition helpers. They combine metadata, canonical/robots values, social tags, JSON-LD schemas, and ready-to-render head HTML into a `SeoPagePresetOutputDTO`; they do not choose Host routes or own business lifecycle policy.

```php
use Maatify\Seo\Web\Page\EcommerceSeoPresetFactory;

$preset = EcommerceSeoPresetFactory::productDetail(
    title: 'Blue Shirt',
    description: 'Cotton shirt',
    product: [
        'name' => 'Blue Shirt',
        'sku' => 'SKU-123',
        'price' => '29.99',
        'currency' => 'USD',
    ],
    options: ['canonicalUrl' => 'https://example.com/products/blue-shirt'],
);

$metaTags = $preset->metaTags;
$schemas = $preset->schemas;
$headHtml = $preset->html;
```

`SeoPagePresetFactory` also provides generic, product, category, article, home, and breadcrumb composition. `ContentSeoPresetFactory` covers article, blog-post, news-article, tag, and author pages; `LocalBusinessSeoPresetFactory` covers business-home, location, service, and contact pages. Ecommerce presets include product detail, category listing, search results, brand, and offer pages. Presets accept package options for canonical, robots, social metadata, breadcrumbs, and extra schemas.

`SeoPagePresetOutputDTO` serializes these top-level fields: `meta_tags`,
`canonical_url`, `robots`, `social_tags`, `social_html`, `schemas`, and `html`.
The following selected serialized values are flattened for compact reading;
the runnable example prints the full nested DTO:

```json
{
  "meta_tags": {
    "title": "Super Cool T-Shirt - MySite",
    "description": "Buy this super cool t-shirt.",
    "canonical_url": "https://example.com/product/tshirt",
    "robots": "index, follow",
    "open_graph_type": "product",
    "open_graph_image": "https://example.com/tshirt.png",
    "twitter_card": "summary_large_image",
    "twitter_image": "https://example.com/tshirt.png"
  },
  "canonical_url": "https://example.com/product/tshirt",
  "robots": "index, follow",
  "social_tags[0]": {"name":"og:title","content":"Super Cool T-Shirt - MySite","attribute":"property"},
  "social_tags[9]": {"name":"twitter:image","content":"https://example.com/tshirt.png","attribute":"name"},
  "schemas[0]": {
    "@context":"https://schema.org",
    "@type":"Product",
    "name":"Super Cool T-Shirt",
    "description":"Buy this super cool t-shirt.",
    "url":"https://example.com/product/tshirt",
    "sku":"TSHIRT-001",
    "offers":{"@type":"Offer","priceCurrency":"USD","price":"19.99","availability":"https://schema.org/InStock"},
    "image":["https://example.com/tshirt.png"]
  }
}
```

`social_html` and `html` are newline-separated rendered strings. The runnable
[`seo-page-presets.php`](../../examples/seo-page-presets.php) prints their full
values along with the complete `social_tags` list. The preset composes from
Host-supplied page data; the Host chooses the route and delivers the result.

### Page rendering orchestration

Use `SeoPageRenderService` when a Host wants a single service call to combine `MetaGeneratorService` output with supplied JSON-LD schemas and optional breadcrumb data. A `RenderSeoPageCommand` carries Host entity identifiers and defaults plus those inputs. `render()` returns a `SeoPagePayloadDTO` containing `MetaTagsDTO` and generated schema DTOs; it does not return an HTTP response or write template output. A `SeoHeadHtmlRenderer` can render that payload after composition.

`RenderSeoPageCommand::$schemas` accepts values that implement `JsonSerializable`. A `JsonLdBuilderInterface` builder does not implement `JsonSerializable` and cannot be passed directly; materialize its array and wrap it in a `JsonLdSchemaDTO` before passing it to the command.

```php
use Maatify\Seo\Shared\DTO\Schema\JsonLdSchemaDTO;
use Maatify\Seo\Web\JsonLd\Builder\ProductJsonLdBuilder;
use Maatify\Seo\Web\Render\SeoHeadHtmlRenderer;
use Maatify\Seo\Web\SeoRender\Command\RenderSeoPageCommand;

$productSchemaBuilder = (new ProductJsonLdBuilder())->setName('Blue Shirt');
$productSchema = new JsonLdSchemaDTO($productSchemaBuilder->toArray());

$payload = $seoPageRenderService->render(new RenderSeoPageCommand(
    entityType: 'product',
    entityId: 'sku-123',
    languageId: 1,
    defaultTitle: 'Blue Shirt',
    defaultDescription: 'Cotton shirt',
    slug: 'blue-shirt',
    schemas: [$productSchema],
));

$headHtml = (new SeoHeadHtmlRenderer())->renderPayload($payload);
```

The executed [`seo-page-render.php`](../../examples/seo-page-render.php) returns
a payload with metadata, a schema list, and nullable optional sections:

```json
{
  "meta_tags": {
    "title": "About Example.com",
    "description": "Learn how Example.com helps teams publish discoverable content.",
    "canonical_url": "https://example.com/en/page/about",
    "robots": "index,follow",
    "open_graph_title": "About Example.com",
    "open_graph_description": "Learn how Example.com helps teams publish discoverable content.",
    "open_graph_url": "https://example.com/en/page/about",
    "twitter_title": "About Example.com",
    "twitter_description": "Learn how Example.com helps teams publish discoverable content.",
    "open_graph_type": null,
    "open_graph_image": null,
    "twitter_card": null,
    "twitter_image": null
  },
  "schemas": [
    {
      "@context": "https://schema.org",
      "@graph": [
        {"@type":"Product","name":"Example Product"},
        {"@type":"WebPage","name":"About Example.com","url":"https://example.com/en/page/about","description":"Learn how Example.com helps teams publish discoverable content."}
      ]
    }
  ],
  "redirect_decision": null,
  "sitemap_xml": null
}
```

The Product builder is materialized as
`new JsonLdSchemaDTO($productSchemaBuilder->toArray())` before it enters
`RenderSeoPageCommand::$schemas`, which accepts `JsonSerializable` values.
`JsonLdBuilderInterface` builders themselves are not `JsonSerializable` and
cannot be passed directly. The service composes the package payload; the Host
renders/places it and sends the HTTP response.

The renderer is a presentation step over the service payload. Use the lower-level builders and renderers directly when the Host needs a custom composition. Optional redirect resolution returns a domain redirect decision for the Host to apply; the Host still delivers the HTTP response.

### Canonical URLs and hreflang

`CanonicalUrlBuilder` assembles a URL from an optional base, path, and query parameters and can render a canonical link tag. `HreflangLinkBuilder` composes alternate links and `HreflangLinkRenderer` renders them as HTML. These are generation helpers; use separate validation profiles when checking a canonical candidate or a supplied hreflang cluster.

```php
use Maatify\Seo\Web\Hreflang\HreflangLinkBuilder;
use Maatify\Seo\Web\Indexing\CanonicalUrlBuilder;

$canonical = (new CanonicalUrlBuilder('https://example.com'))
    ->setPath('/articles/seo')
    ->setQueryParams(['page' => 2, 'tracking' => null])
    ->build();

$alternates = (new HreflangLinkBuilder())
    ->add('en', 'https://example.com/en/articles/seo')
    ->add('fr', 'https://example.com/fr/articles/seo')
    ->xDefault('https://example.com/en/articles/seo');

$hreflangHtml = $alternates->render();
```

`CanonicalUrlBuilder` joins the optional base and path, removes null query
values, and emits a canonical link tag with the query value HTML-escaped. The
executed filtered-query example returns:

```html
<link rel="canonical" href="https://example.com/blog?page=3&amp;sort=recent">
```

The `HreflangLinkBuilder` exposes normalized `HreflangLinkDTO` objects, and
`HreflangLinkRenderer` renders them. The executed fixture returns:

```html
<link rel="alternate" hreflang="en" href="https://example.com/en/page">
<link rel="alternate" hreflang="en-US" href="https://example.com/en-us/page">
<link rel="alternate" hreflang="en-GB" href="https://example.com/en-gb/page">
<link rel="alternate" hreflang="fr" href="https://example.com/fr/page">
<link rel="alternate" hreflang="x-default" href="https://example.com/en/page">
```

`GoogleCanonicalValidator` and `GoogleHreflangClusterValidator` return separate companion diagnostics. The hreflang profile checks its defined lexical, URL, self-reference, reciprocity, and alternate-set boundaries; it does not establish ISO registry membership. Generation alone does not assert that a cluster passes validation.

URL generation is not semantic selection or validation: the Host chooses which
page URL is canonical. The separate profiles diagnose the supplied candidate
or cluster, and neither profile proves ISO registry membership or guarantees a
search engine will honor the declarations.

### Admin-domain utilities

The package's Admin namespace contains domain operations and data helpers, not an Admin application. `AdminRedirectCommandService` and `AdminRedirectQueryService` create, update, retrieve, list, and delete redirect records; `AdminSeoOverrideCommandService` and `AdminSeoOverrideQueryService` provide corresponding override operations; `AdminSlugHistoryCommandService` and `AdminSlugHistoryQueryService` record and query prior slugs. Redirect decisions and configured status values are domain data for the Host to apply to its HTTP response.

`SerpPreviewFactory` and `SocialPreviewFactory` can consume a preset or `MetaTagsDTO` and return preview DTOs with missing-field warnings. `SeoMetadataExporter` serializes override, redirect, and slug-history data; `SeoMetadataImporter` validates JSON/array payloads and supports dry runs, with repositories supplied when writes are intended.

```php
use Maatify\Seo\Admin\Export\SeoMetadataExporter;
use Maatify\Seo\Admin\Import\SeoMetadataImporter;
use Maatify\Seo\Admin\Preview\SerpPreviewFactory;
use Maatify\Seo\Admin\Preview\SocialPreviewFactory;

$serpPreview = SerpPreviewFactory::fromPreset($preset);
$socialPreview = SocialPreviewFactory::fromPreset($preset, 'Example Store');

$exporter = new SeoMetadataExporter();
$export = $exporter->export($seoOverrides, $redirects, $slugHistory);
$json = $exporter->toJson($export);

$importer = new SeoMetadataImporter(
    seoOverrideRepository: $seoOverrideRepository,
    redirectRepository: $redirectRepository,
    slugHistoryRepository: $slugHistoryRepository,
);
$dryRun = $importer->importJson($json, dryRun: true);
```

Supply the package repository implementations only when the importer is intended to write those sections; an unconfigured importer can still validate and run a dry run.

The Host still owns Admin UI, routes, controllers, authentication, permissions, and workflow. It chooses which service results to display, when to invoke writes, and how to map domain outcomes to HTTP behavior.

#### Redirect resolution and slug history

When a Host changes an entity slug, `SlugHistoryService` can record the old
slug, optionally create redirect intent, and the resolver can turn an old slug
into a `RedirectDecisionDTO`. The runnable example uses in-memory repository
fixtures; a persistent Host can use the package repositories and package-owned
schemas described in the integration guide.

The fixture records a slug-history DTO and resolves a redirect decision with
these values:

The service calls for the recorded change and subsequent lookup are:

```php
use Maatify\Seo\Shared\Command\SlugHistory\RecordSlugChangeCommand;
use Maatify\Seo\Shared\Command\Redirect\ResolveRedirectCommand;

$historyId = $slugHistoryService->recordSlugChange(new RecordSlugChangeCommand(
    entityType: 'product',
    entityId: '42',
    languageId: 1,
    oldSlug: 'widget-pro',
    newSlug: 'super-widget-pro',
    createRedirect: true,
));
$history = $slugHistoryQueryService->getById($historyId);

$decision = $redirectManagerService->resolve(new ResolveRedirectCommand(
    entityType: 'product',
    languageId: 1,
    requestedSlug: 'widget-pro',
));
```

Here the Host provides configured repositories/services and an implementation
of `HostUrlGeneratorInterface`; the runnable example uses in-memory test
fixtures.

```json
{
  "id": 1,
  "entity_type": "product",
  "entity_id": "42",
  "language_id": 1,
  "old_slug": "widget-pro",
  "created_at": "2026-09-08T12:00:00+00:00",
  "deleted_at": null
}
```

```json
{
  "should_redirect": true,
  "http_status": 301,
  "target_entity_type": "product",
  "target_entity_id": "42",
  "target_url": "https://example.com/product/super-widget-pro",
  "redirect": {
    "id": 1,
    "entity_type": "product",
    "language_id": 1,
    "requested_slug": "widget-pro",
    "target_entity_type": "product",
    "target_entity_id": "42",
    "http_status": 301,
    "created_at": "2026-09-08T12:00:00+00:00",
    "deleted_at": null
  }
}
```

`should_redirect: false` means there is no redirect decision to apply; a
`should_redirect: true` result carries the configured status and, when
available, target URL. The package returns this domain decision only. The Host
loads/owns the target entity lifecycle and sends the HTTP status and `Location`
response.

#### SEO override DTO and resolution

The override command/query services create and retrieve values such as this
serialized `SeoOverrideDTO` from the runnable fixture:

```php
use Maatify\Seo\Shared\Command\SeoOverride\CreateSeoOverrideCommand;

$overrideId = $overrideCommandService->create(new CreateSeoOverrideCommand(
    entityType: 'product',
    entityId: '42',
    languageId: 1,
    metaTitle: 'Manual Product Title | Example Store',
    metaDescription: 'Manual product description supplied by the SEO override workflow.',
));
$activeOverride = $overrideQueryService->getActiveForEntity('product', '42', 1);
```

```json
{
  "id": 1,
  "entity_type": "product",
  "entity_id": "42",
  "language_id": 1,
  "meta_title": "Manual Product Title | Example Store",
  "meta_description": "Manual product description supplied by the SEO override workflow.",
  "created_at": "2026-09-08T12:00:00+00:00",
  "updated_at": "2026-09-08T12:00:00+00:00",
  "deleted_at": null
}
```

`MetaGeneratorService` queries the active override for the same entity and
language and combines it with Host-supplied defaults. Its full resulting
`MetaTagsDTO` is shown under [metadata orchestration](#metadata-orchestration-with-metageneratorservice).

#### Admin previews

`SerpPreviewFactory` and `SocialPreviewFactory` take a metadata array, DTO, or
preset and return `SerpPreviewDTO` / `SocialPreviewDTO` data. For the runnable
preview fixture, the two separately returned DTOs have these values. They are
displayed in one object here only to place the outputs side by side:

```json
{
  "serp_preview": {
    "title": "My Page Title - Example",
    "description": "This is the description that will show up in search engine results.",
    "url": "https://example.com/my-page",
    "display_url": "example.com/my-page",
    "robots": "index, follow",
    "warnings": [],
    "score": null,
    "status": null
  },
  "social_preview": {
    "title": "My Social Media Title",
    "description": "A catchy description for Facebook, LinkedIn, etc.",
    "image_url": "https://example.com/social-share.jpg",
    "url": "https://example.com/my-page",
    "type": "website",
    "site_name": "My Example Site",
    "twitter_card": "summary_large_image",
    "warnings": []
  }
}
```

These are preview data and warnings for a Host Admin screen to present. They
are not a framework UI, a live search result, or a promise about how an
external service displays the page.

Those output DTOs can also be built from arrays when no preset is available:

```php
$serpPreview = SerpPreviewFactory::fromArray([
    'title' => 'My Page Title - Example',
    'description' => 'This is the description that will show up in search engine results.',
    'url' => 'https://example.com/my-page',
    'robots' => 'index, follow',
]);

$socialPreview = SocialPreviewFactory::fromArray([
    'title' => 'My Social Media Title',
    'description' => 'A catchy description for Facebook, LinkedIn, etc.',
    'url' => 'https://example.com/my-page',
    'image_url' => 'https://example.com/social-share.jpg',
    'site_name' => 'My Example Site',
    'twitter_card' => 'summary_large_image',
]);
```

#### Metadata import/export

`SeoMetadataExporter` emits a versioned JSON-compatible DTO with `schema_version`,
`exported_at`, and a `data` object containing `seo_overrides`, `redirects`, and
`slug_history`. The current example passes that JSON to
`SeoMetadataImporter::importJson(..., dryRun: true)`; no CSV format is exposed
by this example. Its dry-run result is:

```text
dryRun = true
created = 3
updated = 0
failed = 0
errors = []
```

The serialized `SeoMetadataImportResultDTO` for that run is:

```json
{
  "created": 3,
  "updated": 0,
  "skipped": 0,
  "failed": 0,
  "errors": [],
  "dry_run": true
}
```

The executable [`import-export.php`](../../examples/import-export.php) prints
the concrete sample export records. `exported_at` is generated at runtime, so
its timestamp varies. A dry run validates/counts but does not persist. For
writes, supply the package repositories; the Host still decides when to invoke
the import and how to expose its result.

The stable data portion of the executed example's export is:

```json
{
  "schema_version": "1.0",
  "data": {
    "seo_overrides": [{
      "entity_type": "product",
      "entity_id": "123",
      "language_id": 1,
      "meta_title": "Custom Product Title",
      "meta_description": "A custom description for product 123."
    }],
    "redirects": [{
      "entity_type": "product",
      "language_id": 1,
      "requested_slug": "old-product-page",
      "target_entity_type": "product",
      "target_entity_id": "123",
      "http_status": 301
    }],
    "slug_history": [{
      "entity_type": "category",
      "entity_id": "456",
      "language_id": 1,
      "old_slug": "old-category"
    }]
  }
}
```

---

## 14. Common Mistakes

When implementing the Maatify SEO library, ensure you adhere strictly to the following guidelines:

*   **Do not output HTTP responses directly from the library.** All SEO services and helpers return strings or DTOs. The host application must format the final HTTP response (e.g., managing the `Content-Type: application/xml` header for sitemaps).
*   **Do not embed routing or controller logic.** Route mapping belongs exclusively within the host app.
*   **Do not hardcode framework dependencies.** Do not attempt to use `Illuminate\Support` or `Symfony\Component\HttpFoundation` inside the core library. Ensure integration points use standard PHP functionality or provided contracts.
*   **Do not commit `composer.lock`.** As a library package, `composer.lock` should not be tracked to allow proper dependency resolution in host environments.
*   **Do not rely on the `spatie/schema-org` package unless installed.** The library does not enforce this package as a required dependency. The provided adapter checks for class existence before relying on the object methods, allowing the host application to opt-in independently.

### Validation Report Builder

The `SeoValidationReportBuilder` combines validation (`SeoMetaValidator`) and scoring (`SeoValidationScoreCalculator`) into a comprehensive report object (`SeoValidationReportDTO`). This provides a single, easy-to-use interface for both processes while preserving original metadata input and any context you wish to pass through.

#### Building a Report

You can provide metadata, validation options, score options, and custom context. The `SeoValidationPreset` helper is a great fit here:

```php
use Maatify\Seo\Web\Validation\SeoValidationReportBuilder;
use Maatify\Seo\Web\Validation\SeoValidationPreset;

$metaData = [
    'title' => 'Missing Canonical URL Example',
    'description' => 'This page has a title and description, but is missing a canonical URL.',
];

$preset = SeoValidationPreset::strict();

$report = SeoValidationReportBuilder::build(
    meta: $metaData,
    validationOptions: $preset['validationOptions'],
    scoreOptions: $preset['scoreOptions'],
    context: [
        'url' => 'https://example.com/products/demo',
        'entityType' => 'product',
        'entityId' => 123,
        'language' => 'en',
        'source' => 'qa',
    ],
);
```

> **Note:** Providing invalid types or configuration values in `validationOptions` or `scoreOptions` will throw `SeoInvalidArgumentException`. The `SeoValidationReportBuilder` does not mutate your original metadata array/object, does not change the behavior of the `SeoMetaValidator` or `SeoValidationScoreCalculator`, and does not emit HTTP headers, routes, controllers, or responses. It is fully framework-neutral.

#### Reading the Report

The `$report` (`SeoValidationReportDTO`) provides several ways to consume the result:

- `$report->isValid`: Boolean. `false` if any errors exist.
- `$report->isHealthy`: Boolean. `false` if the score is below the `healthyMinimumScore`.
- `$report->score`: Integer (0-100).
- `$report->grade`: String (A, B, C, D, or F).
- `$report->errorCount`, `$report->warningCount`, `$report->infoCount`: Integers.
- `$report->issues`, `$report->errors`, `$report->warnings`, `$report->info`: Arrays of issue shapes (`code`, `severity`, `message`, `field`).
- `$report->deductions`: Array of applied point deductions shapes (`code`, `severity`, `field`, `points`).
- `$report->context`: Your optional `context` array is preserved exactly as-is. Typical keys might be `url`, `entityType`, `entityId`, `language`, or `source`.
- `$report->summary`: An array with `status` and `message` keys indicating the overall validation status.

#### Summary Status Rules

The `$report->summary['status']` and `$report->summary['message']` are determined using the following rules:
- **`fail`**: If the validation has errors (`isValid` is false). Message: `SEO validation failed.`
- **`warning`**: If there are no errors, but warnings exist OR the score is not healthy. Message: `SEO validation completed with warnings.`
- **`pass`**: If valid, healthy, and no warnings exist. Message: `SEO validation passed.`

#### Exporting the Report


### Validation Batch Report Builder

The `SeoValidationBatchReportBuilder` allows you to build SEO validation reports for multiple pages/products/entities in one framework-neutral batch. It provides aggregate counts, score stats, and summary status. It is particularly useful for QA crawls, admin dashboards, audits, CI reports, and bulk product/category checks.

It accepts an `$items` array which must be a non-empty list. Each item is an associative array that requires a `meta` key (array or object) and accepts an optional `context` array. The builder can also accept a `$sharedContext` array, which is merged into each item's context. If an item provides context keys that overlap with the shared context, the item's context overrides the shared context.

```php
use Maatify\Seo\Web\Validation\SeoValidationBatchReportBuilder;
use Maatify\Seo\Web\Validation\SeoValidationPreset;

$preset = SeoValidationPreset::standard();
$batch = SeoValidationBatchReportBuilder::build(
    items: [
        [
            'meta' => [
                'title' => 'Product A',
                'description' => 'Product A description long enough for SEO snippets.',
                'canonical' => 'https://example.com/products/a',
            ],
            'context' => [
                'url' => 'https://example.com/products/a',
                'entityType' => 'product',
                'entityId' => 101,
            ],
        ],
        [
            'meta' => [
                'title' => 'Product B',
                'description' => 'Product B description long enough for SEO snippets.',
                'canonical' => 'https://example.com/products/b',
            ],
            'context' => [
                'url' => 'https://example.com/products/b',
                'entityType' => 'product',
                'entityId' => 102,
            ],
        ],
    ],
    validationOptions: $preset['validationOptions'],
    scoreOptions: $preset['scoreOptions'],
    sharedContext: [
        'language' => 'en',
        'source' => 'qa-crawl',
    ],
);

$array = $batch->toArray();
$json = json_encode($batch, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
```

The output DTO (`SeoValidationBatchReportDTO`) contains counts (`totalCount`, `validCount`, `invalidCount`), score stats (`averageScore`, `minScore`, `maxScore`), and summary rules: it fails if any report is invalid, warns if all reports are valid but any report is unhealthy or has warnings, and passes when all reports are valid, healthy, and warning-free.

### Validation Batch Report Exporter

The `SeoValidationBatchReportExporter` can export the batch DTO into arrays, JSON, summary arrays, and Markdown. It does not mutate the batch DTO, does not call validator/score/report/batch builder internally, and emits no HTTP output.

`toArray()` returns the full batch DTO data using existing batch serialization.
`toJson()` returns a JSON string, uses readable defaults, respects custom JSON flags, and throws `SeoInvalidArgumentException` if encoding fails.
`toSummaryArray()` returns compact batch status/counts/score stats/message data.
`toMarkdown()` returns a plain Markdown batch report with summary, status, message, valid/healthy flags, total/valid/invalid counts, healthy/unhealthy counts, error/warning/info counts, average/min/max score, per-report summaries, and report context when present.

```php
use Maatify\Seo\Web\Validation\SeoValidationBatchReportBuilder;
use Maatify\Seo\Web\Validation\SeoValidationBatchReportExporter;
use Maatify\Seo\Web\Validation\SeoValidationPreset;

$preset = SeoValidationPreset::standard();
$batch = SeoValidationBatchReportBuilder::build(
    items: [
        [
            'meta' => [
                'title' => 'Product A',
                'description' => 'Product A description long enough for SEO snippets.',
                'canonical' => 'https://example.com/products/a',
            ],
            'context' => [
                'url' => 'https://example.com/products/a',
                'entityType' => 'product',
                'entityId' => 101,
            ],
        ],
        [
            'meta' => [
                'title' => 'Product B',
                'description' => 'Product B description long enough for SEO snippets.',
                'canonical' => 'https://example.com/products/b',
            ],
            'context' => [
                'url' => 'https://example.com/products/b',
                'entityType' => 'product',
                'entityId' => 102,
            ],
        ],
    ],
    validationOptions: $preset['validationOptions'],
    scoreOptions: $preset['scoreOptions'],
    sharedContext: [
        'language' => 'en',
        'source' => 'qa-crawl',
    ],
);

// Full array export
$fullArray = SeoValidationBatchReportExporter::toArray($batch);

// JSON string export
$json = SeoValidationBatchReportExporter::toJson($batch);

// Compact summary array
$summary = SeoValidationBatchReportExporter::toSummaryArray($batch);
/*
[
    'isValid' => true,
    'isHealthy' => true,
    'totalCount' => 2,
    'validCount' => 2,
    'invalidCount' => 0,
    'healthyCount' => 2,
    'unhealthyCount' => 0,
    'errorCount' => 0,
    'warningCount' => 0,
    'infoCount' => 0,
    'averageScore' => 100.0,
    'minScore' => 100,
    'maxScore' => 100,
    'status' => 'pass',
    'message' => 'SEO batch validation passed.',
]
*/

// Markdown export
// Note: Do not hardcode full markdown output if it is too long. Show short representative output only.
$markdown = SeoValidationBatchReportExporter::toMarkdown($batch);
```

```php
$batch->summary['status']; // pass | warning | fail
$batch->totalCount;
$batch->validCount;
$batch->invalidCount;
$batch->averageScore;
$batch->reports[0]->summary['message'];
```

> **Note:** The `SeoValidationBatchReportBuilder` uses `SeoValidationReportBuilder::build(...)` internally for each item. It does not mutate the input data. It is completely framework-neutral and emits no HTTP headers, routes, controllers, or responses. Existing validation, score, report builder, exporter, preset, and robots behaviors remain unchanged; sitemap DTO URL generation now preserves the supported extended child collections through the canonical XML path.

You can easily export the report into various formats using the `SeoValidationReportExporter`:

```php
use Maatify\Seo\Web\Validation\SeoValidationReportExporter;

// Export as a complete array (same as calling $report->toArray())
$array = SeoValidationReportExporter::toArray($report);

// Export as a JSON string
$json = SeoValidationReportExporter::toJson($report);

// Export as a compact summary array for quick logging or dashboard APIs
$summary = SeoValidationReportExporter::toSummaryArray($report);
/*
[
    'isValid' => false,
    'isHealthy' => true,
    'score' => 90,
    'grade' => 'A',
    'errorCount' => 0,
    'warningCount' => 1,
    'infoCount' => 0,
    'status' => 'warning',
    'message' => 'SEO validation completed with warnings.'
]
*/

// Export as human-readable Markdown for CI/CD output or pull request comments
$markdown = SeoValidationReportExporter::toMarkdown($report);
```

## 15. Optional Search Console Indexed-Result Verification

The optional Search Console boundary inspects Google's indexed version of a URL
through the URL Inspection API. The library does not perform HTTP, OAuth, or
credential handling. A host application supplies a transport implementation and
keeps the resulting `SearchConsoleInspectionResultDTO` separate from core SEO
validation and scoring.

The following is the integration shape; `HostSearchConsoleGateway` represents a
host-owned adapter around the host's configured HTTP and OAuth facilities:

```php
use Maatify\Seo\Web\Indexing\SearchConsole\DTO\SearchConsoleInspectionRequestDTO;
use Maatify\Seo\Web\Indexing\SearchConsole\DTO\SearchConsoleTransportResponseDTO;
use Maatify\Seo\Web\Indexing\SearchConsole\Mapper\SearchConsoleResponseMapper;
use Maatify\Seo\Web\Indexing\SearchConsole\SearchConsoleInspectionService;
use Maatify\Seo\Web\Indexing\SearchConsole\SearchConsoleTransportInterface;

final readonly class HostSearchConsoleTransport implements SearchConsoleTransportInterface
{
    public function __construct(private HostSearchConsoleGateway $gateway)
    {
    }

    public function inspect(SearchConsoleInspectionRequestDTO $request): SearchConsoleTransportResponseDTO
    {
        $response = $this->gateway->inspect(
            $request->inspectionUrl,
            $request->siteUrl,
            $request->languageCode,
        );

        return new SearchConsoleTransportResponseDTO(
            httpStatus: $response->httpStatus,
            decodedBody: $response->decodedBody,
        );
    }
}

$service = new SearchConsoleInspectionService(
    new HostSearchConsoleTransport($hostSearchConsoleGateway),
    new SearchConsoleResponseMapper(),
);

$result = $service->inspect(new SearchConsoleInspectionRequestDTO(
    inspectionUrl: 'https://example.com/articles/seo/',
    siteUrl: 'https://example.com/',
    languageCode: 'en-US',
));
```

`HostSearchConsoleGateway` is intentionally not implemented by the library: it
owns the Google request, OAuth credentials, and the recommended
`webmasters.readonly` scope. The mapped result reports provider/index evidence
only; it does not alter `SeoMetaValidator`, scoring, summaries, batch reports, or
existing exporters. A missing provider `richResultsResult` remains `null` rather
than being treated as a pass, and this API is not a live Rich Results Test.

[`search-console-response-mapping.php`](../../examples/search-console-response-mapping.php)
uses a local sample transport to demonstrate request → response mapping. Its
fixture is a **sample provider payload**, not a captured response or proof of
the state of a real URL:

```json
{
  "inspectionResult": {
    "inspectionResultLink": "https://search.google.com/search-console/inspect?resource_id=https%3A%2F%2Fexample.com%2F",
    "indexStatusResult": {
      "verdict": "PASS",
      "coverageState": "Submitted and indexed",
      "robotsTxtState": "ALLOWED",
      "indexingState": "INDEXING_ALLOWED",
      "lastCrawlTime": "2026-09-08T12:00:00Z",
      "pageFetchState": "SUCCESSFUL",
      "googleCanonical": "https://example.com/guides/seo",
      "userCanonical": "https://example.com/guides/seo",
      "crawledAs": "MOBILE"
    },
    "richResultsResult": {
      "verdict": "PASS",
      "detectedItems": [{
        "richResultType": "Article",
        "items": [{"name":"SEO guide","issues":[]}]
      }]
    }
  }
}
```

Given that fixture and a `200` transport response, the mapper returns this
package DTO serialization:

```json
{
  "provider_identity": "Google Search Console",
  "inspection_result_link": "https://search.google.com/search-console/inspect?resource_id=https%3A%2F%2Fexample.com%2F",
  "index_status_result": {
    "verdict": "PASS",
    "coverage_state": "Submitted and indexed",
    "robots_txt_state": "ALLOWED",
    "indexing_state": "INDEXING_ALLOWED",
    "last_crawl_time": "2026-09-08T12:00:00Z",
    "page_fetch_state": "SUCCESSFUL",
    "google_canonical": "https://example.com/guides/seo",
    "user_canonical": "https://example.com/guides/seo",
    "crawled_as": "MOBILE"
  },
  "rich_results_result": {
    "verdict": "PASS",
    "detected_items": [{
      "rich_result_type": "Article",
      "items": [{"name":"SEO guide","issues":[]}]
    }]
  }
}
```

The request DTO carries `inspectionUrl`, `siteUrl`, and optional `languageCode`;
the Host transport returns `httpStatus` plus a decoded body. The service
validates the request, invokes the transport once, and maps the body. Invalid
requests, malformed required response shape, and non-2xx transport responses
have distinct package exception types. The Host chooses retry/backoff policy,
maps package exceptions into its own application behavior, and never treats a
provider HTTP code as an application HTTP status automatically.

## 16. Optional Merchant Center Eligibility Diagnostics

The optional Merchant Center boundary provides typed DTOs and orchestration for reading product eligibility and issues from the Google Merchant API v1. The library handles the validation and mapping, but the host application is responsible for the HTTP transport, OAuth (`https://www.googleapis.com/auth/content`), JSON decoding, pagination, and scheduling.

The host must implement `MerchantCenterTransportInterface`. Unknown provider values are passed through safely, and the library does not attempt automatic remediation.

```php
use Maatify\Seo\Web\MerchantCenter\DTO\MerchantCenterAggregateRequestDTO;
use Maatify\Seo\Web\MerchantCenter\DTO\MerchantCenterProductRequestDTO;
use Maatify\Seo\Web\MerchantCenter\DTO\MerchantCenterTransportResponseDTO;
use Maatify\Seo\Web\MerchantCenter\Mapper\MerchantCenterResponseMapper;
use Maatify\Seo\Web\MerchantCenter\MerchantCenterDiagnosticsService;
use Maatify\Seo\Web\MerchantCenter\MerchantCenterTransportInterface;

final readonly class HostMerchantCenterTransport implements MerchantCenterTransportInterface
{
    public function __construct(private HostMerchantCenterGateway $gateway)
    {
    }

    public function getProduct(
        MerchantCenterProductRequestDTO $request
    ): MerchantCenterTransportResponseDTO {
        // Host executes GET https://merchantapi.googleapis.com/products/v1/{$request->name}
        $response = $this->gateway->fetchProduct($request->name);

        return new MerchantCenterTransportResponseDTO(
            httpStatus: $response->httpStatus,
            decodedBody: $response->decodedBody,
        );
    }

    public function listAggregateProductStatuses(
        MerchantCenterAggregateRequestDTO $request
    ): MerchantCenterTransportResponseDTO {
        // Host executes GET https://merchantapi.googleapis.com/issueresolution/v1/{$request->parent}/aggregateProductStatuses
        $response = $this->gateway->fetchAggregateStatuses(
            $request->parent,
            $request->pageSize,
            $request->pageToken,
            $request->filter
        );

        return new MerchantCenterTransportResponseDTO(
            httpStatus: $response->httpStatus,
            decodedBody: $response->decodedBody,
        );
    }
}

$service = new MerchantCenterDiagnosticsService(
    new HostMerchantCenterTransport($hostMerchantCenterGateway),
    new MerchantCenterResponseMapper(),
);

// Fetch specific product diagnostics
$productResult = $service->getProductDiagnostics(
    new MerchantCenterProductRequestDTO(
        name: 'accounts/123/products/en~US~sku123'
    )
);

// Fetch aggregate statistics (host manages pagination via nextPageToken)
$aggregateResult = $service->listAggregateProductDiagnostics(
    new MerchantCenterAggregateRequestDTO(
        parent: 'accounts/123',
        pageSize: 100, // Provider may coerce >250, library passes it through
    )
);
```

[`merchant-center-diagnostics.php`](../../examples/merchant-center-diagnostics.php)
uses a local fixture and a sample `MerchantCenterTransportInterface`. The
**sample provider payload** demonstrates fields the mapper accepts; it is not
live account data:

```json
{
  "name": "accounts/123/products/en~US~sku123",
  "productStatus": {
    "destinationStatuses": [{
      "reportingContext": "SHOPPING_ADS",
      "approvedCountries": ["US"],
      "pendingCountries": [],
      "disapprovedCountries": []
    }],
    "itemLevelIssues": [{
      "code": "missing_value",
      "severity": "ERROR",
      "resolution": "MERCHANT_ACTION",
      "attribute": "title",
      "reportingContext": "SHOPPING_ADS",
      "description": "A title is missing.",
      "detail": "Add a title to the product.",
      "documentation": "https://support.google.com/merchants/answer/example",
      "applicableCountries": ["US", "CA"]
    }],
    "creationDate": "2026-09-01T10:00:00Z",
    "lastUpdateDate": "2026-09-08T12:00:00Z",
    "googleExpirationDate": "2026-10-08T12:00:00Z"
  }
}
```

With that fixture, the executed example's selected package DTO fields are:

```json
{
  "productName": "accounts/123/products/en~US~sku123",
  "destinationStatuses": [{
    "reportingContext": "SHOPPING_ADS",
    "approvedCountries": ["US"],
    "pendingCountries": [],
    "disapprovedCountries": []
  }],
  "itemLevelIssues": [{
    "code": "missing_value",
    "severity": "ERROR",
    "resolution": "MERCHANT_ACTION",
    "attribute": "title",
    "description": "A title is missing.",
    "detail": "Add a title to the product.",
    "applicableCountries": ["US", "CA"]
  }],
  "creationDate": "2026-09-01T10:00:00Z",
  "lastUpdateDate": "2026-09-08T12:00:00Z",
  "googleExpirationDate": "2026-10-08T12:00:00Z"
}
```

The request carries a product resource `name`. The Host implementation supplies
network access, OAuth credentials, and decoded transport values; the service
validates the request, invokes the transport, and maps provider fields into a
`MerchantCenterProductStatusResultDTO` or aggregate status DTO. Aggregate
results include `nextPageToken` for Host-managed
pagination. Malformed responses and non-2xx responses are package exceptions;
the Host owns retry/backoff and application error mapping. The output is
provider evidence and issue mapping: this DTO does not add a separate overall
eligibility verdict, promise real-world item approval, or remediate issues.
