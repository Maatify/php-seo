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
    'maa_seo_slug_history',
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
