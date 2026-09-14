<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Maatify\Seo\Web\Validation\DTO\SeoValidationContextDTO;
use Maatify\Seo\Web\Validation\Input\Hreflang\HreflangValidationClusterDTO;
use Maatify\Seo\Web\Validation\Input\Hreflang\HreflangValidationLinkDTO;
use Maatify\Seo\Web\Validation\Input\Hreflang\HreflangValidationPageDTO;
use Maatify\Seo\Web\Validation\Input\RobotsMetaValidationInputDTO;
use Maatify\Seo\Web\Validation\Input\RobotsTxtValidationInputDTO;
use Maatify\Seo\Web\Validation\Input\Sitemap\SitemapImageValidationInputDTO;
use Maatify\Seo\Web\Validation\Input\Sitemap\SitemapValidationDocumentDTO;
use Maatify\Seo\Web\Validation\Input\Sitemap\SitemapUrlValidationInputDTO;
use Maatify\Seo\Web\Validation\Profile\GoogleCanonicalValidator;
use Maatify\Seo\Web\Validation\Profile\GoogleHreflangClusterValidator;
use Maatify\Seo\Web\Validation\Profile\GoogleImageSitemapValidator;
use Maatify\Seo\Web\Validation\Profile\GoogleRobotsMetaValidator;
use Maatify\Seo\Web\Validation\Profile\GoogleRobotsTxtValidator;
use Maatify\Seo\Web\Validation\Profile\GoogleSitemapValidator;
use Maatify\Seo\Web\Validation\Profile\OpenGraphProtocolValidator;
use Maatify\Seo\Web\Validation\Profile\Rfc9309RobotsValidator;
use Maatify\Seo\Web\Validation\Profile\SitemapProtocolValidator;

$context = new SeoValidationContextDTO([
    'robots_meta.unavailable_after_recognizability' => 'unknown',
    'google_sitemap.host_verification' => 'unknown',
]);
$recognizedContext = new SeoValidationContextDTO([
    'robots_meta.unavailable_after_recognizability' => 'recognized',
]);
$unrecognizedContext = new SeoValidationContextDTO([
    'robots_meta.unavailable_after_recognizability' => 'unrecognized',
]);

$robotsInput = new RobotsTxtValidationInputDTO("User-agent: *\nDisallow: *\nCrawl-delay: 2\n");
$robotsMetaInput = new RobotsMetaValidationInputDTO(['unavailable_after:31-Dec-2026 23:59:59 GMT']);

$sitemapDocument = new SitemapValidationDocumentDTO('urlset', [
    new SitemapUrlValidationInputDTO(
        loc: 'https://example.com/guides/seo',
        images: [new SitemapImageValidationInputDTO(loc: 'https://cdn.example.com/images/seo.png')],
    ),
]);

$hreflangCluster = new HreflangValidationClusterDTO([
    new HreflangValidationPageDTO('https://example.com/en/seo', [
        new HreflangValidationLinkDTO('en', 'https://example.com/en/seo'),
        new HreflangValidationLinkDTO('fr', 'https://example.com/fr/seo'),
    ]),
    new HreflangValidationPageDTO('https://example.com/fr/seo', [
        new HreflangValidationLinkDTO('en', 'https://example.com/en/seo'),
        new HreflangValidationLinkDTO('fr', 'https://example.com/fr/seo'),
    ]),
]);

$results = [
    'rfc9309_robots' => (new Rfc9309RobotsValidator())->validate($robotsInput, $context),
    'google_robots_txt' => (new GoogleRobotsTxtValidator())->validate($robotsInput, $context),
    'google_robots_meta_recognized' => (new GoogleRobotsMetaValidator())->validate($robotsMetaInput, $recognizedContext),
    'google_robots_meta_unrecognized' => (new GoogleRobotsMetaValidator())->validate($robotsMetaInput, $unrecognizedContext),
    'google_robots_meta_unknown' => (new GoogleRobotsMetaValidator())->validate($robotsMetaInput, $context),
    'sitemap_protocol' => (new SitemapProtocolValidator())->validate($sitemapDocument, $context),
    'google_sitemap' => (new GoogleSitemapValidator())->validate($sitemapDocument, $context),
    'google_image_sitemap' => (new GoogleImageSitemapValidator())->validate($sitemapDocument, $context),
    'google_canonical' => (new GoogleCanonicalValidator())->validate('/guides/seo', $context),
    'google_hreflang' => (new GoogleHreflangClusterValidator())->validate($hreflangCluster, $context),
    'open_graph' => (new OpenGraphProtocolValidator())->validate([
        'openGraph' => ['title' => 'SEO guide'],
    ], context: $context),
];

$summary = [];
foreach ($results as $profile => $result) {
    $summary[$profile] = array_map(
        static fn ($diagnostic): array => [
            'code' => $diagnostic->code,
            'severity' => $diagnostic->severity,
            'origin' => $diagnostic->origin,
            'profile' => $diagnostic->profile,
            'field' => $diagnostic->field,
            'evidence_state' => $diagnostic->evidenceState,
        ],
        $result->diagnostics,
    );
}

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
