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

use Maatify\Seo\Shared\Command\CreateSlugHistoryCommand;
use Maatify\Seo\Shared\Command\CreateRedirectCommand;
use Maatify\Seo\Shared\Command\Redirect\ResolveRedirectCommand;
use Maatify\Seo\Shared\Command\UpdateRedirectCommand;
use Maatify\Seo\Shared\Contract\HostUrlGeneratorInterface;
use Maatify\Seo\Shared\Contract\RedirectRepositoryInterface;
use Maatify\Seo\Shared\Contract\SlugHistoryRepositoryInterface;
use Maatify\Seo\Shared\DTO\Redirect\RedirectDecisionDTO;
use Maatify\Seo\Shared\DTO\RedirectDTO;
use Maatify\Seo\Shared\DTO\SlugHistoryDTO;
use Maatify\Seo\Shared\Service\RedirectCommandService;
use Maatify\Seo\Shared\Service\RedirectManagerService;
use Maatify\Seo\Shared\Service\RedirectQueryService;
use Maatify\Seo\Shared\Service\SlugHistoryCommandService;
use Maatify\Seo\Shared\Service\SlugHistoryQueryService;
use Maatify\Seo\Shared\Service\SlugHistoryService;
use Maatify\Seo\Admin\Redirect\Command\CreateAdminRedirectCommand;
use Maatify\Seo\Admin\Redirect\Command\UpdateAdminRedirectCommand;
use Maatify\Seo\Admin\Redirect\Service\AdminRedirectCommandService;
use Maatify\Seo\Admin\Redirect\Service\AdminRedirectQueryService;
use Maatify\Seo\Admin\SlugHistory\Command\RecordAdminSlugHistoryCommand;
use Maatify\Seo\Admin\SlugHistory\Service\AdminSlugHistoryCommandService;
use Maatify\Seo\Admin\SlugHistory\Service\AdminSlugHistoryQueryService;
use Maatify\Seo\Exception\SeoNotFoundException;

final class InMemorySlugHistoryRepository implements SlugHistoryRepositoryInterface
{
    /** @var array<int, SlugHistoryDTO> */
    private array $records = [];

    private int $nextId = 1;

    public function create(CreateSlugHistoryCommand $command): int
    {
        $id = $this->nextId++;
        $this->records[$id] = new SlugHistoryDTO(
            id: $id,
            entityType: $command->entityType,
            entityId: $command->entityId,
            languageId: $command->languageId,
            oldSlug: $command->oldSlug,
            createdAt: '2026-09-08T12:00:00+00:00',
            deletedAt: null,
        );

        return $id;
    }

    public function findById(int $id): ?SlugHistoryDTO
    {
        return $this->records[$id] ?? null;
    }

    public function findActiveBySlug(string $entityType, int $languageId, string $oldSlug): ?SlugHistoryDTO
    {
        foreach (array_reverse($this->records, true) as $record) {
            if ($record->deletedAt === null
                && $record->entityType === $entityType
                && $record->languageId === $languageId
                && $record->oldSlug === $oldSlug
            ) {
                return $record;
            }
        }

        return null;
    }

    /** @return list<SlugHistoryDTO> */
    public function findActiveForEntity(string $entityType, string $entityId, int $languageId): array
    {
        $matches = [];
        foreach (array_reverse($this->records, true) as $record) {
            if ($record->deletedAt === null
                && $record->entityType === $entityType
                && $record->entityId === $entityId
                && $record->languageId === $languageId
            ) {
                $matches[] = $record;
            }
        }

        return $matches;
    }

    public function softDelete(int $id): bool
    {
        $record = $this->records[$id] ?? null;
        if ($record === null || $record->deletedAt !== null) {
            return false;
        }

        $this->records[$id] = new SlugHistoryDTO(
            id: $record->id,
            entityType: $record->entityType,
            entityId: $record->entityId,
            languageId: $record->languageId,
            oldSlug: $record->oldSlug,
            createdAt: $record->createdAt,
            deletedAt: '2026-09-08T12:05:00+00:00',
        );

        return true;
    }

    public function hardDelete(int $id): bool
    {
        if (!isset($this->records[$id])) {
            return false;
        }

        unset($this->records[$id]);
        return true;
    }
}

final class InMemoryRedirectRepository implements RedirectRepositoryInterface
{
    /** @var array<int, RedirectDTO> */
    private array $records = [];

    private int $nextId = 1;

    public function create(CreateRedirectCommand $command): int
    {
        $id = $this->nextId++;
        $this->records[$id] = new RedirectDTO(
            id: $id,
            entityType: $command->entityType,
            languageId: $command->languageId,
            requestedSlug: $command->requestedSlug,
            targetEntityType: $command->targetEntityType,
            targetEntityId: $command->targetEntityId,
            httpStatus: $command->httpStatus,
            createdAt: '2026-09-08T12:00:00+00:00',
            deletedAt: null,
        );

        return $id;
    }

    public function update(UpdateRedirectCommand $command): bool
    {
        $record = $this->records[$command->id] ?? null;
        if ($record === null || $record->deletedAt !== null) {
            return false;
        }

        $this->records[$command->id] = new RedirectDTO(
            id: $record->id,
            entityType: $record->entityType,
            languageId: $record->languageId,
            requestedSlug: $record->requestedSlug,
            targetEntityType: $command->targetEntityType,
            targetEntityId: $command->targetEntityId,
            httpStatus: $command->httpStatus,
            createdAt: $record->createdAt,
            deletedAt: $record->deletedAt,
        );

        return true;
    }

    public function findById(int $id): ?RedirectDTO
    {
        return $this->records[$id] ?? null;
    }

    public function findActiveByRequestedSlug(string $entityType, int $languageId, string $requestedSlug): ?RedirectDTO
    {
        foreach (array_reverse($this->records, true) as $record) {
            if ($record->deletedAt === null
                && $record->entityType === $entityType
                && $record->languageId === $languageId
                && $record->requestedSlug === $requestedSlug
            ) {
                return $record;
            }
        }

        return null;
    }

    /** @return list<RedirectDTO> */
    public function findByEntity(string $entityType, ?int $languageId = null, bool $includeDeleted = false): array
    {
        $matches = [];
        foreach (array_reverse($this->records, true) as $record) {
            if ($record->entityType !== $entityType
                || ($languageId !== null && $record->languageId !== $languageId)
                || (!$includeDeleted && $record->deletedAt !== null)
            ) {
                continue;
            }

            $matches[] = $record;
        }

        return $matches;
    }

    public function softDelete(int $id): bool
    {
        $record = $this->records[$id] ?? null;
        if ($record === null || $record->deletedAt !== null) {
            return false;
        }

        $this->records[$id] = new RedirectDTO(
            id: $record->id,
            entityType: $record->entityType,
            languageId: $record->languageId,
            requestedSlug: $record->requestedSlug,
            targetEntityType: $record->targetEntityType,
            targetEntityId: $record->targetEntityId,
            httpStatus: $record->httpStatus,
            createdAt: $record->createdAt,
            deletedAt: '2026-09-08T12:05:00+00:00',
        );

        return true;
    }

    public function hardDelete(int $id): bool
    {
        if (!isset($this->records[$id])) {
            return false;
        }

        unset($this->records[$id]);
        return true;
    }
}

final class ExampleHostUrlGenerator implements HostUrlGeneratorInterface
{
    /** @var array<string, string> */
    private array $currentSlugs = [
        'product:42' => 'super-widget-pro',
    ];

    public function generateEntityUrl(string $entityType, string $entityId, int $languageId, ?string $slug): string
    {
        $key = $entityType . ':' . $entityId;
        $resolvedSlug = $slug ?? ($this->currentSlugs[$key] ?? $entityId);

        return 'https://example.com/' . rawurlencode($entityType) . '/' . rawurlencode($resolvedSlug);
    }

    public function generateHomeUrl(int $languageId): string
    {
        return 'https://example.com/';
    }
}

function printJsonSection(string $title, \JsonSerializable $value): void
{
    $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($encoded === false) {
        throw new RuntimeException('Unable to encode example output.');
    }

    echo "\n==============================\n";
    echo $title . "\n";
    echo "==============================\n";
    echo $encoded . "\n";
}

$slugHistoryRepository = new InMemorySlugHistoryRepository();
$redirectRepository = new InMemoryRedirectRepository();
$urlGenerator = new ExampleHostUrlGenerator();

$slugHistoryQueryService = new SlugHistoryQueryService($slugHistoryRepository);
$slugHistoryCommandService = new SlugHistoryCommandService($slugHistoryRepository);
$redirectCommandService = new RedirectCommandService($redirectRepository);
$redirectQueryService = new RedirectQueryService($redirectRepository);

$slugHistoryService = new SlugHistoryService(
    slugHistoryQueryService: $slugHistoryQueryService,
    slugHistoryCommandService: $slugHistoryCommandService,
    redirectCommandService: $redirectCommandService,
);
$redirectManagerService = new RedirectManagerService(
    redirectQueryService: $redirectQueryService,
    redirectCommandService: $redirectCommandService,
    urlGenerator: $urlGenerator,
);

$entityType = 'product';
$entityId = '42';
$languageId = 1;
$oldSlug = 'widget-pro';
$newSlug = 'super-widget-pro';

$historyId = $slugHistoryService->recordSlugChange(new \Maatify\Seo\Shared\Command\SlugHistory\RecordSlugChangeCommand(
    entityType: $entityType,
    entityId: $entityId,
    languageId: $languageId,
    oldSlug: $oldSlug,
    newSlug: $newSlug,
    createRedirect: true,
));

echo "\n1. Slug change recorded: {$oldSlug} -> {$newSlug}\n";
$history = $slugHistoryQueryService->getById($historyId);
printJsonSection('Slug-history record', $history);

$redirects = $redirectQueryService->listByEntity($entityType, $languageId);
if ($redirects === []) {
    throw new RuntimeException('Expected the optional redirect to be created.');
}

$redirect = $redirects[0];
echo "\n2. Optional redirect creation: enabled\n";
printJsonSection('Redirect record created by SlugHistoryService', $redirect);

$decision = $redirectManagerService->resolve(new ResolveRedirectCommand(
    entityType: $entityType,
    languageId: $languageId,
    requestedSlug: $oldSlug,
));

echo "\n3. Legacy slug resolution: {$oldSlug}\n";
printJsonSection('Final redirect decision', $decision);

if (!$decision instanceof RedirectDecisionDTO || $decision->targetUrl === null) {
    throw new RuntimeException('Expected a permanent redirect decision with a target URL.');
}

echo "Target URL: {$decision->targetUrl}\n";

$adminSlugCommands = new AdminSlugHistoryCommandService($slugHistoryCommandService);
$adminSlugQueries = new AdminSlugHistoryQueryService($slugHistoryQueryService);
$adminHistoryId = $adminSlugCommands->record(new RecordAdminSlugHistoryCommand(
    entityType: 'admin-example',
    entityId: 'history-42',
    languageId: 1,
    oldSlug: '/admin-old-slug',
));
$adminHistory = $adminSlugQueries->getById($adminHistoryId);
echo "\n4. Admin slug-history API\n";
echo 'record() returned ID: ' . $adminHistoryId . "\n";
printJsonSection('AdminSlugHistoryDTO returned by getById()', $adminHistory);
echo 'listActiveForEntity() count: ' . count($adminSlugQueries->listActiveForEntity('admin-example', 'history-42', 1)) . "\n";

$adminRedirectCommands = new AdminRedirectCommandService($redirectCommandService);
$adminRedirectQueries = new AdminRedirectQueryService($redirectQueryService);
$adminRedirectId = $adminRedirectCommands->create(new CreateAdminRedirectCommand(
    entityType: 'admin-example',
    languageId: 1,
    requestedSlug: '/admin-old-path',
    targetEntityType: 'product',
    targetEntityId: '42',
));
$adminRedirectCommands->update(new UpdateAdminRedirectCommand($adminRedirectId, 'product', '43', 301));
$adminRedirect = $adminRedirectQueries->getActiveByRequestedSlug('admin-example', 1, '/admin-old-path');
echo "\n5. Admin redirect API\n";
echo 'create() returned ID: ' . $adminRedirectId . "\n";
printJsonSection('AdminRedirectDTO returned after update and active query', $adminRedirect);
echo 'listByEntity() count: ' . count($adminRedirectQueries->listByEntity('admin-example', 1)) . "\n";

$adminRedirectCommands->softDelete($adminRedirectId);
$softDeletedRedirect = $adminRedirectQueries->getById($adminRedirectId);
echo 'softDelete() returns void; getById().isDeleted: ' . ($softDeletedRedirect->isDeleted ? 'true' : 'false') . "\n";
echo 'active list count after soft delete: ' . count($adminRedirectQueries->listByEntity('admin-example', 1)) . "\n";
$adminRedirectCommands->hardDelete($adminRedirectId);
try {
    $adminRedirectQueries->getById($adminRedirectId);
} catch (SeoNotFoundException $exception) {
    echo 'getById() after hardDelete(): ' . $exception::class . "\n";
}
