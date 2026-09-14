<?php

declare(strict_types=1);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'Maatify\\Seo\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $path = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($path)) {
            require $path;
        }
    });
}

use Maatify\Seo\Web\Validation\SeoValidationBatchReportBuilder;
use Maatify\Seo\Web\Validation\SeoValidationBatchReportExporter;
use Maatify\Seo\Web\Validation\SeoValidationReportBuilder;
use Maatify\Seo\Web\Validation\SeoValidationReportExporter;

$report = SeoValidationReportBuilder::build(
    meta: ['title' => '', 'description' => 'Short'],
    context: [
        'url' => 'https://example.com/products/42',
        'entityType' => 'product',
        'entityId' => 42,
        'source' => 'admin-audit',
    ],
);

echo "\n==============================\nSingle report DTO JSON\n==============================\n";
echo SeoValidationReportExporter::toJson($report) . "\n";
echo "\nSingle report summary array\n";
echo json_encode(SeoValidationReportExporter::toSummaryArray($report), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
echo "\nSingle report Markdown\n";
echo SeoValidationReportExporter::toMarkdown($report);

$validMetadata = [
    'title' => 'A useful product page title',
    'description' => 'This useful product page description is long enough for ordinary search result snippets.',
    'canonical' => 'https://example.com/products/useful',
    'robots' => 'index,follow',
];
$batch = SeoValidationBatchReportBuilder::build(
    items: [
        [
            'meta' => $validMetadata,
            'context' => [
                'url' => 'https://example.com/products/useful',
                'entityType' => 'product',
                'entityId' => 42,
            ],
        ],
        [
            'meta' => ['title' => 'A useful product page title'],
            'context' => [
                'url' => 'https://example.com/products/missing-description',
                'entityType' => 'product',
                'entityId' => 43,
            ],
        ],
    ],
    sharedContext: ['language' => 'en', 'source' => 'admin-audit'],
);

echo "\n==============================\nBatch report DTO JSON\n==============================\n";
echo SeoValidationBatchReportExporter::toJson($batch) . "\n";
echo "\nBatch report summary array\n";
echo json_encode(SeoValidationBatchReportExporter::toSummaryArray($batch), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
echo "\nBatch report Markdown\n";
echo SeoValidationBatchReportExporter::toMarkdown($batch);
