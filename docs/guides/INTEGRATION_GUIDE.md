# SEO Library Integration Guide

This guide explains how host applications should integrate the Maatify SEO library. The library is strictly framework-neutral and decoupled from any specific routing, presentation, or DI container.

For the current package contract and complete runtime inventory, see the
[canonical Package Reference](../../SEO_PACKAGE_REFERENCE.md).
For practical choices and executed output examples, see the
[Usage Guide decision map](USAGE_GUIDE.md#capability-decision-map).

---

## 1. Overview

The core philosophy of the SEO library is simple: **it computes data and returns it**.

- The library returns pure DTOs, PHP strings, and standard service results.
- **The host application owns** the HTTP responses, controllers, route definitions, template engines, DI containers, and database wiring.
- The SEO library has zero dependency on frameworks like Slim, Laravel, Symfony, Twig, or Blade.

---

## 2. Recommended Integration Architecture

To keep your application decoupled and testable, follow this data flow:

1.  **Request Handling:** The framework's route or controller receives the HTTP request.
2.  **Data Preparation:** The controller calls your host's internal services to fetch the necessary page/product data.
3.  **SEO Generation:** The controller passes this data into the SEO library (e.g., using `FluentSeoBuilder` or `SeoHeadHtmlRenderer`) to build the DTOs or rendered HTML output.
4.  **Template Rendering:** The controller passes the final SEO output (as a string or a DTO) to the template engine.
5.  **Response:** The controller sends the template output in an HTTP response.

*Best Practice:* Keep the construction of SEO objects in the controller/service layer, not inside the template files.

---

## 3. Plain PHP Integration

Integrating without a framework is straightforward using Composer autoloading.

```php
<?php
// my-page.php
require 'vendor/autoload.php';

use Maatify\Seo\Web\Builder\FluentSeoBuilder;

// 1. Build SEO output using the Fluent Builder
$seoBuilder = (new FluentSeoBuilder())
    ->title('My Awesome Page')
    ->description('Learn more about our plain PHP integration.')
    ->canonical('https://example.com/my-page')
    ->schema([
        '@context' => 'https://schema.org',
        '@type'    => 'WebPage',
        'name'     => 'My Awesome Page',
    ]);

// 2. Render to a plain string
$seoHtml = $seoBuilder->render();

// Note on Escaping: The SEO library renderers natively and safely escape
// generated tags (e.g., htmlspecialchars on title and description).
// However, the host application is still responsible for how it echoes the final string.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- 3. Output inside the plain PHP template -->
    <?= $seoHtml ?>
</head>
<body>
    <h1>Welcome to Plain PHP</h1>
</body>
</html>
```

---

## 4. Slim Integration

Slim Framework integration relies on building the SEO content inside the route callback and passing the resulting payload to your view layer.

```php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Maatify\Seo\Web\Builder\FluentSeoBuilder;

$app->get('/products/{id}', function (Request $request, Response $response, array $args) {

    // 1. Fetch product from your host application
    $product = $this->get('ProductService')->find($args['id']);

    // 2. Build SEO output
    $seoBuilder = (new FluentSeoBuilder())
        ->title($product->name . ' - Store')
        ->description($product->shortDescription);

    // You can pass the raw string ($seoHtml) or a structured DTO ($seoDto)
    $seoHtml = $seoBuilder->render();
    // $seoDto = $seoBuilder->renderDto();

    // 3. Render the template
    // IMPORTANT: The SEO library does not return PSR-7 responses. You must write it to the response.
    return $this->get('view')->render($response, 'product.twig', [
        'product' => $product,
        'seoHtml' => $seoHtml
    ]);
});
```

---

## 5. Laravel Integration

In Laravel, manage SEO inside Controllers, View Composers, or dedicated host-level services. **Do not add Laravel-specific packages or dependencies to the SEO library itself.**

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatify\Seo\Web\Builder\FluentSeoBuilder;
use App\Models\Product;

class ProductController extends Controller
{
    public function show(Request $request, $id)
    {
        // 1. Fetch the product
        $product = Product::findOrFail($id);

        // 2. Build SEO output
        $builder = (new FluentSeoBuilder())
            ->title($product->name)
            ->description($product->description)
            ->canonical(route('products.show', $product->id));

        $seoHtml = $builder->render();
        // Alternatively, use ->renderDto() if you want Blade to place sections individually

        // 3. Pass to Blade template
        return view('products.show', [
            'product' => $product,
            'seoHtml' => $seoHtml,
        ]);
    }
}
```

---

## 6. Template Integration

### Twig Example (Pre-rendered string)

If you passed `$seoHtml` to Twig, use the `raw` filter to output the unescaped HTML block (since the SEO library already handled attribute escaping safely).

```twig
{# product.twig #}
<!DOCTYPE html>
<html>
<head>
    {{ seoHtml|raw }}
</head>
<body>
    <h1>{{ product.name }}</h1>
</body>
</html>
```

### Blade Example (Pre-rendered string)

In Blade, use the `{!! !!}` syntax.

```blade
{{-- product.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    {!! $seoHtml !!}
</head>
<body>
    <h1>{{ $product->name }}</h1>
</body>
</html>
```

### DTO Section Rendering Example

If you used `$seoDto = $builder->renderDto();` and passed it to the template (e.g., as `$seo`), you can distribute the tags to different blocks or sections.

```blade
{{-- layout.blade.php --}}
<head>
    {{-- Core Meta (Title, Description, Canonical) --}}
    {!! $seo->metaHtml !!}

    {{-- Social Tags --}}
    {!! $seo->openGraphHtml !!}
    {!! $seo->twitterCardHtml !!}

    {{-- Structured Data --}}
    {!! $seo->jsonLdHtml !!}

    {{-- Or just use the pre-combined full string --}}
    {{-- {!! $seo->fullHtml !!} --}}
</head>
```

---

## 7. Sitemap Integration

Sitemaps require the host application to configure the route, set the correct HTTP headers, and echo the XML string. The library does not emit HTTP headers itself.

```php
// Example in a basic framework or plain PHP
use Maatify\Seo\Shared\DTO\Sitemap\SitemapAlternateUrlDTO;
use Maatify\Seo\Shared\DTO\Sitemap\SitemapImageDTO;
use Maatify\Seo\Shared\DTO\Sitemap\SitemapVideoDTO;
use Maatify\Seo\Shared\DTO\Sitemap\SitemapNewsDTO;
use Maatify\Seo\Shared\DTO\Sitemap\SitemapUrlDTO;
use Maatify\Seo\Web\Sitemap\SitemapXmlStringRenderer;

// 1. Host app routing handles `/sitemap.xml`
$sitemapUrls = [
    new SitemapUrlDTO(
        loc: 'https://example.com/en',
        lastmod: '2023-10-01',
        changefreq: 'daily',
        priority: 1.0,
        alternates: [
            new SitemapAlternateUrlDTO('en', 'https://example.com/en'),
            new SitemapAlternateUrlDTO('es', 'https://example.com/es')
        ],
        images: [
            new SitemapImageDTO('https://example.com/image.jpg')
        ],
        videos: [
            new SitemapVideoDTO('https://example.com/thumbnail.jpg', 'Hero Video', 'A great video', 'https://example.com/video.mp4')
        ],
        news: [
            new SitemapNewsDTO('Example Daily', 'en', '2023-10-01', 'Breaking News')
        ]
    ),
    new SitemapUrlDTO('https://example.com/about', '2023-09-15', 'monthly', 0.8)
];

// 2. SEO library generates the XML string.
// If any alternates, images, videos, or news data are provided, the xmlns:xhtml, xmlns:image, xmlns:video, and xmlns:news namespaces are automatically added.
$renderer = new SitemapXmlStringRenderer();
$xmlString = $renderer->renderUrlSet($sitemapUrls);

// 3. Host app sets Content-Type and emits the response
header('Content-Type: application/xml; charset=utf-8');
echo $xmlString;
exit;
```

The Web URL renderer accepts either typed `SitemapUrlDTO` values or raw
associative URL entries. Both forms use the same top-level contract for `loc`,
`lastmod`, `changefreq`, and `priority`: `lastmod` accepts `YYYY-MM-DD`,
full-seconds date-times with `Z` or a numeric offset, and fractional-seconds
date-times with one or more digits plus `Z` or a numeric offset. Invalid
calendar dates and parser warnings are rejected, allowed frequency values are
enforced, and priority remains within `0.0..1.0`.
`SitemapNewsDTO::publicationDate` is intentionally only required to be
non-empty and is emitted as provided; it is not subject to the shared strict
date parser.

`SitemapImageDTO` continues to accept `title`, `caption`, `geoLocation`, and
`license` for public/output compatibility. Google-deprecates these fields, so
current integrations should prefer `loc` only; they are not current indexing
or search enhancements.

If you need to output a `sitemapindex`:

```php
use Maatify\Seo\Web\Sitemap\SitemapIndexXmlStringRenderer;
use Maatify\Seo\Web\Sitemap\DTO\SitemapIndexEntryDTO;

$indexEntries = [
    new SitemapIndexEntryDTO('https://example.com/sitemap-products.xml', '2023-10-01'),
    new SitemapIndexEntryDTO('https://example.com/sitemap-articles.xml', '2023-10-02')
];

$renderer = new SitemapIndexXmlStringRenderer();
$xmlString = $renderer->renderIndex($indexEntries);

header('Content-Type: application/xml; charset=utf-8');
echo $xmlString;
exit;
```

---

## 8. Robots.txt Integration

Similar to sitemaps, rendering `robots.txt` files requires the host application to configure the route and output the correct headers. The library's renderer strictly returns a string and does not handle HTTP requests or responses.

```php
use Maatify\Seo\Web\Robots\RobotsTxtRenderer;
use Maatify\Seo\Web\Robots\DTO\RobotsTxtDTO;
use Maatify\Seo\Web\Robots\DTO\RobotsRuleDTO;

// 1. Host app routing handles `/robots.txt`
$txt = new RobotsTxtDTO(
    rules: [
        new RobotsRuleDTO('*', ['/'], ['/admin'])
    ],
    sitemaps: ['https://example.com/sitemap.xml']
);

// 2. SEO library generates the plain text string
$renderer = new RobotsTxtRenderer();
$robotsString = $renderer->render($txt);

// 3. Host app sets Content-Type and emits the response
header('Content-Type: text/plain; charset=utf-8');
echo $robotsString;
exit;
```

---

## 9. SEO Validation Helpers Integration

The `SeoMetaValidator` can be utilized by the host application to run audits on pages without emitting output.

### Background Job or Test Integration
You can build a script to scrape your own site's metadata (or catch it during generation) and run the array through the validator:

```php
use Maatify\Seo\Web\Validation\SeoMetaValidator;
use Maatify\Seo\Web\Validation\SeoValidationPreset;

// Assume the host app generated the $meta array for a product page
$meta = [
    'title' => 'Awesome Product',
    'description' => 'A great product.',
    // Missing required fields for OG and Canonical, perhaps?
];

// Use a preset like standard() which requires canonical and has a 50 char min description length
$preset = SeoValidationPreset::standard();

$result = SeoMetaValidator::validate($meta, $preset['validationOptions']);

if ($result->hasWarnings) {
    // Log warnings to host monitoring tools
    // e.g. "Page /products/123 generated a description that is too short."
    HostLogger::warning('SEO warnings detected', ['issues' => $result->warnings]);
}

// Calculate an actionable score for the payload
$scoreDto = \Maatify\Seo\Web\Validation\SeoValidationScoreCalculator::score($result, $preset['scoreOptions']);
if (!$scoreDto->isHealthy) {
    HostLogger::error("Unhealthy SEO Score ({$scoreDto->score}/100) generated for product.", ['deductions' => $scoreDto->deductions]);
}
```

### Pre-Render Check Integration
Host frameworks (like Laravel or Slim middleware) can hook the payload to validate before rendering to a template. If warnings occur, you might flash a message to an admin interface or skip outputting broken SEO tags.

---

## 10. Dependency Injection Guidance

While the SEO library provides a `SeoBindings.php` file mapping interfaces to factories, it **does not require** a specific DI container like PHP-DI or Laravel's container.

- **Host App Registration:** Your host application can take the definitions in `SeoBindings.php` and register them into its own DI container.
- **Plain PHP Constructors:** Every class in the library can be instantiated via plain PHP `new` using standard constructor injection.
- Do not introduce framework-specific container interfaces (like `Illuminate\Contracts\Container\Container`) into the SEO library code.

---

## 11. Persistence Integration Guidance

The Host creates and configures PDO from its own configuration, then injects that object into the package repository. The package does not read `.env`, create the Host database connection, own Host configuration, or install/migrate its schema.

Apply the package-owned SQL asset before constructing the selected repository:

- Redirects: [`schema/maa_seo_redirects.sql`](../../schema/maa_seo_redirects.sql)
- SEO overrides: [`schema/maa_seo_overrides.sql`](../../schema/maa_seo_overrides.sql)
- Slug history: [`schema/maa_seo_slug_history.sql`](../../schema/maa_seo_slug_history.sql)

The representative redirect chain is `Host PDO -> PdoRedirectRepository -> RedirectCommandService / RedirectQueryService -> operation -> RedirectDTO`:

```php
use Maatify\Seo\Shared\Command\CreateRedirectCommand;
use Maatify\Seo\Shared\Infrastructure\Persistence\PdoRedirectRepository;
use Maatify\Seo\Shared\Service\RedirectCommandService;
use Maatify\Seo\Shared\Service\RedirectQueryService;
use PDO;

// The Host owns these connection values and PDO lifecycle.
$pdo = new PDO($hostDsn, $hostUser, $hostPassword, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$repository = new PdoRedirectRepository($pdo);
$commands = new RedirectCommandService($repository);
$queries = new RedirectQueryService($repository);

$id = $commands->create(new CreateRedirectCommand(
    entityType: 'article',
    languageId: 1,
    requestedSlug: '/old-redirect',
    targetEntityType: 'article',
    targetEntityId: 'post-10',
));
$redirect = $queries->getById($id);
```

`create()` returns the inserted integer ID. `getById()` returns a `RedirectDTO` with `id`, `entityType`, `languageId`, `requestedSlug`, `targetEntityType`, `targetEntityId`, `httpStatus`, `createdAt`, and nullable `deletedAt`. The maintained real-MySQL test [`tests/Integration/MySqlPersistenceIntegrationTest.php`](../../tests/Integration/MySqlPersistenceIntegrationTest.php) verifies a positive ID and the persisted values `article`, `1`, `/old-redirect`, `article`, `post-10`, and `301`; the database generates the exact ID and timestamp. **Result shape verified by the maintained MySQL integration test.** [`examples/pdo-persistence-wiring.php`](../../examples/pdo-persistence-wiring.php) runs this chain only when a dedicated local MySQL test database and the already-installed redirects table are available.

The same wiring pattern applies to the other stored domains:

```php
use Maatify\Seo\Shared\Infrastructure\Persistence\PdoSeoOverrideRepository;
use Maatify\Seo\Shared\Infrastructure\Persistence\PdoSlugHistoryRepository;
use Maatify\Seo\Shared\Service\SeoOverrideCommandService;
use Maatify\Seo\Shared\Service\SeoOverrideQueryService;
use Maatify\Seo\Shared\Service\SlugHistoryCommandService;
use Maatify\Seo\Shared\Service\SlugHistoryQueryService;

$overrideRepository = new PdoSeoOverrideRepository($pdo);
$overrideCommands = new SeoOverrideCommandService($overrideRepository);
$overrideQueries = new SeoOverrideQueryService($overrideRepository);

$slugHistoryRepository = new PdoSlugHistoryRepository($pdo);
$slugHistoryCommands = new SlugHistoryCommandService($slugHistoryRepository);
$slugHistoryQueries = new SlugHistoryQueryService($slugHistoryRepository);
```

The override repository's `create()` returns an ID and its query services return `SeoOverrideDTO`; slug-history `create()` returns an ID and its query services return `SlugHistoryDTO` or lists of those DTOs. These repositories do not begin, commit, or roll back transactions; if the Host needs a transaction, it controls the PDO transaction boundary. A missing row returned by a repository becomes `SeoNotFoundException` in the corresponding query service. A SQLSTATE class `23` integrity failure on repository `create()` is translated to `SeoCodeAlreadyExistsException`; other PDO failures, including a missing table or unavailable database, propagate as `PDOException`. Repository update/delete calls return `bool`; command services expose `void` and throw `SeoNotFoundException` when the repository reports no affected row. Across redirect, override, and slug-history PDO repositories, soft delete sets `deleted_at` only when it is currently null, so a missing or already soft-deleted row returns `false` and the command service throws `SeoNotFoundException`. `hardDelete()` physically deletes by ID without checking `deleted_at`, so it succeeds for either active or soft-deleted rows; a missing or already hard-deleted row returns `false` and becomes `SeoNotFoundException`. `getById()` can still read a soft-deleted row in all three families. Redirect and override `findByEntity()` lists accept `includeDeleted: true`; slug-history `findActiveBySlug()` and `findActiveForEntity()` expose only active records, and the latter has no `includeDeleted` option. Redirect and override updates require `deleted_at IS NULL`; slug history has no update operation.

The package does not read `.env` files, config files, environment variables, or framework configuration. Do not pass Laravel `Config::get()` or Symfony parameter bags into the package domain layer; resolve those in the Host and pass the configured PDO object.

---

## 12. Current Page and Domain Integration

The package offers both high-level composition helpers and lower-level
builders. Choose the smallest integration surface that fits the Host page:

- Use `SeoPagePresetFactory` or a domain preset factory for a common page shape
  and a ready `SeoPagePresetOutputDTO` containing metadata, social tags,
  schemas, and head HTML.
- Use `SeoPageRenderService` when the Host wants a service to combine
  Host-provided page defaults, active overrides, and schema inputs into a
  `SeoPagePayloadDTO`.
- Use builders and renderers directly when the Host needs its own composition
  or wants to place sections separately.

Presets do not own Host routes, page selection, product lifecycle, or
application data. The render service does not create controllers or HTTP
responses. `SeoHeadHtmlRenderer` renders a payload into an HTML string or DTO;
the Host places and delivers that output.

### 12.1 Metadata and Host URL generation

The Host supplies entity identity and fallback title/description to
`MetaGeneratorService`. The service queries the active SEO override through
`SeoOverrideQueryService`, keeps defaults when no override is found, and returns
a `MetaTagsDTO`. The Host may configure the service with an implementation of
`HostUrlGeneratorInterface`; that adapter owns URL construction from the Host's
route and entity rules. A non-blank explicit canonical in the command takes
precedence over the optional generated URL, and no URL generator means the
canonical may remain `null`. See the [MetaGeneratorService contract](../SEO/library/META_GENERATOR_SERVICE_CONTRACT.md)
for the exact trimming, fallback, exception, and output rules.

```php
use Maatify\Seo\Shared\Command\GenerateMetaTagsCommand;

$metaTags = $metaGeneratorService->generate(new GenerateMetaTagsCommand(
    entityType: 'product',
    entityId: (string) $product->id,
    languageId: $languageId,
    defaultTitle: $product->name,
    defaultDescription: $product->description,
    slug: $product->slug,
));
```

`MetaGeneratorService` does not own the Host's route definitions, URL policy,
entity lookup, or lifecycle. For persisted overrides, use the package's
`SeoOverrideQueryService` and configured repository; the Host supplies its PDO
connection, while the package provides PDO adapters and schemas for its own
tables as described in [Persistence Integration](#11-persistence-integration-guidance).
The runnable [override/default example](../../examples/seo-override-meta-generation.php)
prints the selected title, description, canonical, and serialized result.

### 12.2 Presets and page rendering

For a common product page, a Host can use a domain preset and render its output:

```php
use Maatify\Seo\Web\Page\EcommerceSeoPresetFactory;

$preset = EcommerceSeoPresetFactory::productDetail(
    title: $product->name,
    description: $product->description,
    product: [
        'name' => $product->name,
        'sku' => $product->sku,
        'price' => $product->price,
        'currency' => $product->currency,
    ],
    options: ['canonicalUrl' => $canonicalUrl],
);

$headHtml = $preset->html;
```

Other factories cover generic, content, and local-business page compositions.
The preset result exposes `metaTags`, `canonicalUrl`, `robots`, `socialTags`,
`socialHtml`, `schemas`, and `html` for Host use.
See the executed [preset example](../../examples/seo-page-presets.php) for the
serialized fields and the [page-render example](../../examples/seo-page-render.php)
for `SeoPagePayloadDTO` plus rendered head output.

When a Host instead wants the service orchestration path, it passes a
`RenderSeoPageCommand` to `SeoPageRenderService::render()`. The command carries
Host identifiers/defaults plus optional breadcrumbs.
`RenderSeoPageCommand::$schemas` accepts values that implement
`JsonSerializable`. A `JsonLdBuilderInterface` builder does not implement
`JsonSerializable` and cannot be passed directly; materialize it with `toArray()`
and wrap the result in a `JsonLdSchemaDTO` first. The returned
`SeoPagePayloadDTO` carries metadata and generated schema DTOs;
`SeoHeadHtmlRenderer::renderPayload()` can produce the head HTML. Optional
redirect resolution returns a `RedirectDecisionDTO` for the Host to apply. It
remains the Host's responsibility to map that domain decision to an HTTP response.

```php
use Maatify\Seo\Shared\DTO\Schema\JsonLdSchemaDTO;
use Maatify\Seo\Web\JsonLd\Builder\ProductJsonLdBuilder;
use Maatify\Seo\Web\Render\SeoHeadHtmlRenderer;
use Maatify\Seo\Web\SeoRender\Command\RenderSeoPageCommand;

$productSchemaBuilder = (new ProductJsonLdBuilder())->setName($product->name);
$productSchema = new JsonLdSchemaDTO($productSchemaBuilder->toArray());

$payload = $seoPageRenderService->render(new RenderSeoPageCommand(
    entityType: 'product',
    entityId: (string) $product->id,
    languageId: $languageId,
    defaultTitle: $product->name,
    defaultDescription: $product->description,
    slug: $product->slug,
    schemas: [$productSchema],
));

$headHtml = (new SeoHeadHtmlRenderer())->renderPayload($payload);
```

### 12.3 Canonical and hreflang integration

Use `CanonicalUrlBuilder` to assemble a canonical URL from Host-approved base,
path, and query values, or use the metadata service's canonical input and
Host URL generator boundary. `HreflangLinkBuilder` composes alternate links and
`HreflangLinkRenderer` renders link tags. These APIs generate values; profile
validation is a separate step.

`GoogleCanonicalValidator` returns a companion diagnostic result for the
canonical candidate. `GoogleHreflangClusterValidator` accepts a cluster input
and checks its defined lexical and relationship boundaries, including usable
URLs, self-references, reciprocity, and consistent alternate sets. Neither
builder output nor these checks prove language-tag membership in an ISO
registry, and the Host chooses when and where to run validation.
The [canonical/robots example](../../examples/meta-robots-canonical.php) and
[hreflang example](../../examples/hreflang-generation.php) show emitted values.

### 12.4 Admin-domain integration

The Admin services are constructed around the Shared services, which use the same repository adapter described in [Persistence Integration](#11-persistence-integration-guidance). For redirects, the complete dependency chain is:

```php
use Maatify\Seo\Admin\Redirect\Command\CreateAdminRedirectCommand;
use Maatify\Seo\Admin\Redirect\Service\AdminRedirectCommandService;
use Maatify\Seo\Admin\Redirect\Service\AdminRedirectQueryService;
use Maatify\Seo\Shared\Infrastructure\Persistence\PdoRedirectRepository;
use Maatify\Seo\Shared\Service\RedirectCommandService;
use Maatify\Seo\Shared\Service\RedirectQueryService;

$redirectRepository = new PdoRedirectRepository($pdo);
$adminRedirectCommands = new AdminRedirectCommandService(new RedirectCommandService($redirectRepository));
$adminRedirectQueries = new AdminRedirectQueryService(new RedirectQueryService($redirectRepository));

$redirectId = $adminRedirectCommands->create(new CreateAdminRedirectCommand(
    entityType: 'article',
    languageId: 1,
    requestedSlug: '/old-article',
    targetEntityType: 'article',
    targetEntityId: 'post-10',
)); // int
$redirect = $adminRedirectQueries->getById($redirectId); // AdminRedirectDTO
```

`update()` returns `void`; query it again to obtain the updated `AdminRedirectDTO`. It cannot update a soft-deleted redirect because the repository update matches only rows with `deleted_at IS NULL`, so the command service throws `SeoNotFoundException`. `listByEntity()` returns `list<AdminRedirectDTO>`. `softDelete()` and `hardDelete()` return `void`: `softDelete()` throws `SeoNotFoundException` for a missing or already soft-deleted redirect; `hardDelete()` succeeds for an active or soft-deleted redirect and throws only when the row is missing or was already hard-deleted. The Admin redirect command/query example demonstrates `softDelete()`, querying the deleted DTO, successful `hardDelete()`, and `SeoNotFoundException` on the subsequent `getById()`. `AdminSeoOverrideCommandService` and `AdminSeoOverrideQueryService` follow the same pattern around `SeoOverrideCommandService` and `SeoOverrideQueryService`, returning an integer from `create()`, `void` from `update()`, and `AdminSeoOverrideDTO` / lists from queries. `AdminSlugHistoryCommandService` and `AdminSlugHistoryQueryService` return an integer from `record()` and `AdminSlugHistoryDTO` / lists from queries; there is no update operation. Other current Admin helpers include `SerpPreviewFactory`, `SocialPreviewFactory`, `SeoMetadataImporter`, and `SeoMetadataExporter`.

The other Admin service constructors wrap their corresponding Shared services from the persistence wiring above:

```php
use Maatify\Seo\Admin\SeoOverride\Service\AdminSeoOverrideCommandService;
use Maatify\Seo\Admin\SeoOverride\Service\AdminSeoOverrideQueryService;
use Maatify\Seo\Admin\SlugHistory\Service\AdminSlugHistoryCommandService;
use Maatify\Seo\Admin\SlugHistory\Service\AdminSlugHistoryQueryService;

$adminOverrideCommands = new AdminSeoOverrideCommandService($overrideCommands);
$adminOverrideQueries = new AdminSeoOverrideQueryService($overrideQueries);
$adminSlugHistoryCommands = new AdminSlugHistoryCommandService($slugHistoryCommands);
$adminSlugHistoryQueries = new AdminSlugHistoryQueryService($slugHistoryQueries);
```

`AdminSeoOverrideQueryService` manages stored override records. It does not generate page metadata: `MetaGeneratorService` later consumes the active override through `SeoOverrideQueryService` (the Shared lower-level contract) when the Host requests metadata generation. Slug-history recording does not update the Host entity, initiate an HTTP redirect, or define its lifecycle; those decisions remain in the Host. Redirect services store redirect intent and status as domain data, and the Host emits the corresponding HTTP response. The services return DTOs, identifiers, validation errors, dry-run counts, or domain decisions for the Host to use. Preview factories return preview DTOs and missing-field warnings rather than an Admin screen or provider-rendered search result.

The Host owns Admin UI, routes/controllers, authentication, authorization,
permissions, and application workflow. It decides who may invoke an operation,
how to present the DTOs, and when to persist or apply the result. Import/export
can work with the package's DTOs and configured repositories; they do not
provide bulk Admin workflows or Host-specific entity mapping automatically. A
dry-run `SeoMetadataImporter::importArray($payload, true)` validates and counts
importable rows without writing them; a non-dry-run import requires the
relevant repositories. Missing repositories are counted as skipped, while
repository failures are returned in the result's failure count and errors.
Maintained test `Batch2AdminPreviewsMigrationsTest` asserts the dry-run count
and flag. Executed output is available in the [Admin CRUD examples](../../examples/redirect-slug-history.php) and
[override example](../../examples/seo-override-meta-generation.php),
[preview example](../../examples/admin-previews.php), and
[import/export example](../../examples/import-export.php).

### 12.5 Search Console and Merchant Center

For Search Console, compose `SearchConsoleInspectionService` with
`SearchConsoleResponseMapper` and a Host implementation of
`SearchConsoleTransportInterface`. The package validates request DTOs, invokes
the transport, and returns mapped provider evidence in typed result DTOs. The
Host supplies HTTP and OAuth handling, credentials, and decoded transport
responses. Search Console results remain separate from generic SEO validation,
scores, and reports; an absent rich-results result remains absent rather than
being treated as a pass.

For Merchant Center, use `MerchantCenterDiagnosticsService`,
`MerchantCenterResponseMapper`, and a Host implementation of
`MerchantCenterTransportInterface`. The service returns typed product or
aggregate diagnostic DTOs. The Host owns HTTP, OAuth, credentials, decoded
responses, pagination using the returned page token, and scheduling. The
package maps provider evidence; it does not synthesize a separate overall
eligibility verdict or automatically remediate provider issues.

Both provider families distinguish invalid request input from malformed
responses and transport failures. Invalid requests use the package's safe
validation exception family; malformed responses and transport errors use its
system-error family. A transport exception may expose the provider response
code through its `httpStatus` property, while the shared application
`getHttpStatus()` remains the package's system-error status. Do not convert a
provider HTTP code such as 403 directly into an application HTTP 403; map the
package exception according to the Host's application error policy. See
[`SEO_PACKAGE_REFERENCE.md`](../../SEO_PACKAGE_REFERENCE.md) for the exact
exception taxonomy.
The [Search Console fixture](../../examples/search-console-response-mapping.php)
and [Merchant Center fixture](../../examples/merchant-center-diagnostics.php)
show sample payloads and mapped package DTOs without making network calls.

## 13. Error Handling

The package returns validation diagnostics for content findings such as missing or short metadata. Invalid command/configuration values throw `SeoInvalidArgumentException`; missing query results throw `SeoNotFoundException`; an integrity conflict from a PDO create is reported as `SeoCodeAlreadyExistsException`. Those outcomes are different: ordinary SEO warnings do not become exceptions. The package does not send an HTTP response.

For example, a Host can catch a missing redirect separately and choose its own API/UI outcome:

```php
use Maatify\Seo\Exception\SeoNotFoundException;

try {
    $redirect = $adminRedirectQueries->getById(17);
} catch (SeoNotFoundException $exception) {
    error_log($exception::class . ': ' . $exception->getMessage());
    $hostOutcome = ['httpStatus' => 404, 'code' => 'seo_redirect_not_found'];
}
```

Invalid command input and a duplicate stored identity have distinct catches. The duplicate case below matches the maintained MySQL integration test, which first inserts the same `(entity_type, language_id, requested_slug)` and then asserts the second create throws `SeoCodeAlreadyExistsException`:

```php
use Maatify\Seo\Exception\SeoCodeAlreadyExistsException;
use Maatify\Seo\Exception\SeoInvalidArgumentException;
use Maatify\Seo\Shared\Command\CreateRedirectCommand;

try {
    $commands->create(new CreateRedirectCommand('article', 1, '/old', null, null, 302));
} catch (SeoInvalidArgumentException $exception) {
    error_log($exception::class . ': ' . $exception->getMessage());
    $hostOutcome = ['httpStatus' => 400, 'code' => 'invalid_seo_input'];
}

try {
    // This key already exists in the maintained Integration fixture.
    $commands->create(new CreateRedirectCommand('article', 1, '/old-redirect', 'article', 'post-duplicate'));
} catch (SeoCodeAlreadyExistsException $exception) {
    error_log($exception::class . ': ' . $exception->getMessage());
    $hostOutcome = ['httpStatus' => 409, 'code' => 'seo_redirect_conflict'];
}
```

`SeoConflictException` is also part of the package's public exception taxonomy and its classification is tested, but the inspected current redirect/override/slug operations do not throw that generic class. The PDO duplicate path specifically throws `SeoCodeAlreadyExistsException`; catch the concrete type the operation documents instead of assuming all conflicts share one concrete class.

The inherited `MaatifyException` API on the concrete SEO exception classes exposes `getErrorCode()`, `getCategory()`, `getHttpStatus()`, `isSafe()`, `isRetryable()`, and `getMeta()`. The `SeoExceptionInterface` marker itself only extends `Throwable`; code typed only to that marker should not assume those additional methods. The maintained `ExceptionArchitectureTest.php` verifies package classifications including Not Found `404`, invalid argument `400`, and conflict `409`. Treat those as package classifications available to Host policy, not a mandate that every operation map to the same response in every application. Log internal failures as appropriate and choose the user-facing response/message in the Host; do not expose internal exception messages automatically.

Provider failures have a separate status boundary. Search Console and Merchant Center invalid request exceptions represent package request validation (classification `400`, before a transport call); malformed provider response exceptions represent a system/mapping failure (classification `500`); transport exceptions represent a failed provider request and expose the provider's HTTP status in their public `httpStatus` property. For a Search Console `403`, `$exception->httpStatus` is `403` while the inherited `$exception->getHttpStatus()` is the package system classification `500`. A provider HTTP 403 is evidence about the provider request. It does not automatically become the Host application's HTTP 403. The Host chooses its own response policy; the executable [`provider-failure-handling.php`](../../examples/provider-failure-handling.php) uses a local transport fixture, displays provider `403` / package `500`, and selects Host `502` without contacting Google.

The library should never call `http_response_code()` or throw HTTP-specific framework exceptions (like `Symfony\Component\HttpKernel\Exception\NotFoundHttpException`).

---

## 14. Common Integration Mistakes

To maintain library integrity, ensure you **do not**:

*   **Add controllers or routes to the library.** Routing belongs to the host application.
*   **Return PSR-7, Laravel, or Symfony responses from library classes.** Return strings or DTOs only.
*   **Call framework helpers inside library code.** (e.g., `request()`, `route()`, `env()`).
*   **Output headers from renderers.** Do not use `header('Content-Type: ...')` inside the SEO library.
*   **Commit `composer.lock`.** This is a reusable library; dependency resolution happens at the host application level.
*   **Add framework packages as dependencies.** (e.g., `illuminate/support`, `symfony/http-foundation`). Keep dependencies generic.

### Comprehensive SEO Reporting Integration

For a unified view, the host application can use the `SeoValidationReportBuilder` to combine the `SeoMetaValidator` and `SeoValidationScoreCalculator` into a single, comprehensive `SeoValidationReportDTO`. If you need to validate multiple items at once (e.g. for a bulk audit or CI report), you can use `SeoValidationBatchReportBuilder::build($items, $validationOptions = [], $scoreOptions = [], $sharedContext = [])`. This builder ensures existing validation and scoring behaviors remain completely unchanged. You can also use the `SeoValidationBatchReportExporter` to export these batch reports to arrays, JSON, summary arrays, and Markdown for external dashboards, QA tools, and CI environments.

```php
use Maatify\Seo\Web\Validation\SeoValidationReportBuilder;
use Maatify\Seo\Web\Validation\SeoValidationPreset;

$preset = SeoValidationPreset::standard();

$report = SeoValidationReportBuilder::build(
    meta: $pageMetaData,
    validationOptions: $preset['validationOptions'],
    scoreOptions: $preset['scoreOptions'],
    context: ['url' => 'https://example.com/blog/hello-world', 'entityType' => 'blog']
);

// Send the report array to a dashboard, logging service, or API response
return new JsonResponse($report->toArray());
```

The report builder is completely framework-neutral. It simply returns a DTO and has zero side effects: it does not mutate your original metadata, change internal validator logic, or emit any HTTP headers, routes, controllers, or responses.

If you are exporting the report to external dashboards, QA tools, or CI environments, the `SeoValidationReportExporter` can provide the same data in JSON, compact summary arrays, or Markdown:

```php
use Maatify\Seo\Web\Validation\SeoValidationReportExporter;

// For a dashboard API
return new JsonResponse(SeoValidationReportExporter::toSummaryArray($report));

// For a CLI tool or GitHub Action output
echo SeoValidationReportExporter::toMarkdown($report);
```
