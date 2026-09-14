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

use Maatify\Seo\Exception\SeoInvalidArgumentException;
use Maatify\Seo\Exception\SeoNotFoundException;
use Maatify\Seo\Shared\Command\CreateRedirectCommand;
use Maatify\Seo\Shared\Command\UpdateRedirectCommand;
use Maatify\Seo\Shared\Contract\RedirectRepositoryInterface;
use Maatify\Seo\Shared\DTO\RedirectDTO;
use Maatify\Seo\Shared\Service\RedirectQueryService;

$missingRowRepository = new class implements RedirectRepositoryInterface {
    public function create(CreateRedirectCommand $command): int { return 1; }
    public function update(UpdateRedirectCommand $command): bool { return false; }
    public function findById(int $id): ?RedirectDTO { return null; }
    public function findActiveByRequestedSlug(string $entityType, int $languageId, string $requestedSlug): ?RedirectDTO { return null; }
    public function findByEntity(string $entityType, ?int $languageId = null, bool $includeDeleted = false): array { return []; }
    public function softDelete(int $id): bool { return false; }
    public function hardDelete(int $id): bool { return false; }
};

try {
    (new RedirectQueryService($missingRowRepository))->getById(17);
} catch (SeoNotFoundException $exception) {
    printf("%s package_http_status=%d safe=%s\n", $exception::class, $exception->getHttpStatus(), $exception->isSafe() ? 'true' : 'false');
}

try {
    new CreateRedirectCommand('article', 1, '/old', null, null, 302);
} catch (SeoInvalidArgumentException $exception) {
    printf("%s package_http_status=%d safe=%s\n", $exception::class, $exception->getHttpStatus(), $exception->isSafe() ? 'true' : 'false');
}
