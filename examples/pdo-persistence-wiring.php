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

use Maatify\Seo\Shared\Command\CreateRedirectCommand;
use Maatify\Seo\Shared\Infrastructure\Persistence\PdoRedirectRepository;
use Maatify\Seo\Shared\Service\RedirectCommandService;
use Maatify\Seo\Shared\Service\RedirectQueryService;

$dsn = getenv('MAATIFY_SEO_TEST_DB_DSN');
$user = getenv('MAATIFY_SEO_TEST_DB_USER');
$password = getenv('MAATIFY_SEO_TEST_DB_PASSWORD');
if (
    !is_string($dsn)
    || trim($dsn) === ''
    || !is_string($user)
    || trim($user) === ''
    || !is_string($password)
    || trim($password) === ''
) {
    fwrite(STDOUT, "Skipped: provide the dedicated MySQL test database settings and install maa_seo_redirects first.\n");
    return;
}

if (!str_starts_with($dsn, 'mysql:')
    || !preg_match('/(?:^|[;:])host=(?:127\.0\.0\.1|localhost)(?:;|$)/', $dsn)
    || !preg_match('/(?:^|;)dbname=[A-Za-z0-9_]+_test(?:;|$)/', $dsn)
    || !preg_match('/(?:^|;)charset=utf8mb4(?:;|$)/', $dsn)
) {
    throw new RuntimeException('This example requires a local MySQL test database using utf8mb4.');
}

$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$repository = new PdoRedirectRepository($pdo);
$commands = new RedirectCommandService($repository);
$queries = new RedirectQueryService($repository);

$requestedSlug = '/wu-11-pdo-example-' . bin2hex(random_bytes(6));
$redirectId = null;
try {
    $redirectId = $commands->create(new CreateRedirectCommand(
        entityType: 'article',
        languageId: 1,
        requestedSlug: $requestedSlug,
        targetEntityType: 'article',
        targetEntityId: 'demo-42',
    ));
    $redirect = $queries->getById($redirectId);

    echo json_encode([
        'id' => $redirect->id,
        'entity_type' => $redirect->entityType,
        'language_id' => $redirect->languageId,
        'requested_slug' => $redirect->requestedSlug,
        'target_entity_type' => $redirect->targetEntityType,
        'target_entity_id' => $redirect->targetEntityId,
        'http_status' => $redirect->httpStatus,
        'created_at' => $redirect->createdAt,
        'deleted_at' => $redirect->deletedAt,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
} finally {
    if ($redirectId !== null) {
        $commands->hardDelete($redirectId);
    }
}
