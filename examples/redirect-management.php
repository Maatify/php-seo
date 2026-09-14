<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Maatify\Seo\Admin\Redirect\Command\CreateAdminRedirectCommand;
use Maatify\Seo\Admin\Redirect\Command\UpdateAdminRedirectCommand;
use Maatify\Seo\Admin\Redirect\Service\AdminRedirectCommandService;
use Maatify\Seo\Admin\Redirect\Service\AdminRedirectQueryService;
use Maatify\Seo\Shared\Command\CreateRedirectCommand;
use Maatify\Seo\Shared\Command\UpdateRedirectCommand;
use Maatify\Seo\Shared\Command\Redirect\ResolveRedirectCommand;
use Maatify\Seo\Shared\Contract\HostUrlGeneratorInterface;
use Maatify\Seo\Shared\Contract\RedirectRepositoryInterface;
use Maatify\Seo\Shared\DTO\RedirectDTO;
use Maatify\Seo\Shared\Service\RedirectCommandService;
use Maatify\Seo\Shared\Service\RedirectManagerService;
use Maatify\Seo\Shared\Service\RedirectQueryService;

final class InMemoryRedirectRepository implements RedirectRepositoryInterface
{
    /** @var array<int, RedirectDTO> */
    private array $records = [];

    private int $nextId = 1;

    public function create(CreateRedirectCommand $command): int
    {
        $id = $this->nextId++;
        $this->records[$id] = new RedirectDTO(
            $id,
            $command->entityType,
            $command->languageId,
            $command->requestedSlug,
            $command->targetEntityType,
            $command->targetEntityId,
            $command->httpStatus,
            '2026-09-14T12:00:00+00:00',
            null,
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
            $record->id,
            $record->entityType,
            $record->languageId,
            $record->requestedSlug,
            $command->targetEntityType,
            $command->targetEntityId,
            $command->httpStatus,
            $record->createdAt,
            null,
        );

        return true;
    }

    public function findById(int $id): ?RedirectDTO
    {
        return $this->records[$id] ?? null;
    }

    public function findActiveByRequestedSlug(string $entityType, int $languageId, string $requestedSlug): ?RedirectDTO
    {
        foreach ($this->records as $record) {
            if (
                $record->entityType === $entityType
                && $record->languageId === $languageId
                && $record->requestedSlug === $requestedSlug
                && $record->deletedAt === null
            ) {
                return $record;
            }
        }

        return null;
    }

    /** @return list<RedirectDTO> */
    public function findByEntity(string $entityType, ?int $languageId = null, bool $includeDeleted = false): array
    {
        return array_values(array_filter(
            $this->records,
            static fn (RedirectDTO $record): bool => $record->entityType === $entityType
                && ($languageId === null || $record->languageId === $languageId)
                && ($includeDeleted || $record->deletedAt === null),
        ));
    }

    public function softDelete(int $id): bool
    {
        $record = $this->records[$id] ?? null;
        if ($record === null || $record->deletedAt !== null) {
            return false;
        }

        $this->records[$id] = new RedirectDTO(
            $record->id,
            $record->entityType,
            $record->languageId,
            $record->requestedSlug,
            $record->targetEntityType,
            $record->targetEntityId,
            $record->httpStatus,
            $record->createdAt,
            '2026-09-14T12:05:00+00:00',
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
    public function generateEntityUrl(string $entityType, string $entityId, int $languageId, ?string $slug): string
    {
        $pathSegment = $slug ?? $entityId;
        return 'https://example.com/' . rawurlencode($entityType) . '/' . rawurlencode($pathSegment);
    }

    public function generateHomeUrl(int $languageId): string
    {
        return 'https://example.com/';
    }
}

$repository = new InMemoryRedirectRepository();
$commands = new RedirectCommandService($repository);
$queries = new RedirectQueryService($repository);
$adminCommands = new AdminRedirectCommandService($commands);
$adminQueries = new AdminRedirectQueryService($queries);

// The Host decides when its routing or entity changes warrant a redirect record.
$redirectId = $adminCommands->create(new CreateAdminRedirectCommand(
    entityType: 'product',
    languageId: 1,
    requestedSlug: '/products/old-widget',
    targetEntityType: 'product',
    targetEntityId: '42',
));

$adminCommands->update(new UpdateAdminRedirectCommand(
    id: $redirectId,
    targetEntityType: 'product',
    targetEntityId: '43',
    httpStatus: 301,
));

$redirectManager = new RedirectManagerService($queries, $commands, new ExampleHostUrlGenerator());
$decision = $redirectManager->resolve(new ResolveRedirectCommand(
    entityType: 'product',
    languageId: 1,
    requestedSlug: 'old-widget',
    requestedPath: '/products/old-widget',
));

$goneId = $adminCommands->createGoneRedirect(new CreateAdminRedirectCommand(
    entityType: 'product',
    languageId: 1,
    requestedSlug: '/products/discontinued-widget',
    targetEntityType: null,
    targetEntityId: null,
    httpStatus: 410,
));
$goneDecision = $redirectManager->resolve(new ResolveRedirectCommand(
    entityType: 'product',
    languageId: 1,
    requestedSlug: 'discontinued-widget',
    requestedPath: '/products/discontinued-widget',
));

echo json_encode([
    'redirect' => $adminQueries->getById($redirectId),
    'decision' => $decision,
    'gone_redirect' => $adminQueries->getById($goneId),
    'gone_decision' => $goneDecision,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
