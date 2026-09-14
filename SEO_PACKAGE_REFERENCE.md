# Maatify SEO — Package Reference

This document describes the current repository/package contract for Maatify SEO. It is derived from the current Composer metadata, runtime source, shipped schemas, maintained tests, and CI configuration. It is the single canonical human-readable package reference; it does not establish external Packagist publication, a Stable release, or release readiness.

## Package identity and requirements

- Composer package: **maatify/php-seo**
- PHP: **^8.2**
- Runtime requirements: **ext-json**, **ext-pdo**, **ext-xmlwriter**, and **maatify/exceptions ^1.0**
- Optional integration: **spatie/schema-org**, suggested for adapting Spatie schema objects
- PSR-4 namespace prefix `Maatify\Seo\` maps to `src/`

The package is standalone and framework-neutral. It has no Laravel, Symfony, Slim, or PHP-DI dependency. It computes SEO values and returns DTOs, arrays, HTML/XML strings, or typed results. It does not own host controllers, routes, HTTP requests or responses, templates, credentials, or application data.

## Architecture boundaries

The current runtime is organized into these layers:

- **src/Bootstrap/** — framework-neutral service binding definitions.
- **src/Exception/** — SEO exception interface, domain exception families, and the package's numeric SEO error-code constants.
- **src/Shared/** — host contracts, commands, DTOs, domain services, and the package-owned PDO persistence adapters.
- **src/Admin/** — host-facing admin commands, query services, DTOs, previews, and import/export helpers.
- **src/Web/** — the framework-neutral host-facing SEO consumption layer: builders, validators, renderers, page orchestration, and external-provider contracts.

**SeoBindings** supplies reusable service maps for shared, admin, web, or combined bindings. The host supplies a configured PDO instance and may supply HostUrlGeneratorInterface; a host container can adapt the maps to its own resolution mechanism. This does not couple the package to a specific container.

Services, builders, and renderers return values for the host to use. The host decides how to route requests, emit HTTP responses, render templates, deliver files, and integrate its own entities and application lifecycle.

## Persistence contract

The host supplies a configured **PDO**. The package ships concrete **PdoRedirectRepository** and **PdoSeoOverrideRepository** implementations in **Maatify\Seo\Shared\Infrastructure\Persistence**, together with package-owned SQL assets:

| Schema asset | Package-owned table |
| --- | --- |
| [schema/maa_seo_redirects.sql](schema/maa_seo_redirects.sql) | maa_seo_redirects |
| [schema/maa_seo_overrides.sql](schema/maa_seo_overrides.sql) | maa_seo_overrides |

These schemas use MySQL semantics, InnoDB, utf8mb4, and utf8mb4_unicode_ci. The repositories operate on these package-owned tables; they do not require Host foreign keys or Host-table joins. Host data remains Host-owned, and the Host does not need to implement a parallel ORM persistence layer. The package uses deleted_at for soft-delete state where applicable; command services expose soft-delete and explicit hard-delete operations.

The SQL files are shipped assets for deployment/integration. The package does not claim that a schema is installed automatically. CI uses mysql:8.4.11 as a reproducibility fixture; this is not the package's minimum supported MySQL product version.

See the [integration guide](docs/guides/INTEGRATION_GUIDE.md) for wiring and schema setup.

## Consumer-facing runtime API inventory

The inventory below is organized by capability and namespace. It lists intended Host integration and consumption types from the current source tree, not every autoloadable implementation detail.

### Bootstrap and exceptions

- **Maatify\Seo\Bootstrap**: SeoBindings.
- **Maatify\Seo\Exception**: SeoExceptionInterface, SeoInvalidArgumentException, SeoNotFoundException, SeoConflictException, SeoCodeAlreadyExistsException, SeoErrorCode.

SeoExceptionInterface extends Throwable. Package exceptions use maatify/exceptions while preserving the existing numeric SEO PHP exception codes through normal getCode() behavior where a numeric SEO code is defined.

The core taxonomy is:

| SEO family | Shared code | Category | Application HTTP status | Safe |
| --- | --- | --- | --- | --- |
| Invalid argument | INVALID_ARGUMENT | VALIDATION | 400 | yes |
| Not found | RESOURCE_NOT_FOUND | NOT_FOUND | 404 | yes |
| Conflict / code already exists | CONFLICT | CONFLICT | 409 | yes |

Search Console and Merchant Center exceptions have separate catchable family bases:

- **SearchConsoleException**
  - SearchConsoleInvalidRequestException
  - SearchConsoleMalformedResponseException
  - SearchConsoleTransportException
- **MerchantCenterException**
  - MerchantCenterInvalidRequestException
  - MerchantCenterMalformedResponseException
  - MerchantCenterTransportException

Provider invalid requests map to **INVALID_ARGUMENT / VALIDATION / 400 / safe**. Malformed provider responses and transport failures map to **MAATIFY_ERROR / SYSTEM / 500 / unsafe**. Each transport exception may also expose its provider response code through the readonly httpStatus property. That property is distinct from the shared application getHttpStatus(): a provider HTTP 403 does not by itself become an application HTTP 403.

**JsonLdBuildException** is a SYSTEM / MAATIFY_ERROR / 500 package exception.

### Shared contracts, commands, and DTOs

**Maatify\Seo\Shared\Contract** provides HostEntityProviderInterface, HostSearchContextInterface, HostUrlGeneratorInterface, RedirectRepositoryInterface, and SeoOverrideRepositoryInterface. The repository interfaces are implemented by the package's PDO repositories above.

Commands under **Maatify\Seo\Shared\Command**:

- Base namespace: CreateRedirectCommand, UpdateRedirectCommand, GenerateMetaTagsCommand.
- Redirect: ResolveRedirectCommand.
- SEO override: CreateSeoOverrideCommand, UpdateSeoOverrideCommand.

DTOs under **Maatify\Seo\Shared\DTO**:

- Base namespace: MetaTagsDTO, RedirectDTO.
- Redirect: RedirectDecisionDTO.
- SEO override: SeoOverrideDTO.
- Schema: BreadcrumbItemDTO, BreadcrumbListDTO, BreadcrumbSchemaDTO, GenericSchemaDTO, JsonLdSchemaDTO, OrganizationSchemaDTO, ProductSchemaDTO, WebPageSchemaDTO, WebsiteSchemaDTO.
- Sitemap: SitemapAlternateUrlDTO, SitemapGenerationResultDTO, SitemapImageDTO, SitemapIndexEntryDTO, SitemapNewsDTO, SitemapUrlDTO, SitemapVideoDTO.

### Shared services and persistence implementations

**Maatify\Seo\Shared\Service** provides MetaGeneratorService, RedirectCommandService, RedirectManagerService, RedirectQueryService, SchemaGeneratorService, SeoOverrideCommandService, SeoOverrideQueryService, and SitemapGeneratorService.

The command and query services operate through the repository contracts. The package's PDO repositories provide the concrete persistence implementation. Redirect management independently resolves stored redirect decisions and, when configured with command services, records redirect or gone outcomes; URL generation remains a Host integration. SEO overrides are queried by entity and language, and missing active overrides are reported as not found so the metadata service can retain defaults.

### Admin capabilities

- **Maatify\Seo\Admin\DTO**: SeoMetadataExportDTO, SeoMetadataImportResultDTO, SerpPreviewDTO, SocialPreviewDTO.
- **Maatify\Seo\Admin\Export**: SeoMetadataExporter.
- **Maatify\Seo\Admin\Import**: SeoMetadataImporter.
- **Maatify\Seo\Admin\Preview**: SerpPreviewFactory, SocialPreviewFactory.
- **Maatify\Seo\Admin\Redirect\Command**: CreateAdminRedirectCommand, UpdateAdminRedirectCommand.
- **Maatify\Seo\Admin\Redirect\DTO**: AdminRedirectDTO.
- **Maatify\Seo\Admin\Redirect\Service**: AdminRedirectCommandService, AdminRedirectQueryService.
- **Maatify\Seo\Admin\SeoOverride\Command**: CreateSeoOverrideCommand, UpdateSeoOverrideCommand.
- **Maatify\Seo\Admin\SeoOverride\DTO**: AdminSeoOverrideDTO.
- **Maatify\Seo\Admin\SeoOverride\Service**: AdminSeoOverrideCommandService, AdminSeoOverrideQueryService.

These types provide admin-oriented domain operations and preview/import/export results. They do not supply an admin UI, controller, or route.

### Web rendering and page composition

- **Maatify\Seo\Web\Builder**: FluentSeoBuilder.
- **Maatify\Seo\Web\DTO**: SeoHeadHtmlDTO.
- **Maatify\Seo\Web\Page**: SeoPagePresetFactory, SeoPagePresetOutputDTO, EcommerceSeoPresetFactory, ContentSeoPresetFactory, LocalBusinessSeoPresetFactory.
- **Maatify\Seo\Web\SeoRender\Command**: RenderSeoPageCommand.
- **Maatify\Seo\Web\SeoRender\DTO**: SeoPagePayloadDTO.
- **Maatify\Seo\Web\SeoRender\Service**: SeoPageRenderService.

### Canonical URLs and hreflang

- **Maatify\Seo\Web\Indexing**: CanonicalUrlBuilder.
- **Maatify\Seo\Web\Hreflang**: HreflangLinkBuilder, HreflangLinkDTO, HreflangLinkRenderer.
- **Maatify\Seo\Web\Validation\Profile**: GoogleCanonicalValidator, GoogleHreflangClusterValidator.

Lexical shape checks or normalization do not prove membership in an ISO registry. The current Hreflang boundary does not imply ISO 639 registry membership; that remains tied to a separately versioned standards-data contract.

### Social metadata

- **Maatify\Seo\Web\Social**: OpenGraphBuilder, TwitterCardBuilder, SocialPreviewBuilder, SocialMetaTag, SocialMetaCollection, SocialMetaRenderOutput, SocialImage, SocialImageFactory.
- **Maatify\Seo\Web\Render**: OpenGraphHtmlRenderer, TwitterCardHtmlRenderer, MetaTagsHtmlRenderer, SeoHeadHtmlRenderer, JsonLdScriptRenderer.

The package generates Open Graph and Twitter Card compatibility output. Open Graph behavior has been source-reviewed where documented. Twitter/X provider conformance was not source-verified; the presence of builders or renderers does not establish provider conformance.

### Robots and sitemaps

- **Maatify\Seo\Web\Robots**: MetaRobotsBuilder, RobotsTxtRenderer.
- **Maatify\Seo\Web\Robots\DTO**: RobotsRuleDTO, RobotsTxtDTO.
- **Maatify\Seo\Web\Sitemap**: SitemapIndexXmlStringRenderer, SitemapXmlStringRenderer.
- **Maatify\Seo\Web\Sitemap\DTO**: SitemapIndexEntryDTO.

Shared sitemap DTOs and SitemapGeneratorService are listed under Shared. Rendering produces strings; the Host chooses delivery and response behavior. Sitemap protocol and named Google profile validators are separate from generic generation.

### JSON-LD and Schema.org-oriented generation

The **Maatify\Seo\Web\JsonLd\Builder** namespace contains JsonLdBuilderInterface, AbstractJsonLdBuilder, and the concrete builders:

AboutPageJsonLdBuilder, AggregateOfferJsonLdBuilder, AggregateRatingJsonLdBuilder, ArticleJsonLdBuilder, AudioObjectJsonLdBuilder, BookJsonLdBuilder, BreadcrumbJsonLdBuilder, CollectionPageJsonLdBuilder, ContactPageJsonLdBuilder, CourseJsonLdBuilder, DatasetJsonLdBuilder, EventJsonLdBuilder, FAQPageJsonLdBuilder, HowToJsonLdBuilder, ImageObjectJsonLdBuilder, ItemListJsonLdBuilder, JobPostingJsonLdBuilder, LocalBusinessJsonLdBuilder, MovieJsonLdBuilder, MusicAlbumJsonLdBuilder, OfferJsonLdBuilder, OrganizationJsonLdBuilder, PersonJsonLdBuilder, ProductGroupJsonLdBuilder, ProductJsonLdBuilder, ProfilePageJsonLdBuilder, RecipeJsonLdBuilder, ReviewJsonLdBuilder, SearchResultsPageJsonLdBuilder, ServiceJsonLdBuilder, SoftwareApplicationJsonLdBuilder, VideoObjectJsonLdBuilder, WebPageJsonLdBuilder, and WebSiteJsonLdBuilder.

The same namespace provides JsonLdBuildException. **Maatify\Seo\Web\Schema** provides SpatieSchemaAdapter; the Spatie dependency is optional and only needed when using that integration.

Builders construct Schema.org-oriented output. Generation does not establish complete Schema.org validation or Google Rich Results or Merchant eligibility.

### SEO validation

- **Maatify\Seo\Web\Validation**: SeoMetaValidator, SeoValidationBatchReportBuilder, SeoValidationBatchReportExporter, SeoValidationPreset, SeoValidationReportBuilder, SeoValidationReportExporter, SeoValidationScoreCalculator.
- **Maatify\Seo\Web\Validation\DTO**: SeoCompanionDiagnosticDTO, SeoCompanionValidationResultDTO, SeoDiagnosticTargetDTO, SeoValidationBatchReportDTO, SeoValidationContextDTO, SeoValidationIssueDTO, SeoValidationReportDTO, SeoValidationResultDTO, SeoValidationScoreDTO.
- **Maatify\Seo\Web\Validation\Input**: RobotsMetaValidationInputDTO, RobotsTxtValidationInputDTO.
- **Maatify\Seo\Web\Validation\Input\Hreflang**: HreflangValidationClusterDTO, HreflangValidationLinkDTO, HreflangValidationPageDTO.
- **Maatify\Seo\Web\Validation\Input\Sitemap**: SitemapImageValidationInputDTO, SitemapIndexEntryValidationInputDTO, SitemapNewsValidationInputDTO, SitemapUrlValidationInputDTO, SitemapValidationDocumentDTO, SitemapValidationLocationDTO, SitemapVideoValidationInputDTO.
- **Maatify\Seo\Web\Validation\JsonLd**: JsonLdSemanticValidator.
- **Maatify\Seo\Web\Validation\Profile**: GoogleCanonicalValidator, GoogleHreflangClusterValidator, GoogleImageSitemapValidator, GoogleNewsSitemapValidator, GoogleRobotsMetaValidator, GoogleRobotsTxtValidator, GoogleSitemapValidator, GoogleVideoSitemapValidator, OpenGraphProtocolValidator, Rfc9309RobotsValidator, SitemapProtocolValidator.

JsonLdSemanticValidator and SeoMetaValidator use structural checks and scoped structural and property-range semantic validation for Product, Offer, AggregateOffer, and ProductGroup. This is not complete Schema.org semantic or lexical proof. In the current URL, Date, DateTime, ItemAvailability, and OfferItemCondition property-range boundaries, non-empty strings remain accepted representations; URL/date grammar, enumeration membership, provider-vocabulary lookup, reachability, and DNS/network checks are not thereby performed. Unknown extension properties and valid out-of-scope types or relationship targets are not rejected solely for being outside the fixed deep-validation catalog.

This layer does not claim complete Schema.org coverage, required-property completeness, or provider eligibility. Google required/recommended properties are not a provider-eligibility profile in this validator. Merchant eligibility remains a separate capability, and a complete provider capability matrix belongs to a separately approved, date-stamped contract.

### External-provider capabilities

**Search Console** types live under **Maatify\Seo\Web\Indexing\SearchConsole**:

- Service and transport boundary: SearchConsoleInspectionService, SearchConsoleTransportInterface, SearchConsoleResponseMapper.
- Request/transport/result DTOs: SearchConsoleInspectionRequestDTO, SearchConsoleTransportResponseDTO, SearchConsoleInspectionResultDTO, SearchConsoleIndexStatusResultDTO, SearchConsoleRichResultsResultDTO, SearchConsoleRichResultItemDTO, SearchConsoleRichResultIssueDTO, SearchConsoleDetectedItemDTO.
- Exceptions: SearchConsoleException and its invalid-request, malformed-response, and transport subclasses.

This optional boundary maps URL Inspection API results. It is not a live page fetch or a replacement for the Rich Results Test. The Host supplies HTTP and OAuth behavior and credentials; the package transport interface does not make network calls or provide retries, persistence, queues, or scheduling. Missing rich-results data remains null rather than becoming a passing verdict, and unknown provider values are preserved.

**Merchant Center** types live under **Maatify\Seo\Web\MerchantCenter**:

- Service and transport boundary: MerchantCenterDiagnosticsService, MerchantCenterTransportInterface, MerchantCenterResponseMapper.
- Request/transport/result DTOs: MerchantCenterProductRequestDTO, MerchantCenterAggregateRequestDTO, MerchantCenterTransportResponseDTO, MerchantCenterProductStatusResultDTO, MerchantCenterAggregateStatusListResultDTO, MerchantCenterAggregateStatusResultDTO, MerchantCenterAggregateStatisticsDTO, MerchantCenterAggregateIssueDTO, MerchantCenterDestinationStatusDTO, MerchantCenterItemIssueDTO.
- Exceptions: MerchantCenterException and its invalid-request, malformed-response, and transport subclasses.

This boundary maps product and aggregate product-status diagnostics from already-decoded provider response data. The Host supplies HTTP, OAuth, and credential handling. Provider results remain separate from core SEO validation; unknown provider values are preserved, missing values remain null or empty as modeled, and no synthetic overall eligibility verdict is derived. No retry behavior is promised.

### Implementation-only details

The runtime contains supporting types that are not presented here as consumer contracts: types below a namespace/path containing Internal, validation-profile support classes, the JSON-LD normalization concern, JsonLdBuilderTrait, and the factory-only DomainSeoPresetFactoryHelper. The Json-LD interface and abstract/concrete builders are the documented builder extension and consumption surface; implementation traits and helper mechanics are not primary API.

## MetaGeneratorService behavior

**MetaGeneratorService** constructs host-agnostic metadata from Host-provided defaults and active SEO overrides. The detailed [MetaGeneratorService contract](docs/SEO/library/META_GENERATOR_SERVICE_CONTRACT.md) is the current resolved behavior, not an unresolved Phase decision.

- The default title is trimmed. A null or blank trimmed default description becomes null; otherwise the trimmed value is used.
- Active title and description overrides are applied independently. Null or blank trimmed override values do not clear defaults; non-blank values replace only their corresponding default.
- A missing active override represented by SeoNotFoundException leaves defaults in place. Other override lookup failures propagate.
- Canonical precedence is: non-blank explicit command canonical, then the value returned by optional HostUrlGeneratorInterface, then null. An explicit usable canonical short-circuits the Host URL generator. The Host-generated value is returned as supplied; this service does not trim or validate it.
- The service trims robots only; it does not apply additional robots normalization.
- It mirrors final title/description into the current Open Graph and Twitter title/description fields and the final canonical into Open Graph URL and canonical URL. Open Graph type/image and Twitter card/image remain null in this service output.

## Redirects and SEO overrides

Redirect and SEO-override command/query services depend on repository interfaces. The package ships corresponding PDO repositories. Their state is stored in the package-owned tables, with soft-delete-aware queries and explicit hard-delete operations where exposed.

RedirectManagerService resolves the package's redirect decision from a ResolveRedirectCommand and may record permanent or gone redirect data when the relevant command service is configured. It returns domain results; selecting an HTTP status or emitting a response remains Host work.

Redirect records are independent SEO data: recording or resolving a redirect does not depend on recording a slug change. The Host decides when to create a redirect and applies the returned decision to its HTTP response.

The Host owns entity and route lifecycle, including slug generation, normalization, uniqueness, current values, history, and old-slug lookup. SEO does not require a Slug library or store slug lifecycle data. `HostUrlGeneratorInterface` is an SEO-owned Host port: a Host may use its own routing and data, a framework router, an optional Slug library, or an adapter to generate entity URLs. SEO may pass a Host-provided slug to that port as an input; any connection between SEO and a separate Slug library belongs to the Host or adapter.

SEO override lookup is scoped by entity and language; the resulting active override is consumed field-by-field by MetaGeneratorService. The Host continues to own its entities, identity model, and application data.

## Structured-data, canonical, and provider boundaries

The exact structured-data validation wording is **scoped structural and property-range semantic validation** for Product, Offer, AggregateOffer, and ProductGroup. The implementation does not claim complete Schema.org validation. Lexical acceptance is not proof that a value belongs to a standards registry, enumeration, or provider vocabulary.

Canonical and hreflang generation or lexical validation does not prove ISO 639 registry membership. Standards-data membership is a separately versioned standards-data contract. Provider-specific eligibility is also distinct: Google required/recommended properties and Merchant eligibility are not inferred by generic JSON-LD generation or the scoped semantic validator.

Open Graph behavior is source-reviewed where documented. Twitter/X provider conformance was not source-verified. External-provider capabilities are limited to their typed, documented request/response boundaries; they do not promise network retries or guarantee provider outcomes.

## Verification represented by current CI

The repository's CI configuration includes:

- Standalone verification on PHP 8.2, 8.3, 8.4, and 8.5.
- PHPStan level max over src and tests, dependency resolution, lowest-dependency verification on minimum PHP, strict Composer validation, and platform requirement checks.
- Composer security audit with abandoned-package failures and workflow lint.
- Real MySQL persistence Integration with PHP 8.2/current dependencies, PHP 8.5/current dependencies, and PHP 8.2/lowest dependencies.
- Consumer Verification Harness on PHP 8.2 and PHP 8.5. It installs through a staged Composer path repository, loads the consumer's production autoloader, and runs the MySQL workflow from a separate consumer root.

The staged path repository proves package-consumer behavior; it does not prove external Packagist publication. mysql:8.4.11 is the CI reproducibility fixture, not a minimum-product compatibility claim. The required terminal status is CI Gate. Configured gates alone do not establish that a current workflow run has passed or that the package is Stable or release-ready.

## Further documentation

- [Usage guide](docs/guides/USAGE_GUIDE.md) — feature-oriented examples and output boundaries.
- [Integration guide](docs/guides/INTEGRATION_GUIDE.md) — Host wiring, PDO, and schemas.
- [MetaGeneratorService contract](docs/SEO/library/META_GENERATOR_SERVICE_CONTRACT.md) — resolved override, canonical, and output behavior.
- [Structured data architecture](docs/SEO/library/STRUCTURED_DATA_ARCHITECTURE.md) — JSON-LD composition and scoped validation.
- [CI operations](docs/CI.md) — local commands and configured workflow gates.
