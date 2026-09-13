<?php

declare(strict_types=1);

namespace Maatify\Seo\Web\MerchantCenter\Exception;

use Maatify\Seo\Exception\SeoExceptionInterface;
use Maatify\Exceptions\Exception\MaatifyException;

abstract class MerchantCenterException extends MaatifyException implements SeoExceptionInterface
{
}
