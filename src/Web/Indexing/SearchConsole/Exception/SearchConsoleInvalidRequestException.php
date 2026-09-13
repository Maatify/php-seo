<?php

declare(strict_types=1);

namespace Maatify\Seo\Web\Indexing\SearchConsole\Exception;

use Maatify\Exceptions\Contracts\ErrorCategoryInterface;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;

final class SearchConsoleInvalidRequestException extends SearchConsoleException
{
    public static function forField(string $field, string $reason): self
    {
        return new self("Search Console request field [{$field}] is invalid: {$reason}.");
    }

    protected function defaultErrorCode(): ErrorCodeInterface
    {
        return ErrorCodeEnum::INVALID_ARGUMENT;
    }

    protected function defaultCategory(): ErrorCategoryInterface
    {
        return ErrorCategoryEnum::VALIDATION;
    }

    protected function defaultHttpStatus(): int
    {
        return 400;
    }

    protected function defaultIsSafe(): bool
    {
        return true;
    }
}
