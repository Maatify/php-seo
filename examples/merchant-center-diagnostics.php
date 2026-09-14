<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Maatify\Seo\Web\MerchantCenter\DTO\MerchantCenterProductRequestDTO;
use Maatify\Seo\Web\MerchantCenter\DTO\MerchantCenterTransportResponseDTO;
use Maatify\Seo\Web\MerchantCenter\Mapper\MerchantCenterResponseMapper;
use Maatify\Seo\Web\MerchantCenter\MerchantCenterDiagnosticsService;
use Maatify\Seo\Web\MerchantCenter\MerchantCenterTransportInterface;

final class SampleMerchantCenterTransport implements MerchantCenterTransportInterface
{
    /** @param array<string, mixed> $samplePayload */
    public function __construct(private array $samplePayload)
    {
    }

    public function getProduct(MerchantCenterProductRequestDTO $request): MerchantCenterTransportResponseDTO
    {
        echo 'Transport received product resource: ' . $request->name . "\n";

        return new MerchantCenterTransportResponseDTO(200, $this->samplePayload);
    }

    public function listAggregateProductStatuses(
        \Maatify\Seo\Web\MerchantCenter\DTO\MerchantCenterAggregateRequestDTO $request,
    ): MerchantCenterTransportResponseDTO {
        return new MerchantCenterTransportResponseDTO(200, ['aggregateProductStatuses' => []]);
    }
}

// Local sample fixture only; it does not assert real account/product eligibility.
$sampleProviderPayload = [
    'name' => 'accounts/123/products/en~US~sku123',
    'productStatus' => [
        'destinationStatuses' => [[
            'reportingContext' => 'SHOPPING_ADS',
            'approvedCountries' => ['US'],
            'pendingCountries' => [],
            'disapprovedCountries' => [],
        ]],
        'itemLevelIssues' => [[
            'code' => 'missing_value',
            'severity' => 'ERROR',
            'resolution' => 'MERCHANT_ACTION',
            'attribute' => 'title',
            'reportingContext' => 'SHOPPING_ADS',
            'description' => 'A title is missing.',
            'detail' => 'Add a title to the product.',
            'documentation' => 'https://support.google.com/merchants/answer/example',
            'applicableCountries' => ['US', 'CA'],
        ]],
        'creationDate' => '2026-09-01T10:00:00Z',
        'lastUpdateDate' => '2026-09-08T12:00:00Z',
        'googleExpirationDate' => '2026-10-08T12:00:00Z',
    ],
];

$service = new MerchantCenterDiagnosticsService(
    new SampleMerchantCenterTransport($sampleProviderPayload),
    new MerchantCenterResponseMapper(),
);

$result = $service->getProductDiagnostics(new MerchantCenterProductRequestDTO(
    name: 'accounts/123/products/en~US~sku123',
));

echo "Mapped package DTO fields:\n";
echo json_encode([
    'productName' => $result->productName,
    'destinationStatuses' => array_map(
        static fn ($status): array => [
            'reportingContext' => $status->reportingContext,
            'approvedCountries' => $status->approvedCountries,
            'pendingCountries' => $status->pendingCountries,
            'disapprovedCountries' => $status->disapprovedCountries,
        ],
        $result->destinationStatuses,
    ),
    'itemLevelIssues' => array_map(
        static fn ($issue): array => [
            'code' => $issue->code,
            'severity' => $issue->severity,
            'resolution' => $issue->resolution,
            'attribute' => $issue->attribute,
            'description' => $issue->description,
            'detail' => $issue->detail,
            'applicableCountries' => $issue->applicableCountries,
        ],
        $result->itemLevelIssues,
    ),
    'creationDate' => $result->creationDate,
    'lastUpdateDate' => $result->lastUpdateDate,
    'googleExpirationDate' => $result->googleExpirationDate,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
