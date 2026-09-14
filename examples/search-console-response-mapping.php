<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Maatify\Seo\Web\Indexing\SearchConsole\DTO\SearchConsoleInspectionRequestDTO;
use Maatify\Seo\Web\Indexing\SearchConsole\DTO\SearchConsoleTransportResponseDTO;
use Maatify\Seo\Web\Indexing\SearchConsole\Mapper\SearchConsoleResponseMapper;
use Maatify\Seo\Web\Indexing\SearchConsole\SearchConsoleInspectionService;
use Maatify\Seo\Web\Indexing\SearchConsole\SearchConsoleTransportInterface;

final class SampleSearchConsoleTransport implements SearchConsoleTransportInterface
{
    /** @param array<string, mixed> $samplePayload */
    public function __construct(private array $samplePayload)
    {
    }

    public function inspect(SearchConsoleInspectionRequestDTO $request): SearchConsoleTransportResponseDTO
    {
        echo 'Transport received inspection URL: ' . $request->inspectionUrl . "\n";

        return new SearchConsoleTransportResponseDTO(200, $this->samplePayload);
    }
}

// This local fixture illustrates a payload shape. It is not a live provider response.
$sampleProviderPayload = [
    'inspectionResult' => [
        'inspectionResultLink' => 'https://search.google.com/search-console/inspect?resource_id=https%3A%2F%2Fexample.com%2F',
        'indexStatusResult' => [
            'verdict' => 'PASS',
            'coverageState' => 'Submitted and indexed',
            'robotsTxtState' => 'ALLOWED',
            'indexingState' => 'INDEXING_ALLOWED',
            'lastCrawlTime' => '2026-09-08T12:00:00Z',
            'pageFetchState' => 'SUCCESSFUL',
            'googleCanonical' => 'https://example.com/guides/seo',
            'userCanonical' => 'https://example.com/guides/seo',
            'crawledAs' => 'MOBILE',
        ],
        'richResultsResult' => [
            'verdict' => 'PASS',
            'detectedItems' => [[
                'richResultType' => 'Article',
                'items' => [[
                    'name' => 'SEO guide',
                    'issues' => [],
                ]],
            ]],
        ],
    ],
];

$service = new SearchConsoleInspectionService(
    new SampleSearchConsoleTransport($sampleProviderPayload),
    new SearchConsoleResponseMapper(),
);

$result = $service->inspect(new SearchConsoleInspectionRequestDTO(
    inspectionUrl: 'https://example.com/guides/seo',
    siteUrl: 'https://example.com/',
    languageCode: 'en-US',
));

echo "Mapped package DTO:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
