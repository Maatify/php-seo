<?php

declare(strict_types=1);

use Maatify\Seo\Exception\SeoCodeAlreadyExistsException;
use Maatify\Seo\Exception\SeoNotFoundException;
use Maatify\Seo\Shared\Command\CreateRedirectCommand;
use Maatify\Seo\Shared\Command\SeoOverride\CreateSeoOverrideCommand;
use Maatify\Seo\Shared\Command\SeoOverride\UpdateSeoOverrideCommand;
use Maatify\Seo\Shared\Command\UpdateRedirectCommand;
use Maatify\Seo\Shared\Infrastructure\Persistence\PdoRedirectRepository;
use Maatify\Seo\Shared\Infrastructure\Persistence\PdoSeoOverrideRepository;
use Maatify\Seo\Shared\Service\RedirectCommandService;
use Maatify\Seo\Shared\Service\RedirectQueryService;
use Maatify\Seo\Shared\Service\SeoOverrideCommandService;
use Maatify\Seo\Shared\Service\SeoOverrideQueryService;

require_once dirname(__DIR__) . '/bootstrap.php';

/** @return list<string> */
function mysqlPersistenceOwnedTables(): array
{
    return [
        'maa_seo_redirects',
        'maa_seo_overrides',
    ];
}

function mysqlPersistenceAssertSame(mixed $expected, mixed $actual, string $message): void
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

function mysqlPersistenceAssertTrue(bool $actual, string $message): void
{
    mysqlPersistenceAssertSame(true, $actual, $message);
}

/**
 * @param class-string<Throwable> $expectedClass
 * @param \Closure(): mixed        $operation
 */
function mysqlPersistenceAssertThrows(string $expectedClass, \Closure $operation, string $message): void
{
    try {
        $operation();
    } catch (Throwable $exception) {
        if (! $exception instanceof $expectedClass) {
            throw new RuntimeException(sprintf(
                '%s. Expected %s, got %s.',
                $message,
                $expectedClass,
                $exception::class,
            ), 0, $exception);
        }

        return;
    }

    throw new RuntimeException(sprintf('%s. Expected %s, but nothing was thrown.', $message, $expectedClass));
}

function mysqlPersistenceRequiredEnvironmentValue(string $name): string
{
    $value = getenv($name);
    if (! is_string($value) || trim($value) === '') {
        throw new RuntimeException(sprintf(
            'Required MySQL Integration environment variable [%s] is missing or empty.',
            $name,
        ));
    }

    return $value;
}

function mysqlPersistenceValidateDsn(string $dsn): void
{
    if (! str_starts_with($dsn, 'mysql:')) {
        throw new RuntimeException('MySQL Integration requires a PDO MySQL DSN beginning with [mysql:].');
    }

    $dsnOptions = substr($dsn, strlen('mysql:'));
    $requiredOptions = ['host', 'port', 'dbname', 'charset'];
    foreach ($requiredOptions as $option) {
        if (! preg_match('/(?:^|;)' . preg_quote($option, '/') . '=([^;]+)(?:;|$)/', $dsnOptions, $matches)) {
            throw new RuntimeException(sprintf('MySQL Integration DSN must include a non-empty [%s] option.', $option));
        }

        $value = $matches[1];
        if ($option === 'host' && ! in_array($value, ['127.0.0.1', 'localhost'], true)) {
            throw new RuntimeException('MySQL Integration DSN host must be local (127.0.0.1 or localhost).');
        }

        if ($option === 'port' && (! ctype_digit($value) || (int) $value < 1 || (int) $value > 65535)) {
            throw new RuntimeException('MySQL Integration DSN port must be between 1 and 65535.');
        }

        if ($option === 'dbname' && ! preg_match('/^[A-Za-z0-9_]+_test$/', $value)) {
            throw new RuntimeException('MySQL Integration DSN database name must be a dedicated name ending in [_test].');
        }

        if ($option === 'charset' && $value !== 'utf8mb4') {
            throw new RuntimeException('MySQL Integration DSN charset must be [utf8mb4].');
        }
    }
}

function mysqlPersistenceDropOwnedTables(PDO $pdo): void
{
    foreach (array_reverse(mysqlPersistenceOwnedTables()) as $table) {
        $pdo->exec(sprintf('DROP TABLE IF EXISTS `%s`', $table));
    }
}

function mysqlPersistenceVerifySchemas(PDO $pdo): void
{
    foreach (mysqlPersistenceOwnedTables() as $table) {
        $statement = $pdo->prepare(
            'SELECT ENGINE, TABLE_COLLATION FROM information_schema.TABLES '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name',
        );
        $statement->execute(['table_name' => $table]);

        /** @var array<string, mixed>|false $tableMetadata */
        $tableMetadata = $statement->fetch(PDO::FETCH_ASSOC);
        if ($tableMetadata === false) {
            throw new RuntimeException(sprintf('Shipped schema did not create table [%s].', $table));
        }

        mysqlPersistenceAssertSame('InnoDB', $tableMetadata['ENGINE'] ?? null, sprintf('Table [%s] uses InnoDB.', $table));
        mysqlPersistenceAssertSame(
            'utf8mb4_unicode_ci',
            $tableMetadata['TABLE_COLLATION'] ?? null,
            sprintf('Table [%s] uses utf8mb4_unicode_ci.', $table),
        );

        $foreignKeyStatement = $pdo->prepare(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name '
            . 'AND REFERENCED_TABLE_NAME IS NOT NULL',
        );
        $foreignKeyStatement->execute(['table_name' => $table]);

        /** @var list<array<string, mixed>> $foreignKeys */
        $foreignKeys = $foreignKeyStatement->fetchAll(PDO::FETCH_ASSOC);
        mysqlPersistenceAssertSame([], $foreignKeys, sprintf('Table [%s] has no foreign keys.', $table));
    }
}

function mysqlPersistenceAssertNoResidue(PDO $pdo): void
{
    $statement = $pdo->prepare(
        'SELECT TABLE_NAME FROM information_schema.TABLES '
        . 'WHERE TABLE_SCHEMA = DATABASE() '
        . 'AND TABLE_NAME IN (:redirect_table, :override_table)',
    );
    $statement->execute([
        'redirect_table' => 'maa_seo_redirects',
        'override_table' => 'maa_seo_overrides',
    ]);

    /** @var list<array<string, mixed>> $remainingTables */
    $remainingTables = $statement->fetchAll(PDO::FETCH_ASSOC);
    mysqlPersistenceAssertSame([], $remainingTables, 'No package-owned test tables remain after cleanup.');
}

function mysqlPersistenceVerifyRedirects(PDO $pdo): void
{
    $repository = new PdoRedirectRepository($pdo);
    $commands = new RedirectCommandService($repository);
    $queries = new RedirectQueryService($repository);

    $redirectId = $commands->create(new CreateRedirectCommand(
        'article',
        1,
        '/old-redirect',
        'article',
        'post-10',
    ));
    mysqlPersistenceAssertTrue($redirectId > 0, 'Redirect creation returns a positive inserted ID.');

    $redirect = $queries->getById($redirectId);
    mysqlPersistenceAssertSame('article', $redirect->entityType, 'Redirect entity type is persisted.');
    mysqlPersistenceAssertSame(1, $redirect->languageId, 'Redirect language is persisted.');
    mysqlPersistenceAssertSame('/old-redirect', $redirect->requestedSlug, 'Redirect requested slug is persisted.');
    mysqlPersistenceAssertSame('article', $redirect->targetEntityType, 'Redirect target type is persisted.');
    mysqlPersistenceAssertSame('post-10', $redirect->targetEntityId, 'Redirect target ID is persisted.');
    mysqlPersistenceAssertSame(301, $redirect->httpStatus, 'Redirect 301 status is persisted.');

    $activeRedirect = $queries->getActiveByRequestedSlug('article', 1, '/old-redirect');
    mysqlPersistenceAssertSame($redirectId, $activeRedirect->id, 'Active redirect lookup finds the persisted record.');

    $secondLanguageId = $commands->create(new CreateRedirectCommand(
        'article',
        2,
        '/old-redirect-fr',
        'article',
        'post-10-fr',
    ));
    mysqlPersistenceAssertTrue($secondLanguageId > 0, 'Second-language redirect creation returns a positive inserted ID.');

    mysqlPersistenceAssertSame(2, count($queries->listByEntity('article')), 'Redirect list-by-entity returns both active records.');
    $languageOneRedirects = $queries->listByEntity('article', 1);
    mysqlPersistenceAssertSame(1, count($languageOneRedirects), 'Redirect list-by-entity filters by language.');
    mysqlPersistenceAssertSame($redirectId, $languageOneRedirects[0]->id, 'Redirect language filter returns the matching record.');

    mysqlPersistenceAssertThrows(
        SeoCodeAlreadyExistsException::class,
        static function () use ($commands): void {
            $commands->create(new CreateRedirectCommand('article', 1, '/old-redirect', 'article', 'post-duplicate'));
        },
        'Duplicate redirect identity maps to SeoCodeAlreadyExistsException',
    );

    $commands->update(new UpdateRedirectCommand($redirectId, 'article', 'post-11', 301));
    $updatedRedirect = $queries->getById($redirectId);
    mysqlPersistenceAssertSame('post-11', $updatedRedirect->targetEntityId, 'Redirect update persists changed target values.');

    $goneId = $commands->create(new CreateRedirectCommand('article', 3, '/gone', null, null, 410));
    mysqlPersistenceAssertTrue($goneId > 0, '410 redirect creation returns a positive inserted ID.');
    $goneRedirect = $queries->getById($goneId);
    mysqlPersistenceAssertSame(410, $goneRedirect->httpStatus, 'Redirect 410 status is persisted.');
    mysqlPersistenceAssertSame(null, $goneRedirect->targetEntityType, '410 target entity type remains nullable.');
    mysqlPersistenceAssertSame(null, $goneRedirect->targetEntityId, '410 target entity ID remains nullable.');

    $commands->softDelete($redirectId);
    mysqlPersistenceAssertThrows(
        SeoNotFoundException::class,
        static function () use ($queries): void {
            $queries->getActiveByRequestedSlug('article', 1, '/old-redirect');
        },
        'Soft-deleted redirect disappears from active lookup',
    );
    mysqlPersistenceAssertSame([], $queries->listByEntity('article', 1), 'Soft-deleted redirect disappears from the default active list.');
    $redirectsIncludingDeleted = $queries->listByEntity('article', 1, true);
    mysqlPersistenceAssertSame(1, count($redirectsIncludingDeleted), 'Redirect list can include deleted records.');
    mysqlPersistenceAssertSame($redirectId, $redirectsIncludingDeleted[0]->id, 'Include-deleted redirect list exposes the soft-deleted record.');
    mysqlPersistenceAssertTrue(is_string($redirectsIncludingDeleted[0]->deletedAt), 'Soft-deleted redirect has a deletion timestamp.');

    mysqlPersistenceAssertThrows(
        SeoNotFoundException::class,
        static function () use ($commands, $redirectId): void {
            $commands->softDelete($redirectId);
        },
        'Repeating redirect soft delete uses the service missing-target behavior',
    );

    $commands->hardDelete($redirectId);
    mysqlPersistenceAssertThrows(
        SeoNotFoundException::class,
        static function () use ($queries, $redirectId): void {
            $queries->getById($redirectId);
        },
        'Hard-deleted redirect is no longer found by ID',
    );
}

function mysqlPersistenceVerifyOverrides(PDO $pdo): void
{
    $repository = new PdoSeoOverrideRepository($pdo);
    $commands = new SeoOverrideCommandService($repository);
    $queries = new SeoOverrideQueryService($repository);

    $overrideId = $commands->create(new CreateSeoOverrideCommand('article', 'article-10', 1, null, null));
    mysqlPersistenceAssertTrue($overrideId > 0, 'SEO override creation returns a positive inserted ID.');
    $override = $queries->getById($overrideId);
    mysqlPersistenceAssertSame(null, $override->metaTitle, 'Nullable override title is persisted.');
    mysqlPersistenceAssertSame(null, $override->metaDescription, 'Nullable override description is persisted.');

    mysqlPersistenceAssertSame($overrideId, $queries->getActiveForEntity('article', 'article-10', 1)->id, 'Active SEO override lookup finds the persisted record.');

    $secondLanguageId = $commands->create(new CreateSeoOverrideCommand(
        'article',
        'article-10',
        2,
        'Titre français',
        'Description française',
    ));
    mysqlPersistenceAssertTrue($secondLanguageId > 0, 'Second-language SEO override creation returns a positive inserted ID.');
    mysqlPersistenceAssertSame(2, count($queries->listByEntity('article', 'article-10')), 'SEO override list-by-entity returns both languages.');
    $languageOneOverrides = $queries->listByEntity('article', 'article-10', 1);
    mysqlPersistenceAssertSame(1, count($languageOneOverrides), 'SEO override list filters by language.');
    mysqlPersistenceAssertSame($overrideId, $languageOneOverrides[0]->id, 'SEO override language filter returns the matching record.');

    mysqlPersistenceAssertThrows(
        SeoCodeAlreadyExistsException::class,
        static function () use ($commands): void {
            $commands->create(new CreateSeoOverrideCommand('article', 'article-10', 1, 'duplicate', 'duplicate'));
        },
        'Duplicate SEO override identity maps to SeoCodeAlreadyExistsException',
    );

    $commands->update(new UpdateSeoOverrideCommand($overrideId, 'Updated article title', 'Updated description.'));
    $updatedOverride = $queries->getById($overrideId);
    mysqlPersistenceAssertSame('Updated article title', $updatedOverride->metaTitle, 'SEO override update persists a changed title.');
    mysqlPersistenceAssertSame('Updated description.', $updatedOverride->metaDescription, 'SEO override update persists a changed description.');

    $commands->softDelete($overrideId);
    mysqlPersistenceAssertThrows(
        SeoNotFoundException::class,
        static function () use ($queries): void {
            $queries->getActiveForEntity('article', 'article-10', 1);
        },
        'Soft-deleted SEO override disappears from active lookup',
    );
    mysqlPersistenceAssertSame([], $queries->listByEntity('article', 'article-10', 1), 'Soft-deleted SEO override disappears from the default active list.');
    $overridesIncludingDeleted = $queries->listByEntity('article', 'article-10', 1, true);
    mysqlPersistenceAssertSame(1, count($overridesIncludingDeleted), 'SEO override list can include deleted records.');
    mysqlPersistenceAssertSame($overrideId, $overridesIncludingDeleted[0]->id, 'Include-deleted SEO override list exposes the soft-deleted record.');
    mysqlPersistenceAssertTrue(is_string($overridesIncludingDeleted[0]->deletedAt), 'Soft-deleted SEO override has a deletion timestamp.');

    $commands->hardDelete($overrideId);
    mysqlPersistenceAssertThrows(
        SeoNotFoundException::class,
        static function () use ($queries, $overrideId): void {
            $queries->getById($overrideId);
        },
        'Hard-deleted SEO override is no longer found by ID',
    );
}

$dsn = mysqlPersistenceRequiredEnvironmentValue('MAATIFY_SEO_TEST_DB_DSN');
$user = mysqlPersistenceRequiredEnvironmentValue('MAATIFY_SEO_TEST_DB_USER');
$password = mysqlPersistenceRequiredEnvironmentValue('MAATIFY_SEO_TEST_DB_PASSWORD');
mysqlPersistenceValidateDsn($dsn);

if (! in_array('mysql', PDO::getAvailableDrivers(), true)) {
    throw new RuntimeException('MySQL Integration requires the PDO MySQL driver (pdo_mysql).');
}

$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

mysqlPersistenceAssertSame('mysql', $pdo->getAttribute(PDO::ATTR_DRIVER_NAME), 'Integration connection uses PDO MySQL.');
$versionStatement = $pdo->query('SELECT VERSION()');
if ($versionStatement === false) {
    throw new RuntimeException('Could not read MySQL server version.');
}
$serverVersion = $versionStatement->fetchColumn();
if (! is_string($serverVersion) || stripos($serverVersion, 'mariadb') !== false) {
    throw new RuntimeException('Persistence Integration requires a real MySQL server, not MariaDB or another PDO driver.');
}

try {
    // MySQL DDL commits independently of ordinary transactions; cleanup is explicit.
    mysqlPersistenceDropOwnedTables($pdo);

    $repositoryRoot = dirname(__DIR__, 2);
    $schemaFiles = [
        'maa_seo_redirects' => $repositoryRoot . '/schema/maa_seo_redirects.sql',
        'maa_seo_overrides' => $repositoryRoot . '/schema/maa_seo_overrides.sql',
    ];

    foreach ($schemaFiles as $table => $schemaFile) {
        $schemaSql = file_get_contents($schemaFile);
        if (! is_string($schemaSql) || trim($schemaSql) === '') {
            throw new RuntimeException(sprintf('Could not load shipped schema file for [%s] at [%s].', $table, $schemaFile));
        }

        if ($pdo->exec($schemaSql) === false) {
            throw new RuntimeException(sprintf('Shipped schema file for [%s] did not execute.', $table));
        }
    }

    mysqlPersistenceVerifySchemas($pdo);
    mysqlPersistenceVerifyRedirects($pdo);
    mysqlPersistenceVerifyOverrides($pdo);
} finally {
    try {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } finally {
        mysqlPersistenceDropOwnedTables($pdo);
    }
}

mysqlPersistenceAssertTrue(! $pdo->inTransaction(), 'No PDO transaction remains open after Integration cleanup.');
mysqlPersistenceAssertNoResidue($pdo);

fwrite(STDOUT, "Real MySQL persistence Integration passed; all package tables were cleaned up.\n");
