<?php

declare(strict_types=1);

function stack8Fail(string $message): never
{
    fwrite(STDERR, "Assertion failed: {$message}\n");
    exit(1);
}

function stack8AssertTrue(string $label, bool $actual): void
{
    if (!$actual) {
        stack8Fail($label);
    }
}

function stack8AssertFalse(string $label, bool $actual): void
{
    stack8AssertTrue($label, !$actual);
}

function stack8AssertContains(string $label, string $haystack, string $needle): void
{
    stack8AssertTrue($label, str_contains($haystack, $needle));
}

function stack8AssertNotContains(string $label, string $haystack, string $needle): void
{
    stack8AssertFalse($label, str_contains($haystack, $needle));
}

function stack8Read(string $relativePath): string
{
    $path = dirname(__DIR__) . '/' . $relativePath;
    if (!is_file($path)) {
        stack8Fail("missing documentation fixture: {$relativePath}");
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        stack8Fail("unable to read documentation fixture: {$relativePath}");
    }

    return $contents;
}

// This is intentionally a documentation-only test: it must not load production code.
$loadedSourceFiles = array_values(array_filter(
    get_included_files(),
    static fn (string $path): bool => str_contains($path, DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR),
));
stack8AssertTrue('Stack 8 test has no production-source dependency', $loadedSourceFiles === []);

$docsIndex = stack8Read('docs/README.md');
$repositoryRoot = dirname(__DIR__);
$referencePath = $repositoryRoot . '/SEO_PACKAGE_REFERENCE.md';
stack8AssertTrue('canonical Package Reference exists at repository root', is_file($referencePath));
$oldReferencePath = $repositoryRoot . '/docs/' . 'SEO_' . 'LIBRARY_REFERENCE.md';
stack8AssertFalse('obsolete nested Package Reference has been removed', is_file($oldReferencePath));
$rootPackageReferences = glob($repositoryRoot . '/*_PACKAGE_REFERENCE.md') ?: [];
stack8AssertTrue('exactly one root Package Reference exists', count($rootPackageReferences) === 1);
stack8AssertTrue(
    'the sole root Package Reference is SEO_PACKAGE_REFERENCE.md',
    count($rootPackageReferences) === 1 && basename($rootPackageReferences[0]) === 'SEO_PACKAGE_REFERENCE.md',
);

foreach ([
    '## Documentation authority',
    'Executable truth',
    '- composer.json',
    '- src/**',
    '- schema/**',
    '- tests/**',
    'Canonical package contract',
    '- SEO_PACKAGE_REFERENCE.md',
    'Maintained documentation',
    '- README.md',
    '- docs/guides/**',
    '- docs/SEO/library/**',
    '- docs/CI.md',
    'Future planning',
    '- docs/roadmap/ROADMAP.md',
    '- active proposals only',
    'Governance',
    '- docs/php-engineering-standards/**',
] as $needle) {
    stack8AssertContains("docs index contains {$needle}", $docsIndex, $needle);
}

$libraryHandbook = stack8Read('docs/SEO/library/README.md');
stack8AssertContains(
    'library README identifies the current maintained engineering handbook',
    $libraryHandbook,
    'current, maintained Maatify SEO Library Engineering',
);
stack8AssertContains(
    'library handbook heading identifies it as an engineering handbook',
    $libraryHandbook,
    '# Maatify SEO Library Engineering Handbook',
);
stack8AssertContains(
    'library handbook defers to the canonical package-level contract',
    $libraryHandbook,
    '[SEO_PACKAGE_REFERENCE.md](../../../SEO_PACKAGE_REFERENCE.md)',
);
stack8AssertContains(
    'library handbook identifies the canonical package-level contract',
    $libraryHandbook,
    'canonical package-level contract',
);
stack8AssertContains(
    'historical implementation evidence belongs to Git and GitHub history',
    $libraryHandbook,
    'Historical implementation and execution evidence lives in Git and GitHub',
);
stack8AssertContains(
    'historical evidence is located in commits, pull requests, tags, and releases',
    $libraryHandbook,
    'commits, pull requests, tags, and releases',
);
stack8AssertContains(
    'roadmap and active proposals are planning material',
    $libraryHandbook,
    'active proposals are planning',
);
stack8AssertContains('library handbook links to the current roadmap', $libraryHandbook, '../../roadmap/ROADMAP.md');
stack8AssertContains(
    'planning does not override current executable or package contracts',
    $libraryHandbook,
    'They do not override executable truth or current package contracts',
);
stack8AssertContains(
    'host-specific SEO architecture is outside the package handbook without an explicit contract',
    $libraryHandbook,
    'outside this package handbook',
);
stack8AssertTrue(
    'host-specific architecture is in scope only when an explicit package contract represents it',
    preg_match('/outside this package handbook\s+unless represented by an explicit current package contract/', $libraryHandbook) === 1,
);
foreach ([
    'routing structure',
    'product lifecycle',
    'HTTP status decisions',
    'internal-linking strategy',
    'site-specific multilingual URL policy',
] as $hostOwnedArchitecture) {
    stack8AssertContains("handbook marks {$hostOwnedArchitecture} as Host/application-specific", $libraryHandbook, $hostOwnedArchitecture);
}
foreach ([
    'docs/SEO/v1',
    'docs/phases/',
    'docs/verification/',
    'docs/batches/',
    'docs/blueprints/',
    'docs/audits/',
    'provide historical evidence',
] as $deletedDocumentationReference) {
    stack8AssertNotContains(
        "library handbook does not cite deleted documentation sources: {$deletedDocumentationReference}",
        $libraryHandbook,
        $deletedDocumentationReference,
    );
}

$metaGeneratorContract = stack8Read('docs/SEO/library/META_GENERATOR_SERVICE_CONTRACT.md');
foreach ([
    'current maintained contract of `MetaGeneratorService`',
    'canonical package-level contract',
    'narrower normative service semantics',
    'current runtime source and maintained tests are executable evidence',
    'Historical implementation phases',
] as $currentContractMarker) {
    stack8AssertContains(
        "MetaGeneratorService contract records {$currentContractMarker}",
        $metaGeneratorContract,
        $currentContractMarker,
    );
}
foreach ([
    'Phase 24',
    'integration/phase-24-meta-generator-contract',
    'WU2',
    'not yet be part of current `main`',
    'Stack 0',
    'architecture audit',
] as $staleMetaContractClaim) {
    stack8AssertNotContains(
        "MetaGeneratorService contract does not retain stale claim {$staleMetaContractClaim}",
        $metaGeneratorContract,
        $staleMetaContractClaim,
    );
}

foreach ([
    'Package authority and public contract',
    'Metadata generation and override semantics',
    'HTML head rendering and social metadata',
    'Canonical URLs and hreflang',
    'Robots and sitemaps',
    'Structured data and JSON-LD',
    'Core validation and companion profiles',
    'Redirects and SEO overrides',
    'Persistence and package-owned schemas',
    'Admin previews, operations, and import/export',
    'Page presets and page rendering',
    'Search Console',
    'Merchant Center',
    'CI and local verification',
    'Future roadmap and active proposals',
] as $handbookArchitectureArea) {
    stack8AssertContains(
        "current Engineering Handbook map includes {$handbookArchitectureArea}",
        $libraryHandbook,
        $handbookArchitectureArea,
    );
}
foreach (['Phase 13O', 'Phase 13P', 'phase execution history'] as $staleHandbookChronology) {
    stack8AssertNotContains(
        "Engineering Handbook map does not frame current architecture as {$staleHandbookChronology}",
        $libraryHandbook,
        $staleHandbookChronology,
    );
}

$changelog = stack8Read('CHANGELOG.md');
stack8AssertContains('Unreleased changelog exists', $changelog, '## [Unreleased]');
stack8AssertNotContains('changelog does not claim XML streaming', $changelog, 'to stream valid XML');
$rcHeading = strpos($changelog, '## [1.0.0-rc.1]');
if ($rcHeading === false) {
    stack8Fail('RC1 changelog heading exists after Unreleased history');
}
$unreleased = substr($changelog, 0, $rcHeading);
foreach ([
    'ProductGroup',
    'AggregateOffer',
    'scoped structural and property-range semantic validation',
    'CLI',
    'Search Console',
    'Merchant Center',
    'Stack 0',
    'Stack 1',
    'Stack 2',
    'Stack 3',
    'Stack 4',
    'Stack 5',
    'Stack 6',
    'Stack 7',
    'Stack 8',
] as $needle) {
    stack8AssertContains("Unreleased history contains {$needle}", $changelog, $needle);
}
stack8AssertContains('post-RC Unreleased history records Phase 21 syntax gate', $unreleased, 'Phase 21 explicit PHP syntax gate');
stack8AssertContains('post-RC Unreleased history records Phase 21 structured-data CI gate', $unreleased, 'focused structured-data validation CI gate');
stack8AssertContains('post-RC Unreleased history records Phase 21 release/package readiness', $unreleased, 'release/package-readiness checklist/procedures');
stack8AssertContains('Phase 21 preserves the pre-existing PHP matrix', $unreleased, 'pre-existing PHP 8.2/8.3/8.4 matrix was preserved');
$phase21ChangelogLine = '';
foreach (explode("\n", $unreleased) as $line) {
    if (str_contains($line, 'Phase 21')) {
        $phase21ChangelogLine = $line;
        break;
    }
}
stack8AssertTrue('Phase 21 has one focused changelog line', $phase21ChangelogLine !== '');
stack8AssertFalse(
    'Phase 21 must not claim it added the PHP matrix',
    preg_match('/(?:added|adds|introduced|introduces).{0,100}PHP 8\.2\/8\.3\/8\.4|PHP 8\.2\/8\.3\/8\.4.{0,100}(?:added|adds|introduced|introduces)/i', $phase21ChangelogLine) === 1,
);
$forbiddenAddedClaims = [
    'Framework-neutral `robots.txt` output helpers',
    'Sitemap index, hreflang alternate, image, video, and news support',
    'SEO validation presets, scores, reports, batch reports',
    'Developer usage and integration documentation',
    'Added:** Usage Guide',
];
preg_match_all('/^- \*\*Added:\*\*.*$/mi', $unreleased, $addedLines);
foreach ($addedLines[0] as $addedLine) {
    foreach ($forbiddenAddedClaims as $claim) {
        stack8AssertNotContains("RC1 capability is not claimed as a new addition: {$claim}", $addedLine, $claim);
    }
}

$readme = stack8Read('README.md');
stack8AssertContains('README links to the canonical Package Reference', $readme, '](SEO_PACKAGE_REFERENCE.md)');
stack8AssertContains('README records package-owned PDO persistence', $readme, 'The host supplies PDO and connection configuration; the package ships concrete PDO repositories and its own schemas');
stack8AssertNotContains('README does not require Host ORM repository implementations', $readme, 'allowing the host application to use Doctrine, Eloquent, or native PDO');
stack8AssertContains('README records base and strict sitemap scope', $readme, 'base/strict DTO fields');
stack8AssertContains('README separates sitemap provider profiles', $readme, 'provider/profile validation boundaries');
stack8AssertNotContains('README does not imply provider strict compliance', $readme, 'strict URL/date/frequency/priority validation.');
stack8AssertContains('README records Twitter/X audit boundary', $readme, 'Twitter/X provider conformance was not source-verified');

$structuredDocPaths = [
    'docs/SEO/library/STRUCTURED_DATA_ARCHITECTURE.md',
    'SEO_PACKAGE_REFERENCE.md',
    'docs/guides/USAGE_GUIDE.md',
];
$structuredDocContents = [];
foreach ($structuredDocPaths as $path) {
    $contents = stack8Read($path);
    $structuredDocContents[$path] = $contents;
    stack8AssertContains("{$path} uses the Stack 7 validation wording", $contents, 'scoped structural and property-range semantic validation');
}

$usageGuide = $structuredDocContents['docs/guides/USAGE_GUIDE.md'];
stack8AssertContains('Usage Guide presents a practical capability decision map', $usageGuide, '## Capability decision map');
stack8AssertContains('Usage Guide states its observable-output walkthrough pattern', $usageGuide, "shows an\nobserved result");
stack8AssertContains('Usage Guide remains subordinate to canonical Package Reference', $usageGuide, 'canonical Package Reference');
stack8AssertContains('Usage Guide links directly to the canonical Package Reference', $usageGuide, '](../../SEO_PACKAGE_REFERENCE.md)');
stack8AssertContains('Usage Guide labels provider fixtures as samples, not live truth', $usageGuide, 'not a captured response or proof of');
stack8AssertContains('Usage Guide separates provider input from mapped package DTO', $usageGuide, 'mapper returns this');
stack8AssertContains('Usage Guide keeps Host HTTP and OAuth responsibilities explicit', $usageGuide, 'owns the Google request, OAuth credentials');
stack8AssertContains('Usage Guide states that unknown evidence is not a verdict', $usageGuide, 'not an invented pass or failure');
stack8AssertNotContains('Usage Guide current presentation has no historical phase language', $usageGuide, 'Phase');
foreach (glob(dirname(__DIR__) . '/examples/*.php') ?: [] as $examplePath) {
    $exampleContent = file_get_contents($examplePath);
    stack8AssertTrue(
        'current example presentation contains no historical phase labels: ' . basename($examplePath),
        is_string($exampleContent) && preg_match('/\bphase\b/i', $exampleContent) !== 1,
    );
}
stack8AssertTrue(
    'examples directory has no phase-named current examples',
    (glob(dirname(__DIR__) . '/examples/phase*.php') ?: []) === [],
);
foreach ([
    'Capability decision map',
    'MetaTagsDTO',
    'SeoHeadHtmlDTO',
    'SeoHeadHtmlRenderer',
    'FluentSeoBuilder',
    'MetaGeneratorService',
    'HostUrlGeneratorInterface',
    'JsonLdSchemaDTO',
    'JsonLdScriptRenderer',
    'ProductJsonLdBuilder',
    'AggregateOfferJsonLdBuilder',
    'ProductGroupJsonLdBuilder',
    'SpatieSchemaAdapter',
    'SitemapXmlStringRenderer',
    'SitemapIndexXmlStringRenderer',
    'RobotsTxtRenderer',
    'MetaRobotsBuilder',
    'SeoPagePresetFactory',
    'EcommerceSeoPresetFactory',
    'ContentSeoPresetFactory',
    'LocalBusinessSeoPresetFactory',
    'SeoPagePresetOutputDTO',
    'RenderSeoPageCommand',
    'SeoPagePayloadDTO',
    'SeoPageRenderService',
    'CanonicalUrlBuilder',
    'HreflangLinkBuilder',
    'HreflangLinkDTO',
    'HreflangLinkRenderer',
    'GoogleCanonicalValidator',
    'GoogleHreflangClusterValidator',
    'OpenGraphBuilder',
    'TwitterCardBuilder',
    'SocialPreviewBuilder',
    'AdminRedirectCommandService',
    'AdminSeoOverrideCommandService',
    'SerpPreviewFactory',
    'SocialPreviewFactory',
    'SeoMetadataImporter',
    'SeoMetadataExporter',
    'RedirectDecisionDTO',
    'RedirectManagerService',
    'SeoOverrideQueryService',
    'SerpPreviewDTO',
    'SocialPreviewDTO',
    'SearchConsoleResponseMapper',
    'MerchantCenterResponseMapper',
    'SearchConsoleInspectionResultDTO',
    'MerchantCenterProductStatusResultDTO',
    'Rfc9309RobotsValidator',
    'SitemapProtocolValidator',
    'GoogleSitemapValidator',
    'OpenGraphProtocolValidator',
] as $usageCapability) {
    stack8AssertContains(
        "Usage Guide exposes current capability {$usageCapability}",
        $usageGuide,
        $usageCapability,
    );
}
stack8AssertContains(
    'Usage Guide separates companion profiles from core validation',
    $usageGuide,
    'do not silently change the generic result or score',
);

$integrationGuide = stack8Read('docs/guides/INTEGRATION_GUIDE.md');
foreach ([
    'docs/guides/USAGE_GUIDE.md' => $usageGuide,
    'docs/guides/INTEGRATION_GUIDE.md' => $integrationGuide,
] as $renderGuidePath => $renderGuide) {
    stack8AssertTrue(
        "{$renderGuidePath} limits RenderSeoPageCommand schemas to JsonSerializable values",
        preg_match('/`RenderSeoPageCommand::\$schemas` accepts values that implement\s+`JsonSerializable`/', $renderGuide) === 1,
    );
    stack8AssertTrue(
        "{$renderGuidePath} distinguishes JSON-LD builders from schema DTOs",
        preg_match('/does not implement\s+`JsonSerializable`\s+and cannot be passed directly/', $renderGuide) === 1,
    );
    stack8AssertContains(
        "{$renderGuidePath} materializes builder arrays as JsonLdSchemaDTO values",
        $renderGuide,
        'new JsonLdSchemaDTO($productSchemaBuilder->toArray())',
    );
    stack8AssertContains(
        "{$renderGuidePath} passes the serializable DTO to RenderSeoPageCommand",
        $renderGuide,
        'schemas: [$productSchema]',
    );
    stack8AssertNotContains(
        "{$renderGuidePath} does not pass a builder directly to RenderSeoPageCommand",
        $renderGuide,
        'schemas: [$productSchemaBuilder]',
    );
}

foreach ([
    'SeoPagePresetFactory',
    'SeoPageRenderService',
    'MetaGeneratorService',
    'HostUrlGeneratorInterface',
    'GoogleCanonicalValidator',
    'GoogleHreflangClusterValidator',
    'AdminRedirectCommandService',
    'AdminSeoOverrideCommandService',
    'SerpPreviewFactory',
    'SeoMetadataImporter',
    'SearchConsoleInspectionService',
    'SearchConsoleTransportInterface',
    'MerchantCenterDiagnosticsService',
    'MerchantCenterTransportInterface',
    'httpStatus',
    'getHttpStatus()',
] as $integrationCapability) {
    stack8AssertContains(
        "Integration Guide explains current capability/boundary {$integrationCapability}",
        $integrationGuide,
        $integrationCapability,
    );
}
foreach ([
    'Host creates and configures PDO',
    'new PdoRedirectRepository($pdo)',
    'new PdoSeoOverrideRepository($pdo)',
    'schema/maa_seo_redirects.sql',
    'schema/maa_seo_overrides.sql',
    'Result shape verified by the maintained MySQL integration test',
    'do not begin, commit, or roll back transactions',
] as $persistenceInvariant) {
    stack8AssertContains(
        "Integration Guide documents persistence invariant {$persistenceInvariant}",
        $integrationGuide,
        $persistenceInvariant,
    );
}
foreach ([
    'AdminRedirectCommandService',
    'AdminRedirectQueryService',
    'AdminSeoOverrideCommandService',
    'AdminSeoOverrideQueryService',
    'getActiveByRequestedSlug',
    'listByEntity',
    'softDelete',
    'hardDelete',
    'isDeleted',
] as $adminCrudInvariant) {
    stack8AssertContains(
        "Usage Guide documents Admin API invariant {$adminCrudInvariant}",
        $usageGuide,
        $adminCrudInvariant,
    );
}
stack8AssertContains(
    'Integration Guide keeps provider status distinct from Host response status',
    $integrationGuide,
    'A provider HTTP 403 is evidence about the provider request. It does not automatically become the Host application\'s HTTP 403.',
);
foreach ([
    '"is_valid": false',
    '"is_healthy": false',
    '"deductions"',
    '"context"',
    'SeoValidationReportExporter::toJson($report)',
    'SeoValidationBatchReportExporter::toJson($batch)',
    '"averageScore": 97.5',
    'SEO batch validation completed with warnings.',
] as $reportOutputInvariant) {
    stack8AssertContains(
        "Usage Guide documents report output invariant {$reportOutputInvariant}",
        $usageGuide,
        $reportOutputInvariant,
    );
}
foreach (['Host owns Admin UI', 'authentication, authorization', 'application workflow'] as $hostOwnedAdminSurface) {
    stack8AssertContains(
        "Integration Guide keeps Host ownership of {$hostOwnedAdminSurface}",
        $integrationGuide,
        $hostOwnedAdminSurface,
    );
}
stack8AssertContains(
    'Integration Guide separates provider evidence from generic validation',
    $integrationGuide,
    'separate from generic SEO validation',
);

$adminRfc = stack8Read('docs/proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md');
stack8AssertContains('active Admin RFC remains proposed', $adminRfc, '**Status:** Proposed');
foreach ([
    'current package already provides granular Admin-facing capabilities',
    'optional, higher-level orchestration/control API',
    'No UI or views',
    'No routes or controllers',
    'No authentication or authorization',
    'No framework coupling',
    'No Host lifecycle ownership',
] as $adminRfcCurrentBoundary) {
    stack8AssertContains(
        "active Admin RFC states {$adminRfcCurrentBoundary}",
        $adminRfc,
        $adminRfcCurrentBoundary,
    );
}
foreach ([
    'Post v1.0.0',
    'initial `v1.0.0` release',
    'Not Required for v1.0.0',
    'Phase 11',
    'Phase 19',
] as $staleAdminRfcChronology) {
    stack8AssertNotContains(
        "active Admin RFC does not claim a stale release/phase baseline {$staleAdminRfcChronology}",
        $adminRfc,
        $staleAdminRfcChronology,
    );
}

foreach ([
    'docs/SEO/library/README.md' => $libraryHandbook,
    'docs/SEO/library/META_GENERATOR_SERVICE_CONTRACT.md' => $metaGeneratorContract,
    'docs/SEO/library/STRUCTURED_DATA_ARCHITECTURE.md' => $structuredDocContents['docs/SEO/library/STRUCTURED_DATA_ARCHITECTURE.md'],
    'docs/guides/USAGE_GUIDE.md' => $usageGuide,
    'docs/guides/INTEGRATION_GUIDE.md' => $integrationGuide,
    'docs/proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md' => $adminRfc,
] as $currentDocumentationPath => $currentDocumentation) {
    foreach ([
        'docs/SEO/v1',
        'docs/phases/',
        'docs/verification/',
        'docs/batches/',
        'docs/blueprints/',
        'docs/audits/',
    ] as $deletedDocumentationDirectory) {
        stack8AssertNotContains(
            "{$currentDocumentationPath} does not cite deleted documentation authority {$deletedDocumentationDirectory}",
            $currentDocumentation,
            $deletedDocumentationDirectory,
        );
    }
}

$currentStructuredDocs = implode('\n', $structuredDocContents);
foreach ([
    'scoped structural and property-range semantic validation',
    'Product',
    'Offer',
    'AggregateOffer',
    'ProductGroup',
] as $needle) {
    stack8AssertContains("current structured docs contain {$needle}", $currentStructuredDocs, $needle);
}
stack8AssertContains('current docs record the Hreflang ISO boundary', $currentStructuredDocs, 'ISO 639');
stack8AssertContains('current docs defer standards-data membership', $currentStructuredDocs, 'separately versioned standards-data contract');
stack8AssertContains('current docs record the provider capability-matrix boundary', $currentStructuredDocs, 'capability matrix');
stack8AssertContains('current docs record the date-stamped provider boundary', $currentStructuredDocs, 'date-stamped contract');
stack8AssertContains('current docs separate Google required and recommended properties', $currentStructuredDocs, 'Google required/recommended');
stack8AssertContains('current docs separate Merchant eligibility', $currentStructuredDocs, 'Merchant eligibility');
stack8AssertContains('current docs preserve the Twitter/X compatibility boundary', $readme, 'Twitter/X provider conformance was not source-verified');

$reference = $structuredDocContents['SEO_PACKAGE_REFERENCE.md'];
foreach ([
    'maatify/php-seo',
    'maatify/exceptions',
    'The host supplies a configured **PDO**',
    'package-owned tables',
    'maa_seo_redirects',
    'maa_seo_overrides',
    'Consumer Verification Harness',
    'SeoExceptionInterface extends Throwable',
    'INVALID_ARGUMENT',
    'Google required/recommended',
    'Merchant eligibility',
    'separately versioned standards-data contract',
] as $needle) {
    stack8AssertContains("canonical Package Reference contains {$needle}", $reference, $needle);
}
stack8AssertContains('MetaGeneratorService links its current resolved contract', $reference, 'META_GENERATOR_SERVICE_CONTRACT.md');
stack8AssertNotContains('canonical Package Reference has no unresolved MetaGenerator contract wording', $reference, 'unknown / needs decision');

foreach ([
    'README.md' => $readme,
    'SEO_PACKAGE_REFERENCE.md' => $reference,
    'docs/README.md' => $docsIndex,
    'docs/SEO/library/README.md' => $libraryHandbook,
    'docs/guides/USAGE_GUIDE.md' => $usageGuide,
    'docs/guides/INTEGRATION_GUIDE.md' => $integrationGuide,
    'docs/proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md' => $adminRfc,
] as $currentSlugOwnershipDocPath => $currentSlugOwnershipDoc) {
    foreach ([
        'SlugHistory',
        'AdminSlugHistory',
        'PdoSlugHistoryRepository',
        'maa_seo_slug_history',
        'slug_history',
        'RecordSlugChangeCommand',
    ] as $removedSlugOwnershipSurface) {
        stack8AssertNotContains(
            "{$currentSlugOwnershipDocPath} does not describe removed SEO surface {$removedSlugOwnershipSurface}",
            $currentSlugOwnershipDoc,
            $removedSlugOwnershipSurface,
        );
    }
}
stack8AssertContains(
    'Package Reference states that SEO does not own slug lifecycle data',
    $reference,
    'SEO does not require a Slug library or store slug lifecycle data.',
);
stack8AssertContains(
    'Package Reference preserves the SEO-owned Host URL port boundary',
    $reference,
    '`HostUrlGeneratorInterface` is an SEO-owned Host port',
);
stack8AssertContains(
    'Package Reference leaves optional Slug integration to the Host or adapter',
    $reference,
    'any connection between SEO and a separate Slug library belongs to the Host or adapter.',
);
stack8AssertFalse(
    'removed package-owned slug-history schema is absent',
    is_file(dirname(__DIR__) . '/schema/maa_seo_slug_history.sql'),
);

$roadmap = stack8Read('docs/roadmap/ROADMAP.md');
preg_match_all('/^## .+$/m', $roadmap, $roadmapHeadings);
stack8AssertTrue(
    'roadmap contains only the three future items and active proposal',
    $roadmapHeadings[0] === [
        '## 1. Deeper Generic Schema.org Semantic Validation',
        '## 2. Google Rich Results / Provider-Specific Eligibility Profile',
        '## 3. Large Sitemap Memory / Streaming Strategy',
        '## Active proposal',
    ],
);
stack8AssertContains(
    'generic semantic validation remains scoped to selected structures',
    $roadmap,
    '`Product`, `Offer`, `AggregateOffer`, and `ProductGroup`',
);
stack8AssertContains(
    'roadmap does not imply current JSON-LD generation is invalid',
    $roadmap,
    'does not imply that current JSON-LD generation is invalid',
);
stack8AssertContains(
    'Google eligibility remains a distinct future capability',
    $roadmap,
    'Results eligibility prediction or profile',
);
stack8AssertContains(
    'Merchant Center diagnostics are already implemented',
    $roadmap,
    'implemented and are not future work',
);
stack8AssertContains(
    'sitemap roadmap preserves the current in-memory API',
    $roadmap,
    'The current in-memory API remains valid',
);
stack8AssertContains(
    'active Admin RFC is linked',
    $roadmap,
    '../proposals/OPTIONAL_ADMIN_SEO_CONTROL_LAYER_RFC.md',
);
stack8AssertContains(
    'active Admin RFC remains framework-neutral and not a UI implementation',
    $roadmap,
    'orchestration/control API',
);
stack8AssertContains('active Admin RFC is not a UI implementation', $roadmap, 'not a UI implementation');
stack8AssertContains(
    'views, controllers, and routes remain outside the RFC scope',
    $roadmap,
    'and routes remain outside the proposal',
);

$sitemapExample = stack8Read('examples/sitemap-output.php');
stack8AssertContains('sitemap example uses a valid publication date', $sitemapExample, "publicationDate: '2026-07-01'");
stack8AssertNotContains('sitemap example has no stale invalid publication date', $sitemapExample, "publicationDate: '2026-07-32'");

fwrite(STDOUT, "Stack 8 documentation truth synchronization tests passed.\n");
