# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]
- **Changed:** Removed SEO ownership of slug lifecycle and history, including the public Slug History runtime, Admin, persistence, and schema surfaces. The Host owns slug lifecycle; SEO does not require a Slug library.
- **Changed:** Updated the Admin metadata import/export schema from `1.0` to `2.0`; current import/export data contains only SEO overrides and redirects.
- **Changed:** Adopted and pinned the PHP engineering standards set and synchronized repository governance with the current authorities.
- **Changed:** Normalized the Composer package identity to `maatify/php-seo`.
- **Changed:** Migrated package exceptions to the shared `maatify/exceptions` taxonomy while preserving the package's established exception contracts.
- **Changed:** Hardened CI and static-analysis verification, including real MySQL persistence coverage and the external Consumer Verification Harness.
- **Added:** The canonical `SEO_PACKAGE_REFERENCE.md` and synchronized current package documentation with its verified contracts.
- **Added:** Phase 24 adopted a normative `MetaGeneratorService` output contract that resolves the Stack 0 material decision through a successor contract while preserving runtime behavior.
- **Added:** The Phase 24 standalone regression lock covers 22 normative contract cases.
- **Fixed:** A Phase 24 test-only correction removed unauthorized exact fallback `HostUrlGeneratorInterface` call-count assertions; no production `src/` behavior changed.
- **Added:** Advanced Product structured-data composition, including `ProductGroup`, `AggregateOffer`, and typed nested builder composition.
- **Added:** scoped structural and property-range semantic validation for selected JSON-LD types: `Product`, `Offer`, `AggregateOffer`, and `ProductGroup`, while preserving the legacy validation/report/score contracts.
- **Added:** Phase 20 standalone CLI-friendly examples for SEO validation, Product audits, redirects, overrides, page rendering, and robots output; no CLI package or runtime dependency was introduced.
- **Added:** Phase 21 explicit PHP syntax gate, focused structured-data validation CI gate, and release/package-readiness checklist/procedures; the pre-existing PHP 8.2/8.3/8.4 matrix was preserved.
- **Added:** Optional Search Console URL Inspection evidence boundary under `Maatify\Seo\Web\Indexing\SearchConsole`; provider transport and credentials remain host-owned and separate from core validation.
- **Added:** Optional Merchant Center Eligibility Diagnostics boundary under `Maatify\Seo\Web\MerchantCenter`; provider results remain separate from core validation and scoring.
- **Changed:** Expanded the pre-existing Usage Guide in Phase 8 with an end-to-end homepage example and the then-current validation boundary; later Stack 8 documentation records the completed scoped contract.
- **Added:** Architecture remediation Stack 0 contract characterization and Stack 1 additive validation/diagnostic foundation.
- **Added:** Architecture remediation Stack 2 RFC 9309 and Google robots validation profiles, separate from the pre-existing robots.txt output helpers.
- **Changed:** Architecture remediation Stack 3 unified canonical in-memory sitemap serialization across existing public entry points.
- **Added:** Architecture remediation Stack 4 companion Sitemaps.org, Google Sitemap, Image, Video, and News validation profiles; existing sitemap element support remains compatible.
- **Added:** Architecture remediation Stack 5 Open Graph companion protocol diagnostics while preserving legacy results and scores.
- **Added:** Architecture remediation Stack 6 canonical and hreflang companion profiles and normalization; ISO registry membership remains deferred.
- **Added:** Architecture remediation Stack 7 contract characterization of generic Schema.org generation, scoped validation, and deferred provider eligibility.
- **Changed:** Architecture remediation Stack 8 synchronized current documentation, lifecycle records, and the normative-versus-historical documentation hierarchy.
- **Fixed:** Strict sitemap date validation now rejects malformed calendar ATOM dates. Valid YYYY-MM-DD and ATOM timestamps remain accepted.
- **Fixed:** Raw top-level sitemap URL validation is now aligned with the typed DTO contract.
- **Fixed:** Sitemap priority validation correctly enforces a finite number within `0.0..1.0` (rejecting `NAN`, `+INF`, and `-INF`).

## [1.0.0-rc.1] - 2026-07-05
- **Summary:** Initial Release Candidate for the Maatify SEO library including fully implemented core, shared, admin, and web layers, robust JSON-LD schema support, metadata generation, output showcase, and final verifications.
- **Added:** Finalized package licensing (MIT) and Composer metadata for v1.0.0-rc.1 release readiness.
- **Added:** Implementation of Phase 13I Commerce JSON-LD Builders (`ReviewJsonLdBuilder`, `AggregateRatingJsonLdBuilder`, `OfferJsonLdBuilder`, `ServiceJsonLdBuilder`, `LocalBusinessJsonLdBuilder`) to fluently generate JSON-LD output.
- **Added:** Implementation of Phase 13F WebSite JSON-LD Builder (`WebSiteJsonLdBuilder`) to fluently generate WebSite JSON-LD output.
- **Added:** Implementation of Phase 11G SEO Validation Batch Report Exporter including `SeoValidationBatchReportExporter` to export batch reports to Array, JSON, Summary Array, and Markdown.
- **Added:** Implementation of Phase 11F SEO Validation Batch Report Helpers including `SeoValidationBatchReportBuilder` and `SeoValidationBatchReportDTO` to build SEO validation reports for multiple entities in one batch.
- **Added:** Implementation of Phase 11E SEO Validation Report Exporter including `SeoValidationReportExporter` to export reports to Array, JSON, Summary Array, and Markdown.
- **Added:** Implementation of Phase 10E News Sitemap Support in `SitemapXmlStringRenderer` and addition of `SitemapNewsDTO` for standard Google news sitemap indexing.
- **Added:** Implementation of Phase 11D SEO Validation Presets including `SeoValidationPreset` providing pre-configured validation and score option arrays.
- **Added:** Implementation of Phase 11C SEO Validation Report Helpers including `SeoValidationReportBuilder` and `SeoValidationReportDTO` for comprehensive combined reporting.
- **Added:** Implementation of Phase 11B SEO Validation Score Helpers including `SeoValidationScoreCalculator` and `SeoValidationScoreDTO` to generate actionable scores and grades.
- **Added:** Implementation of Phase 11A SEO Validation Helpers including `SeoMetaValidator`, `SeoValidationResultDTO`, and `SeoValidationIssueDTO` for framework-agnostic metadata auditing.
- **Added:** Implementation of Phase 10D Video Sitemap Support in `SitemapXmlStringRenderer` and addition of `SitemapVideoDTO` for standard Google video sitemap indexing.
- **Added:** Implementation of Phase 10C Image Sitemap Support in `SitemapXmlStringRenderer` and addition of `SitemapImageDTO` for standard Google image sitemap indexing.
- **Added:** Implementation of Phase 10B Sitemap Hreflang / Alternate URL Support in `SitemapXmlStringRenderer` and addition of `SitemapAlternateUrlDTO` for multi-language indexing.
- **Added:** Implementation of Phase 10A Sitemap Index String Renderer (`SitemapIndexXmlStringRenderer`) to directly render XML sitemap index strings.
- **Added:** Implementation of Phase 9A Robots.txt Output Helpers (`RobotsTxtRenderer`) to generate `robots.txt` strings in a framework-neutral way.
- **Added:** Implementation of Phase 7E Sitemap String Output Helpers (`SitemapXmlStringRenderer`) to directly render XML sitemap strings.
- **Added:** Implementation of Phase 7D Optional Spatie Schema Integration to provide a framework-neutral adapter for `spatie/schema-org`.
- **Added:** Implementation of Phase 7C Fluent SEO Builder to provide a framework-neutral fluent interface for dynamic output construction.
- **Added:** Phase 7B (Usability & Rendering) Render Output DTOs (`SeoHeadHtmlDTO`) implemented and verified.
- **Added:** Phase 7A (Usability & Rendering) HTML rendering helpers (`SeoHeadHtmlRenderer`, etc.) implemented and verified.
- **Added:** Phase 6D (Final Module Compliance Audit) completed and verified.
- **Added:** Phase 6C (Bootstrap/DI Full Wiring) implementation including `Bootstrap/SeoBindings.php`.
- **Added:** Phase 6B (Web Layer) implementation including `Web/SeoRender/Service/SeoPageRenderService`, `Web/SeoRender/Command/RenderSeoPageCommand`, and `Web/SeoRender/DTO/SeoPagePayloadDTO`.
- **Added:** Phase 6A (Admin Layer) implementation including `AdminSeoOverride`, `AdminRedirect`, and `AdminSlugHistory` services, DTOs, and commands.
- **Added:** Phase 5 (Documentation & Polish) implementation including final validations and verification reports.
- **Added:** Phase 4 (Sitemap Generation) implementation including `SitemapGeneratorService` and heavily-validated DTOs (`SitemapUrlDTO`, `SitemapIndexEntryDTO`, etc.) to generate/render valid in-memory XML strings.
- **Added:** Phase 3C (Redirect & Slug Services) implementation including `RedirectManagerService`, `SlugHistoryService`, and corresponding DTOs.
- **Added:** Phase 3B (JSON-LD Schema Generator) implementation including `SchemaGeneratorService` and various strictly typed schema DTOs.
- **Added:** Phase 3A (Meta Generator) implementation including `GenerateMetaTagsCommand`, `MetaTagsDTO`, and `MetaGeneratorService`.
- **Added:** Phase 2C (Service Layer) implementations for redirect, slug history, and SEO overrides.
- **Added:** Phase 2B (Repository Layer) containing PDO implementations for standard CRUD.
- **Added:** Phase 2A (Schema) including `maa_seo_slug_history`, `maa_seo_redirects`, and `maa_seo_overrides` tables.
- Initial foundational release (Phase 1).

[Unreleased]: https://github.com/Maatify/php-seo/compare/v1.0.0-rc.1...HEAD
[1.0.0-rc.1]: https://github.com/Maatify/php-seo/releases/tag/v1.0.0-rc.1
