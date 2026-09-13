<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Exception\Conflict\GenericConflictMaatifyException;
use Maatify\Exceptions\Exception\MaatifyException;
use Maatify\Exceptions\Exception\NotFound\ResourceNotFoundMaatifyException;
use Maatify\Exceptions\Exception\System\SystemMaatifyException;
use Maatify\Exceptions\Exception\Validation\InvalidArgumentMaatifyException;
use Maatify\Seo\Exception\SeoCodeAlreadyExistsException;
use Maatify\Seo\Exception\SeoConflictException;
use Maatify\Seo\Exception\SeoExceptionInterface;
use Maatify\Seo\Exception\SeoInvalidArgumentException;
use Maatify\Seo\Exception\SeoNotFoundException;
use Maatify\Seo\Web\Indexing\SearchConsole\Exception\SearchConsoleException;
use Maatify\Seo\Web\Indexing\SearchConsole\Exception\SearchConsoleInvalidRequestException;
use Maatify\Seo\Web\Indexing\SearchConsole\Exception\SearchConsoleMalformedResponseException;
use Maatify\Seo\Web\Indexing\SearchConsole\Exception\SearchConsoleTransportException;
use Maatify\Seo\Web\JsonLd\Builder\JsonLdBuildException;
use Maatify\Seo\Web\MerchantCenter\Exception\MerchantCenterException;
use Maatify\Seo\Web\MerchantCenter\Exception\MerchantCenterInvalidRequestException;
use Maatify\Seo\Web\MerchantCenter\Exception\MerchantCenterMalformedResponseException;
use Maatify\Seo\Web\MerchantCenter\Exception\MerchantCenterTransportException;

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf(
            '%s. Expected %s, got %s.',
            $message,
            var_export($expected, true),
            var_export($actual, true),
        ));
    }
}

function assertTrueValue(bool $actual, string $message): void
{
    assertSameValue(true, $actual, $message);
}

function assertSeoMarker(\Throwable $exception, string $label): void
{
    assertTrueValue($exception instanceof SeoExceptionInterface, "{$label} implements the SEO marker");
}

function assertTaxonomy(
    SeoExceptionInterface $exception,
    ErrorCodeEnum $errorCode,
    ErrorCategoryEnum $category,
    int $httpStatus,
    bool $safe,
    string $label,
): void {
    assertSameValue($errorCode, $exception->getErrorCode(), "{$label} error code");
    assertSameValue($category, $exception->getCategory(), "{$label} category");
    assertSameValue($httpStatus, $exception->getHttpStatus(), "{$label} HTTP status");
    assertSameValue($safe, $exception->isSafe(), "{$label} safe flag");
    assertSameValue(false, $exception->isRetryable(), "{$label} retryable default");
}

$invalidArgumentFactories = [
    [SeoInvalidArgumentException::emptyField('title'), 1001, 'Field [title] must not be empty.'],
    [SeoInvalidArgumentException::invalidId('id'), 1002, 'Field [id] must be a positive integer >= 1.'],
    [SeoInvalidArgumentException::invalidHttpStatus(200), 1003, 'HTTP status [200] is invalid for SEO redirects.'],
    [SeoInvalidArgumentException::invalidSchemaEntry('schema'), 1001, 'Field [schema] must be a non-empty associative JSON-LD schema array or JsonLdSchemaDTO.'],
    [SeoInvalidArgumentException::invalidUrl('invalid-url'), 1004, 'URL [invalid-url] is invalid.'],
    [SeoInvalidArgumentException::invalidValue('canonical', 'must be absolute'), 1005, 'Field [canonical] is invalid: must be absolute'],
];

assertTrueValue(
    is_subclass_of(SeoInvalidArgumentException::class, InvalidArgumentMaatifyException::class),
    'SeoInvalidArgumentException uses the shared validation parent',
);

foreach ($invalidArgumentFactories as $index => [$exception, $legacyCode, $message]) {
    assertSeoMarker($exception, "SEO invalid argument {$index}");
    assertTaxonomy(
        $exception,
        ErrorCodeEnum::INVALID_ARGUMENT,
        ErrorCategoryEnum::VALIDATION,
        400,
        true,
        "SEO invalid argument {$index}",
    );
    assertSameValue($legacyCode, $exception->getCode(), "SEO invalid argument {$index} legacy code");
    assertSameValue($message, $exception->getMessage(), "SEO invalid argument {$index} message");
}

assertTrueValue(
    is_subclass_of(SeoNotFoundException::class, ResourceNotFoundMaatifyException::class),
    'SeoNotFoundException uses the shared not-found parent',
);

$notFoundById = SeoNotFoundException::withId(17);
assertSeoMarker($notFoundById, 'SEO not found by id');
assertTaxonomy($notFoundById, ErrorCodeEnum::RESOURCE_NOT_FOUND, ErrorCategoryEnum::NOT_FOUND, 404, true, 'SEO not found by id');
assertSameValue(2001, $notFoundById->getCode(), 'SEO not found by id legacy code');
assertSameValue('Seo record with id [17] not found.', $notFoundById->getMessage(), 'SEO not found by id message');

$notFoundByCode = SeoNotFoundException::withCode('home-page');
assertSeoMarker($notFoundByCode, 'SEO not found by code');
assertTaxonomy($notFoundByCode, ErrorCodeEnum::RESOURCE_NOT_FOUND, ErrorCategoryEnum::NOT_FOUND, 404, true, 'SEO not found by code');
assertSameValue(2002, $notFoundByCode->getCode(), 'SEO not found by code legacy code');
assertSameValue('Seo record with code [home-page] not found.', $notFoundByCode->getMessage(), 'SEO not found by code message');

assertTrueValue(
    is_subclass_of(SeoConflictException::class, GenericConflictMaatifyException::class),
    'SeoConflictException uses the shared conflict parent',
);

$conflict = SeoConflictException::dueToReason('duplicate route');
assertSeoMarker($conflict, 'SEO conflict');
assertTaxonomy($conflict, ErrorCodeEnum::CONFLICT, ErrorCategoryEnum::CONFLICT, 409, true, 'SEO conflict');
assertSameValue(4001, $conflict->getCode(), 'SEO conflict legacy code');
assertSameValue('Seo conflict occurred: duplicate route', $conflict->getMessage(), 'SEO conflict message');

assertTrueValue(
    is_subclass_of(SeoCodeAlreadyExistsException::class, GenericConflictMaatifyException::class),
    'SeoCodeAlreadyExistsException uses the shared conflict parent',
);

$codeAlreadyExists = SeoCodeAlreadyExistsException::forCode('article');
assertSeoMarker($codeAlreadyExists, 'SEO code already exists');
assertTaxonomy($codeAlreadyExists, ErrorCodeEnum::CONFLICT, ErrorCategoryEnum::CONFLICT, 409, true, 'SEO code already exists');
assertSameValue(3001, $codeAlreadyExists->getCode(), 'SEO code already exists legacy code');
assertSameValue('Seo record with code [article] already exists.', $codeAlreadyExists->getMessage(), 'SEO code already exists message');

$uniqueKeyAlreadyExists = SeoCodeAlreadyExistsException::forUniqueKey('redirect:article');
assertSeoMarker($uniqueKeyAlreadyExists, 'SEO unique key already exists');
assertSameValue(3001, $uniqueKeyAlreadyExists->getCode(), 'SEO unique key legacy code');
assertSameValue('Seo record with unique key [redirect:article] already exists.', $uniqueKeyAlreadyExists->getMessage(), 'SEO unique key message');

assertTrueValue(is_subclass_of(SearchConsoleException::class, MaatifyException::class), 'Search Console family extends MaatifyException');
assertTrueValue(is_subclass_of(MerchantCenterException::class, MaatifyException::class), 'Merchant Center family extends MaatifyException');

$searchConsoleInvalidRequest = SearchConsoleInvalidRequestException::forField('siteUrl', 'must be an absolute URL');
assertTrueValue($searchConsoleInvalidRequest instanceof SearchConsoleException, 'Search Console invalid request remains catchable by its family');
assertSeoMarker($searchConsoleInvalidRequest, 'Search Console invalid request');
assertTaxonomy($searchConsoleInvalidRequest, ErrorCodeEnum::INVALID_ARGUMENT, ErrorCategoryEnum::VALIDATION, 400, true, 'Search Console invalid request');

$searchConsoleMalformedResponse = SearchConsoleMalformedResponseException::forPath('inspectionResult', 'is missing');
assertTrueValue($searchConsoleMalformedResponse instanceof SearchConsoleException, 'Search Console malformed response remains catchable by its family');
assertSeoMarker($searchConsoleMalformedResponse, 'Search Console malformed response');
assertTaxonomy($searchConsoleMalformedResponse, ErrorCodeEnum::MAATIFY_ERROR, ErrorCategoryEnum::SYSTEM, 500, false, 'Search Console malformed response');

$searchConsolePrevious = new RuntimeException('provider connection detail');
$searchConsoleTransport = new SearchConsoleTransportException('Search Console transport failed.', 403, $searchConsolePrevious);
assertTrueValue($searchConsoleTransport instanceof SearchConsoleException, 'Search Console transport remains catchable by its family');
assertSeoMarker($searchConsoleTransport, 'Search Console transport');
assertTaxonomy($searchConsoleTransport, ErrorCodeEnum::MAATIFY_ERROR, ErrorCategoryEnum::SYSTEM, 500, false, 'Search Console transport');
assertSameValue(403, $searchConsoleTransport->httpStatus, 'Search Console provider HTTP status');
assertSameValue($searchConsolePrevious, $searchConsoleTransport->getPrevious(), 'Search Console previous exception');
assertSameValue('Search Console provider request failed with HTTP status [429].', SearchConsoleTransportException::forHttpStatus(429)->getMessage(), 'Search Console transport factory message');
assertSameValue(429, SearchConsoleTransportException::forHttpStatus(429)->httpStatus, 'Search Console transport factory provider status');

$merchantCenterInvalidRequest = MerchantCenterInvalidRequestException::forField('offerId', 'must not be empty');
assertTrueValue($merchantCenterInvalidRequest instanceof MerchantCenterException, 'Merchant Center invalid request remains catchable by its family');
assertSeoMarker($merchantCenterInvalidRequest, 'Merchant Center invalid request');
assertTaxonomy($merchantCenterInvalidRequest, ErrorCodeEnum::INVALID_ARGUMENT, ErrorCategoryEnum::VALIDATION, 400, true, 'Merchant Center invalid request');

$merchantCenterMalformedResponse = MerchantCenterMalformedResponseException::forPath('product', 'is malformed');
assertTrueValue($merchantCenterMalformedResponse instanceof MerchantCenterException, 'Merchant Center malformed response remains catchable by its family');
assertSeoMarker($merchantCenterMalformedResponse, 'Merchant Center malformed response');
assertTaxonomy($merchantCenterMalformedResponse, ErrorCodeEnum::MAATIFY_ERROR, ErrorCategoryEnum::SYSTEM, 500, false, 'Merchant Center malformed response');

$merchantCenterPrevious = new RuntimeException('provider response detail');
$merchantCenterTransport = new MerchantCenterTransportException('Merchant Center transport failed.', 403, $merchantCenterPrevious);
assertTrueValue($merchantCenterTransport instanceof MerchantCenterException, 'Merchant Center transport remains catchable by its family');
assertSeoMarker($merchantCenterTransport, 'Merchant Center transport');
assertTaxonomy($merchantCenterTransport, ErrorCodeEnum::MAATIFY_ERROR, ErrorCategoryEnum::SYSTEM, 500, false, 'Merchant Center transport');
assertSameValue(403, $merchantCenterTransport->httpStatus, 'Merchant Center provider HTTP status');
assertSameValue($merchantCenterPrevious, $merchantCenterTransport->getPrevious(), 'Merchant Center previous exception');
assertSameValue('Merchant Center provider request failed with HTTP status [429].', MerchantCenterTransportException::forHttpStatus(429)->getMessage(), 'Merchant Center transport factory message');
assertSameValue(429, MerchantCenterTransportException::forHttpStatus(429)->httpStatus, 'Merchant Center transport factory provider status');

$jsonException = new JsonException('invalid UTF-8 sequence', 73);
$jsonLdBuild = JsonLdBuildException::encodingFailed($jsonException);
assertSeoMarker($jsonLdBuild, 'JSON-LD build exception');
assertTrueValue($jsonLdBuild instanceof SystemMaatifyException, 'JSON-LD build exception uses the shared system parent');
assertTaxonomy($jsonLdBuild, ErrorCodeEnum::MAATIFY_ERROR, ErrorCategoryEnum::SYSTEM, 500, false, 'JSON-LD build exception');
assertSameValue('JSON-LD schema encoding failed: invalid UTF-8 sequence', $jsonLdBuild->getMessage(), 'JSON-LD build exception message');
assertSameValue(73, $jsonLdBuild->getCode(), 'JSON-LD build exception preserves the JsonException code');
assertSameValue($jsonException, $jsonLdBuild->getPrevious(), 'JSON-LD build exception preserves the JsonException');

fwrite(STDOUT, "Exception architecture tests passed.\n");
