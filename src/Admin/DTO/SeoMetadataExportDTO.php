<?php

declare(strict_types=1);

namespace Maatify\Seo\Admin\DTO;

final readonly class SeoMetadataExportDTO implements \JsonSerializable
{
    public const SCHEMA_VERSION = '2.0';

    /**
     * @param list<array<string, mixed>> $seoOverrides
     * @param list<array<string, mixed>> $redirects
     */
    public function __construct(
        public string $schemaVersion,
        public string $exportedAt,
        public array $seoOverrides = [],
        public array $redirects = [],
    ) {
    }

    /** @return array{schema_version:string, exported_at:string, data:array{seo_overrides:list<array<string, mixed>>, redirects:list<array<string, mixed>>}} */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    /** @return array{schema_version:string, exported_at:string, data:array{seo_overrides:list<array<string, mixed>>, redirects:list<array<string, mixed>>}} */
    public function jsonSerialize(): array
    {
        return [
            'schema_version' => $this->schemaVersion,
            'exported_at' => $this->exportedAt,
            'data' => [
                'seo_overrides' => $this->seoOverrides,
                'redirects' => $this->redirects,
            ],
        ];
    }
}
