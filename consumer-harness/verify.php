<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Maatify\Seo\Shared\Command\GenerateMetaTagsCommand;
use Maatify\Seo\Shared\Command\SeoOverride\CreateSeoOverrideCommand;
use Maatify\Seo\Shared\DTO\MetaTagsDTO;
use Maatify\Seo\Shared\Infrastructure\Persistence\PdoSeoOverrideRepository;
use Maatify\Seo\Shared\Service\MetaGeneratorService;
use Maatify\Seo\Shared\Service\SeoOverrideCommandService;
use Maatify\Seo\Shared\Service\SeoOverrideQueryService;
use Maatify\Seo\Web\Render\MetaTagsHtmlRenderer;
require __DIR__ . '/vendor/autoload.php';

function consumerHarnessAssertSame(mixed $expected, mixed $actual, string $message): void
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

function consumerHarnessRequiredEnvironmentValue(string $name): string
{
    $value = getenv($name);
    if (! is_string($value) || trim($value) === '') {
        throw new RuntimeException(sprintf('Required consumer verification environment variable [%s] is missing or empty.', $name));
    }

    return $value;
}

function consumerHarnessValidateDsn(string $dsn): void
{
    if (! str_starts_with($dsn, 'mysql:')) {
        throw new RuntimeException('Consumer verification requires a PDO MySQL DSN beginning with [mysql:].');
    }

    $dsnOptions = substr($dsn, strlen('mysql:'));
    foreach (['host', 'port', 'dbname', 'charset'] as $option) {
        if (! preg_match('/(?:^|;)' . preg_quote($option, '/') . '=([^;]+)(?:;|$)/', $dsnOptions, $matches)) {
            throw new RuntimeException(sprintf('Consumer verification DSN must include a non-empty [%s] option.', $option));
        }

        $value = $matches[1];
        if ($option === 'host' && ! in_array($value, ['127.0.0.1', 'localhost'], true)) {
            throw new RuntimeException('Consumer verification DSN host must be local (127.0.0.1 or localhost).');
        }

        if ($option === 'port' && (! ctype_digit($value) || (int) $value < 1 || (int) $value > 65535)) {
            throw new RuntimeException('Consumer verification DSN port must be between 1 and 65535.');
        }

        if ($option === 'dbname' && ! preg_match('/^[A-Za-z0-9_]+_test$/', $value)) {
            throw new RuntimeException('Consumer verification database name must be a dedicated name ending in [_test].');
        }

        if ($option === 'charset' && $value !== 'utf8mb4') {
            throw new RuntimeException('Consumer verification DSN charset must be [utf8mb4].');
        }
    }
}

function consumerHarnessAssertInstalledPackage(): string
{
    $installedPath = InstalledVersions::getInstallPath('maatify/php-seo');
    if (! is_string($installedPath) || $installedPath === '') {
        throw new RuntimeException('Composer did not resolve an installed path for [maatify/php-seo].');
    }

    if (is_link($installedPath) || ! is_dir($installedPath)) {
        throw new RuntimeException('The installed [maatify/php-seo] package path must exist and must not be a symlink.');
    }

    $realInstalledPath = realpath($installedPath);
    $serviceFile = (new ReflectionClass(MetaGeneratorService::class))->getFileName();
    $realServiceFile = is_string($serviceFile) ? realpath($serviceFile) : false;
    if (! is_string($realInstalledPath) || ! is_string($realServiceFile)) {
        throw new RuntimeException('Unable to resolve the installed package or representative service file.');
    }

    if (! str_starts_with($realServiceFile, $realInstalledPath . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('MetaGeneratorService was not loaded from the installed Composer package path.');
    }

    return $realInstalledPath;
}

function consumerHarnessAssertHtml(MetaTagsHtmlRenderer $renderer, MetaTagsDTO $metaTags, string $expected, string $message): void
{
    consumerHarnessAssertSame($expected, $renderer->render($metaTags), $message);
}

if (! extension_loaded('pdo_mysql') || ! in_array('mysql', PDO::getAvailableDrivers(), true)) {
    throw new RuntimeException('Consumer verification requires the pdo_mysql extension and PDO MySQL driver.');
}

$installedPackagePath = consumerHarnessAssertInstalledPackage();
$schemaPath = $installedPackagePath . '/schema/maa_seo_overrides.sql';
$realSchemaPath = realpath($schemaPath);
if (! is_string($realSchemaPath) || ! str_starts_with($realSchemaPath, $installedPackagePath . DIRECTORY_SEPARATOR)) {
    throw new RuntimeException('The shipped override schema is missing from the installed Composer package.');
}

$dsn = consumerHarnessRequiredEnvironmentValue('MAATIFY_SEO_TEST_DB_DSN');
$databaseUser = consumerHarnessRequiredEnvironmentValue('MAATIFY_SEO_TEST_DB_USER');
$databasePassword = consumerHarnessRequiredEnvironmentValue('MAATIFY_SEO_TEST_DB_PASSWORD');
consumerHarnessValidateDsn($dsn);

$pdo = new PDO($dsn, $databaseUser, $databasePassword, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

try {
    if ($pdo->inTransaction()) {
        throw new RuntimeException('Consumer verification must begin without an open transaction.');
    }

    $pdo->exec('DROP TABLE IF EXISTS `maa_seo_overrides`');
    $schemaSql = file_get_contents($realSchemaPath);
    if (! is_string($schemaSql) || trim($schemaSql) === '') {
        throw new RuntimeException('Unable to read the installed override schema.');
    }

    $pdo->exec($schemaSql);
    $createdTable = $pdo->query(
        "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'maa_seo_overrides'",
    )->fetchColumn();
    consumerHarnessAssertSame('maa_seo_overrides', $createdTable, 'The installed schema creates the package-owned override table.');

    $repository = new PdoSeoOverrideRepository($pdo);
    $commandService = new SeoOverrideCommandService($repository);
    $queryService = new SeoOverrideQueryService($repository);
    $metaGenerator = new MetaGeneratorService($queryService);
    $renderer = new MetaTagsHtmlRenderer();

    $overrideId = $commandService->create(new CreateSeoOverrideCommand(
        entityType: 'article',
        entityId: 'consumer-article-1',
        languageId: 1,
        metaTitle: 'SEO Override <Title> & "Review"',
        metaDescription: 'Custom "description" & <details>',
    ));
    if ($overrideId < 1) {
        throw new RuntimeException('Creating an SEO override must return a positive inserted ID.');
    }

    $generationCommand = new GenerateMetaTagsCommand(
        entityType: 'article',
        entityId: 'consumer-article-1',
        languageId: 1,
        defaultTitle: '  Host Default & Title  ',
        defaultDescription: ' Host fallback <description> ',
        canonicalUrl: '  https://consumer.example.test/article?id=1&view=full  ',
    );
    $overriddenMetaTags = $metaGenerator->generate($generationCommand);
    consumerHarnessAssertSame('SEO Override <Title> & "Review"', $overriddenMetaTags->title, 'The active override replaces the host title.');
    consumerHarnessAssertSame('Custom "description" & <details>', $overriddenMetaTags->description, 'The active override replaces the host description.');
    consumerHarnessAssertSame('https://consumer.example.test/article?id=1&view=full', $overriddenMetaTags->canonicalUrl, 'The explicit canonical remains selected.');
    consumerHarnessAssertHtml(
        $renderer,
        $overriddenMetaTags,
        '<title>SEO Override &lt;Title&gt; &amp; &quot;Review&quot;</title>' . "\n"
            . '<meta name="description" content="Custom &quot;description&quot; &amp; &lt;details&gt;">' . "\n"
            . '<link rel="canonical" href="https://consumer.example.test/article?id=1&amp;view=full">' . "\n"
            . '<meta name="robots" content="index,follow">',
        'The consumer renderer escapes overridden metadata and the explicit canonical.',
    );

    $commandService->softDelete($overrideId);
    $fallbackMetaTags = $metaGenerator->generate($generationCommand);
    consumerHarnessAssertSame('Host Default & Title', $fallbackMetaTags->title, 'Soft deletion restores the host title fallback.');
    consumerHarnessAssertSame('Host fallback <description>', $fallbackMetaTags->description, 'Soft deletion restores the host description fallback.');
    consumerHarnessAssertSame('https://consumer.example.test/article?id=1&view=full', $fallbackMetaTags->canonicalUrl, 'Soft deletion leaves the explicit canonical intact.');
    consumerHarnessAssertHtml(
        $renderer,
        $fallbackMetaTags,
        '<title>Host Default &amp; Title</title>' . "\n"
            . '<meta name="description" content="Host fallback &lt;description&gt;">' . "\n"
            . '<link rel="canonical" href="https://consumer.example.test/article?id=1&amp;view=full">' . "\n"
            . '<meta name="robots" content="index,follow">',
        'The consumer renderer exposes host fallback metadata after soft deletion.',
    );

    if ($pdo->inTransaction()) {
        throw new RuntimeException('Consumer verification completed with an unexpected open transaction.');
    }
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $pdo->exec('DROP TABLE IF EXISTS `maa_seo_overrides`');
    if ($pdo->inTransaction()) {
        throw new RuntimeException('Consumer verification cleanup left a transaction open.');
    }

    $remainingTable = $pdo->query(
        "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'maa_seo_overrides'",
    )->fetchColumn();
    consumerHarnessAssertSame(false, $remainingTable, 'Consumer verification leaves no package-owned override table behind.');
}

fwrite(STDOUT, "Consumer verification passed: installed package, MySQL persistence, generation, fallback, and rendering.\n");
