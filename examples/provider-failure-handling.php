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

use Maatify\Seo\Web\Indexing\SearchConsole\DTO\SearchConsoleInspectionRequestDTO;
use Maatify\Seo\Web\Indexing\SearchConsole\DTO\SearchConsoleTransportResponseDTO;
use Maatify\Seo\Web\Indexing\SearchConsole\Exception\SearchConsoleTransportException;
use Maatify\Seo\Web\Indexing\SearchConsole\Mapper\SearchConsoleResponseMapper;
use Maatify\Seo\Web\Indexing\SearchConsole\SearchConsoleInspectionService;
use Maatify\Seo\Web\Indexing\SearchConsole\SearchConsoleTransportInterface;

final class ExampleForbiddenSearchConsoleTransport implements SearchConsoleTransportInterface
{
    public function inspect(SearchConsoleInspectionRequestDTO $request): SearchConsoleTransportResponseDTO
    {
        return new SearchConsoleTransportResponseDTO(403, ['error' => 'provider permission denied']);
    }
}

$service = new SearchConsoleInspectionService(
    new ExampleForbiddenSearchConsoleTransport(),
    new SearchConsoleResponseMapper(),
);

try {
    $service->inspect(new SearchConsoleInspectionRequestDTO(
        'https://example.com/products/42',
        'https://example.com/',
    ));
} catch (SearchConsoleTransportException $exception) {
    // This Host example chooses 502 for its own API. The Host owns that policy.
    $hostResponse = [
        'provider_http_status' => $exception->httpStatus,
        'package_http_status' => $exception->getHttpStatus(),
        'host_http_status' => 502,
        'host_error_code' => 'search_console_upstream_failure',
    ];

    echo json_encode($hostResponse, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
}
