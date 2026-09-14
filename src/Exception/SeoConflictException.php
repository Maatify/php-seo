<?php

declare(strict_types=1);

namespace Maatify\Seo\Exception;

use Maatify\Exceptions\Exception\Conflict\GenericConflictMaatifyException;

final class SeoConflictException extends GenericConflictMaatifyException implements SeoExceptionInterface
{
    public static function dueToReason(string $reason): self
    {
        return new self("Seo conflict occurred: {$reason}", SeoErrorCode::CONFLICT_GENERIC);
    }
}
