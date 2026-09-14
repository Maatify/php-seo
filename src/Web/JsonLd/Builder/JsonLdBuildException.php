<?php

declare(strict_types=1);

namespace Maatify\Seo\Web\JsonLd\Builder;

use Maatify\Seo\Exception\SeoExceptionInterface;
use Maatify\Exceptions\Contracts\ErrorCodeInterface;
use Maatify\Exceptions\Enum\ErrorCodeEnum;
use Maatify\Exceptions\Exception\System\SystemMaatifyException;

final class JsonLdBuildException extends SystemMaatifyException implements SeoExceptionInterface
{
    public static function encodingFailed(\JsonException $exception): self
    {
        return new self(
            'JSON-LD schema encoding failed: ' . $exception->getMessage(),
            $exception->getCode(),
            $exception
        );
    }

    protected function defaultErrorCode(): ErrorCodeInterface
    {
        return ErrorCodeEnum::MAATIFY_ERROR;
    }
}
