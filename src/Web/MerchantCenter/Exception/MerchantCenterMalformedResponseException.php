<?php

declare(strict_types=1);

namespace Maatify\Seo\Web\MerchantCenter\Exception;

use Maatify\Exceptions\Contracts\ErrorCategoryInterface;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCategoryEnum;
use Maatify\Exceptions\Enum\ErrorCodeEnum;

final class MerchantCenterMalformedResponseException extends MerchantCenterException
{
    public static function forPath(string $path, string $reason): self
    {
        return new self("Merchant Center response field [{$path}] is malformed: {$reason}.");
    }

    protected function defaultErrorCode(): ErrorCodeInterface
    {
        return ErrorCodeEnum::MAATIFY_ERROR;
    }

    protected function defaultCategory(): ErrorCategoryInterface
    {
        return ErrorCategoryEnum::SYSTEM;
    }

    protected function defaultHttpStatus(): int
    {
        return 500;
    }
}
