<?php

declare(strict_types=1);

namespace Maatify\Seo\Admin\Export;

use Maatify\Seo\Admin\DTO\SeoMetadataExportDTO;
use Maatify\Seo\Exception\SeoInvalidArgumentException;
use Maatify\Seo\Shared\DTO\RedirectDTO;
use Maatify\Seo\Shared\DTO\SeoOverride\SeoOverrideDTO;

final class SeoMetadataExporter
{
    /**
     * @param list<SeoOverrideDTO|array<string, mixed>> $seoOverrides
     * @param list<RedirectDTO|array<string, mixed>> $redirects
     */
    public function export(array $seoOverrides = [], array $redirects = []): SeoMetadataExportDTO
    {
        return new SeoMetadataExportDTO(
            SeoMetadataExportDTO::SCHEMA_VERSION,
            gmdate('c'),
            $this->normalizeSeoOverrides($seoOverrides),
            $this->normalizeRedirects($redirects),
        );
    }

    /** @param list<SeoOverrideDTO|array<string, mixed>> $seoOverrides */
    public function exportSeoOverrides(array $seoOverrides): SeoMetadataExportDTO
    {
        return $this->export($seoOverrides);
    }

    /** @param list<RedirectDTO|array<string, mixed>> $redirects */
    public function exportRedirects(array $redirects): SeoMetadataExportDTO
    {
        return $this->export([], $redirects);
    }

    /** @param SeoMetadataExportDTO|array<string, mixed> $export */
    public function toJson(SeoMetadataExportDTO|array $export): string
    {
        return json_encode(
            $export instanceof SeoMetadataExportDTO ? $export->toArray() : $export,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @param list<SeoOverrideDTO|array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    private function normalizeSeoOverrides(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            $normalized[] = $item instanceof SeoOverrideDTO
                ? $this->normalizeSerializableOutput($item->jsonSerialize(), 'seo_overrides')
                : $this->normalizeArrayRow($item, 'seo_overrides');
        }

        return $normalized;
    }

    /**
     * @param list<RedirectDTO|array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    private function normalizeRedirects(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            $normalized[] = $item instanceof RedirectDTO
                ? $this->normalizeSerializableOutput($item->jsonSerialize(), 'redirects')
                : $this->normalizeArrayRow($item, 'redirects');
        }

        return $normalized;
    }

    /** @return array<string, mixed> */
    private function normalizeSerializableOutput(mixed $value, string $section): array
    {
        if (!is_array($value)) {
            throw SeoInvalidArgumentException::invalidValue($section, 'DTO serialization must return an array.');
        }

        return $this->normalizeArrayRow($value, $section);
    }

    /**
     * @param array<array-key, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeArrayRow(array $row, string $section): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            if (!is_string($key)) {
                throw SeoInvalidArgumentException::invalidValue($section, 'Export rows must use string keys.');
            }
            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
