<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use Maatify\Seo\Admin\DTO\SeoMetadataImportResultDTO;
use Maatify\Seo\Admin\DTO\SerpPreviewDTO;
use Maatify\Seo\Admin\DTO\SocialPreviewDTO;
use Maatify\Seo\Admin\Export\SeoMetadataExporter;
use Maatify\Seo\Admin\Import\SeoMetadataImporter;
use Maatify\Seo\Admin\Preview\SerpPreviewFactory;
use Maatify\Seo\Admin\Preview\SocialPreviewFactory;
use Maatify\Seo\Shared\DTO\MetaTagsDTO;
use Maatify\Seo\Shared\DTO\RedirectDTO;
use Maatify\Seo\Shared\DTO\SeoOverride\SeoOverrideDTO;
use Maatify\Seo\Web\Page\SeoPagePresetOutputDTO;

final class Batch2TestFailureCounter { public static int $count = 0; }
function ok(bool $value, string $message): void { if (!$value) { Batch2TestFailureCounter::$count++; echo "FAIL: $message\n"; } }
function same(mixed $expected, mixed $actual, string $message): void { ok($expected === $actual, $message); }

echo "Running Batch 2 Admin Previews & Migrations Tests...\n\n";

$serp = new SerpPreviewDTO('Title', 'Description', 'https://example.com/a', 'example.com/a', 'index,follow', ['note'], 90, 'ok');
same('Title', $serp->toArray()['title'], 'SERP DTO serializes title');
same('example.com/a', $serp->jsonSerialize()['display_url'], 'SERP DTO serializes display URL');

$social = new SocialPreviewDTO('Title', 'Description', 'https://example.com/i.jpg', 'https://example.com/a', 'article', 'Example', 'summary_large_image', ['note']);
same('https://example.com/i.jpg', $social->toArray()['image_url'], 'Social DTO serializes image URL');
same('summary_large_image', $social->jsonSerialize()['twitter_card'], 'Social DTO serializes Twitter card');

$meta = new MetaTagsDTO('Preset Title', 'Preset Description', 'https://example.com/preset', 'index, follow', openGraphTitle: 'OG Title', openGraphUrl: 'https://example.com/og', openGraphType: 'article', openGraphImage: 'https://example.com/og.jpg', twitterCard: 'summary_large_image');
$preset = new SeoPagePresetOutputDTO($meta, 'https://example.com/preset', 'index, follow');
same('example.com/preset', SerpPreviewFactory::fromPreset($preset)->displayUrl, 'SERP factory builds from SeoPagePresetOutputDTO');
same('OG Title', SocialPreviewFactory::fromPreset($preset, 'Example')->title, 'Social factory builds from SeoPagePresetOutputDTO');

$exporter = new SeoMetadataExporter();
$export = $exporter->export(
    [new SeoOverrideDTO(1, 'product', '10', 1, 'Meta', 'Desc', '2026-01-01', '2026-01-01', null)],
    [new RedirectDTO(1, 'product', 1, 'old', 'product', '10', 301, '2026-01-01', null)]
);
same('2.0', $export->toArray()['schema_version'], 'Exporter produces the current versioned output');
same(1, count($export->toArray()['data']['redirects']), 'Exporter includes redirects');
same(['seo_overrides', 'redirects'], array_keys($export->toArray()['data']), 'Exporter includes only SEO overrides and redirects');
$json = $exporter->toJson($export);
ok(json_decode($json, true) !== null, 'Exporter JSON output is valid');

$importer = new SeoMetadataImporter();
$bad = $importer->importArray(['schema_version' => 'bad']);
ok($bad->failed > 0 && $bad->errors !== [], 'Importer validates malformed payloads');
$dryRun = $importer->importArray($export->toArray(), true);
same(2, $dryRun->created, 'Importer dry-run counts only SEO override and redirect rows');
same(true, $dryRun->dryRun, 'Importer dry-run flag is preserved');
$result = new SeoMetadataImportResultDTO(1, 2, 3, 4, ['err'], true);
same(2, $result->toArray()['updated'], 'Import result DTO serializes updated count');

$source = file_get_contents(__DIR__ . '/../src/Admin/Export/SeoMetadataExporter.php') . file_get_contents(__DIR__ . '/../src/Admin/Import/SeoMetadataImporter.php');
ok(testRuntimeIsString($source) && !str_contains($source, 'Illuminate\\') && !str_contains($source, 'Symfony\\') && !str_contains($source, 'Response'), 'Admin migration helpers have no framework/HTTP coupling strings');

echo "\n";
if (Batch2TestFailureCounter::$count > 0) { echo "FAILED with " . Batch2TestFailureCounter::$count . " errors.\n"; exit(1); }
echo "SUCCESS: All tests passed.\n"; exit(0);
