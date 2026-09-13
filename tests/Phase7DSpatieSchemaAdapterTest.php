<?php

declare(strict_types=1);
function phpstanRuntimeInstanceOfPhase7DSpatieSchemaAdapterTest(mixed $value, string $class): bool
{
    return $value instanceof $class;
}


require_once __DIR__ . '/bootstrap.php';

use Maatify\Seo\Exception\SeoExceptionInterface;
use Maatify\Seo\Exception\SeoInvalidArgumentException;
use Maatify\Seo\Shared\DTO\Schema\JsonLdSchemaDTO;
use Maatify\Seo\Web\Builder\FluentSeoBuilder;
use Maatify\Seo\Web\Render\SeoHeadHtmlRenderer;
use Maatify\Seo\Web\Schema\SpatieSchemaAdapter;

function testPhase7DSpatieSchemaAdapterTestAssertSameValue(string $label, mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, "Assertion failed: {$label}\nExpected:\n" . var_export($expected, true) . "\nActual:\n" . var_export($actual, true) . "\n");
        exit(1);
    }
}

function testPhase7DSpatieSchemaAdapterTestAssertTrueValue(string $label, bool $actual): void
{
    if (!$actual) {
        fwrite(STDERR, "Assertion failed: {$label}\n");
        exit(1);
    }
}

function testPhase7DSpatieSchemaAdapterTestAssertFalseValue(string $label, bool $actual): void
{
    if ($actual) {
        fwrite(STDERR, "Assertion failed: {$label}\n");
        exit(1);
    }
}

function testPhase7DSpatieSchemaAdapterTestAssertThrowsSeoException(string $label, callable $callback): void
{
    try {
        $callback();
    } catch (SeoExceptionInterface $exception) {
        testPhase7DSpatieSchemaAdapterTestAssertTrueValue($label . ' uses invalid argument exception', $exception instanceof SeoInvalidArgumentException);
        return;
    }

    fwrite(STDERR, "Assertion failed: {$label}\nExpected SEO module exception.\n");
    exit(1);
}

final class FakeToArraySchema
{
    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['@type' => 'Article', 'headline' => 'Array schema'];
    }
}

final class FakeJsonSerializeSchema
{
    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return ['@type' => 'Product', 'name' => 'Serialized schema'];
    }
}

final class FakeScriptSchema
{
    public function toScript(): string
    {
        return '<script type="application/ld+json">{"@type":"WebPage","name":"Script schema"}</script>';
    }
}

final class FakeInvalidSchema
{
}

final class FakeEmptyArraySchema
{
    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [];
    }
}

final class FakeListArraySchema
{
    /** @return list<string> */
    public function toArray(): array
    {
        return ['not associative'];
    }
}

$adapter = new SpatieSchemaAdapter();

$arrayDto = $adapter->toJsonLdSchemaDTO(new FakeToArraySchema());
testPhase7DSpatieSchemaAdapterTestAssertTrueValue('toArray conversion returns JsonLdSchemaDTO', phpstanRuntimeInstanceOfPhase7DSpatieSchemaAdapterTest($arrayDto, JsonLdSchemaDTO::class));
testPhase7DSpatieSchemaAdapterTestAssertSameValue('toArray schema maps to DTO', ['@type' => 'Article', 'headline' => 'Array schema'], $arrayDto->jsonSerialize());
testPhase7DSpatieSchemaAdapterTestAssertTrueValue('supports returns true for toArray schema', $adapter->supports(new FakeToArraySchema()));

$jsonSerializeDto = $adapter->toJsonLdSchemaDTO(new FakeJsonSerializeSchema());
testPhase7DSpatieSchemaAdapterTestAssertSameValue('jsonSerialize schema maps to DTO', ['@type' => 'Product', 'name' => 'Serialized schema'], $jsonSerializeDto->jsonSerialize());
testPhase7DSpatieSchemaAdapterTestAssertTrueValue('supports returns true for jsonSerialize schema', $adapter->supports(new FakeJsonSerializeSchema()));

$scriptDto = $adapter->toJsonLdSchemaDTO(new FakeScriptSchema());
testPhase7DSpatieSchemaAdapterTestAssertSameValue('toScript schema maps to DTO', ['@type' => 'WebPage', 'name' => 'Script schema'], $scriptDto->jsonSerialize());
testPhase7DSpatieSchemaAdapterTestAssertTrueValue('supports returns true for toScript schema', $adapter->supports(new FakeScriptSchema()));

$multipleSchemas = $adapter->toJsonLdSchemaDTOs([
    new FakeToArraySchema(),
    new FakeJsonSerializeSchema(),
    new FakeScriptSchema(),
]);
testPhase7DSpatieSchemaAdapterTestAssertSameValue('multiple schema conversion count', 3, count($multipleSchemas));
testPhase7DSpatieSchemaAdapterTestAssertSameValue('multiple schema conversion preserves order', ['@type' => 'Product', 'name' => 'Serialized schema'], $multipleSchemas[1]->jsonSerialize());

testPhase7DSpatieSchemaAdapterTestAssertFalseValue('supports returns false for invalid object', $adapter->supports(new FakeInvalidSchema()));
testPhase7DSpatieSchemaAdapterTestAssertFalseValue('supports returns false for empty array output', $adapter->supports(new FakeEmptyArraySchema()));
testPhase7DSpatieSchemaAdapterTestAssertFalseValue('supports returns false for list array output', $adapter->supports(new FakeListArraySchema()));

testPhase7DSpatieSchemaAdapterTestAssertThrowsSeoException('invalid object throws module exception', static function () use ($adapter): void {
    $adapter->toJsonLdSchemaDTO(new FakeInvalidSchema());
});

testPhase7DSpatieSchemaAdapterTestAssertThrowsSeoException('empty array output throws module exception', static function () use ($adapter): void {
    $adapter->toJsonLdSchemaDTO(new FakeEmptyArraySchema());
});

testPhase7DSpatieSchemaAdapterTestAssertThrowsSeoException('list array output throws module exception', static function () use ($adapter): void {
    $adapter->toJsonLdSchemaDTO(new FakeListArraySchema());
});

$builderOutput = (new FluentSeoBuilder())
    ->title('Spatie builder')
    ->spatieSchema(new FakeToArraySchema(), $adapter)
    ->render(new SeoHeadHtmlRenderer());

testPhase7DSpatieSchemaAdapterTestAssertSameValue(
    'FluentSeoBuilder spatieSchema renders adapter DTO',
    '<title>Spatie builder</title>' . "\n"
    . '<meta name="robots" content="index,follow">' . "\n"
    . '<script type="application/ld+json">{"@type":"Article","headline":"Array schema"}</script>',
    $builderOutput,
);

$existingBuilderOutput = (new FluentSeoBuilder())
    ->title('Existing schema')
    ->schema(['@type' => 'Organization'])
    ->render(new SeoHeadHtmlRenderer());

testPhase7DSpatieSchemaAdapterTestAssertSameValue(
    'Existing Phase 7C schema behavior remains unchanged',
    '<title>Existing schema</title>' . "\n"
    . '<meta name="robots" content="index,follow">' . "\n"
    . '<script type="application/ld+json">{"@type":"Organization"}</script>',
    $existingBuilderOutput,
);

testPhase7DSpatieSchemaAdapterTestAssertSameValue(
    'Existing Phase 7A renderer behavior remains unchanged',
    '<script type="application/ld+json">{"@type":"WebPage"}</script>',
    (new Maatify\Seo\Web\Render\JsonLdScriptRenderer())->render(new JsonLdSchemaDTO(['@type' => 'WebPage'])),
);

echo "Phase 7D Spatie schema adapter tests passed.\n";
