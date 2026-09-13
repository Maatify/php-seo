<?php

declare(strict_types=1);

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (! is_file($autoloadPath)) {
    throw new RuntimeException(sprintf(
        'Composer autoloader is missing at [%s]. Run composer install before executing standalone tests.',
        $autoloadPath,
    ));
}

require_once $autoloadPath;

function testRuntimeIsString(mixed $value): bool
{
    return is_string($value);
}
