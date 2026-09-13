<?php

declare(strict_types=1);

namespace Maatify\Seo\Web\Indexing\SearchConsole\Exception;

use Maatify\Seo\Exception\SeoExceptionInterface;
use Maatify\Exceptions\Exception\MaatifyException;

abstract class SearchConsoleException extends MaatifyException implements SeoExceptionInterface
{
}
