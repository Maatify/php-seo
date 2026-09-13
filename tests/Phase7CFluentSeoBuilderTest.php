<?php

declare(strict_types=1);
function phpstanRuntimeInstanceOfPhase7CFluentSeoBuilderTest(mixed $value, string $class): bool
{
    return $value instanceof $class;
}


require_once __DIR__ . '/bootstrap.php';

use Maatify\Seo\Exception\SeoExceptionInterface;
use Maatify\Seo\Exception\SeoInvalidArgumentException;
use Maatify\Seo\Shared\DTO\MetaTagsDTO;
use Maatify\Seo\Shared\DTO\Schema\JsonLdSchemaDTO;
use Maatify\Seo\Web\Builder\FluentSeoBuilder;
use Maatify\Seo\Web\DTO\SeoHeadHtmlDTO;
use Maatify\Seo\Web\Render\SeoHeadHtmlRenderer;

function testPhase7CFluentSeoBuilderTestAssertSameValue(string $label, mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, "Assertion failed: {$label}\nExpected:\n" . var_export($expected, true) . "\nActual:\n" . var_export($actual, true) . "\n");
        exit(1);
    }
}

function testPhase7CFluentSeoBuilderTestAssertTrueValue(string $label, bool $actual): void
{
    if (!$actual) {
        fwrite(STDERR, "Assertion failed: {$label}\n");
        exit(1);
    }
}

function testPhase7CFluentSeoBuilderTestAssertThrowsSeoException(string $label, callable $callback): void
{
    try {
        $callback();
    } catch (SeoExceptionInterface $exception) {
        testPhase7CFluentSeoBuilderTestAssertTrueValue($label . ' uses invalid argument exception', $exception instanceof SeoInvalidArgumentException);
        return;
    }

    fwrite(STDERR, "Assertion failed: {$label}\nExpected SEO module exception.\n");
    exit(1);
}

$metaTags = (new FluentSeoBuilder())
    ->title('Fluent Title')
    ->description('Fluent description')
    ->canonical('https://example.com/fluent')
    ->robots('noindex,nofollow')
    ->buildMetaTags();

testPhase7CFluentSeoBuilderTestAssertTrueValue('buildMetaTags returns MetaTagsDTO', phpstanRuntimeInstanceOfPhase7CFluentSeoBuilderTest($metaTags, MetaTagsDTO::class));
testPhase7CFluentSeoBuilderTestAssertSameValue('title is mapped', 'Fluent Title', $metaTags->title);
testPhase7CFluentSeoBuilderTestAssertSameValue('description is mapped', 'Fluent description', $metaTags->description);
testPhase7CFluentSeoBuilderTestAssertSameValue('canonical is mapped', 'https://example.com/fluent', $metaTags->canonicalUrl);
testPhase7CFluentSeoBuilderTestAssertSameValue('robots is mapped', 'noindex,nofollow', $metaTags->robots);

$defaultRobots = (new FluentSeoBuilder())->title('Default robots')->buildMetaTags();
testPhase7CFluentSeoBuilderTestAssertSameValue('robots defaults to index,follow', 'index,follow', $defaultRobots->robots);

$socialMetaTags = (new FluentSeoBuilder())
    ->title('Social Title')
    ->openGraphTitle('OG Title')
    ->openGraphDescription('OG Description')
    ->openGraphType('article')
    ->openGraphUrl('https://example.com/og')
    ->openGraphImage('https://example.com/og.jpg')
    ->twitterCard('summary_large_image')
    ->twitterTitle('Twitter Title')
    ->twitterDescription('Twitter Description')
    ->twitterImage('https://example.com/twitter.jpg')
    ->buildMetaTags();

testPhase7CFluentSeoBuilderTestAssertSameValue('OpenGraph title is mapped', 'OG Title', $socialMetaTags->openGraphTitle);
testPhase7CFluentSeoBuilderTestAssertSameValue('OpenGraph description is mapped', 'OG Description', $socialMetaTags->openGraphDescription);
testPhase7CFluentSeoBuilderTestAssertSameValue('OpenGraph type is mapped', 'article', $socialMetaTags->openGraphType);
testPhase7CFluentSeoBuilderTestAssertSameValue('OpenGraph url is mapped', 'https://example.com/og', $socialMetaTags->openGraphUrl);
testPhase7CFluentSeoBuilderTestAssertSameValue('OpenGraph image is mapped', 'https://example.com/og.jpg', $socialMetaTags->openGraphImage);
testPhase7CFluentSeoBuilderTestAssertSameValue('Twitter card is mapped', 'summary_large_image', $socialMetaTags->twitterCard);
testPhase7CFluentSeoBuilderTestAssertSameValue('Twitter title is mapped', 'Twitter Title', $socialMetaTags->twitterTitle);
testPhase7CFluentSeoBuilderTestAssertSameValue('Twitter description is mapped', 'Twitter Description', $socialMetaTags->twitterDescription);
testPhase7CFluentSeoBuilderTestAssertSameValue('Twitter image is mapped', 'https://example.com/twitter.jpg', $socialMetaTags->twitterImage);

$renderer = new SeoHeadHtmlRenderer();
$schemaDto = new JsonLdSchemaDTO(['@type' => 'WebPage', 'name' => 'DTO Schema']);
$builder = (new FluentSeoBuilder())
    ->title('Rendered Title')
    ->description('Rendered description')
    ->schema($schemaDto)
    ->schema(['@type' => 'Organization', 'name' => 'Array Schema']);

$expectedMetaTags = new MetaTagsDTO(
    title: 'Rendered Title',
    description: 'Rendered description',
    canonicalUrl: null,
    robots: 'index,follow',
);
$expectedSchemas = [
    $schemaDto,
    new JsonLdSchemaDTO(['@type' => 'Organization', 'name' => 'Array Schema']),
];

testPhase7CFluentSeoBuilderTestAssertSameValue(
    'render returns the same output as SeoHeadHtmlRenderer',
    $renderer->render($expectedMetaTags, $expectedSchemas),
    $builder->render($renderer),
);

$renderDto = $builder->renderDto($renderer);
testPhase7CFluentSeoBuilderTestAssertTrueValue('renderDto returns SeoHeadHtmlDTO', phpstanRuntimeInstanceOfPhase7CFluentSeoBuilderTest($renderDto, SeoHeadHtmlDTO::class));
testPhase7CFluentSeoBuilderTestAssertSameValue('renderDto fullHtml matches render', $builder->render($renderer), $renderDto->fullHtml);

$multipleSchemasOutput = (new FluentSeoBuilder())
    ->title('Multiple schemas')
    ->schemas([
        new JsonLdSchemaDTO(['@type' => 'WebPage']),
        ['@type' => 'Organization'],
    ])
    ->render($renderer);

testPhase7CFluentSeoBuilderTestAssertSameValue(
    'multiple schemas render in order',
    '<title>Multiple schemas</title>' . "\n"
    . '<meta name="robots" content="index,follow">' . "\n"
    . '<script type="application/ld+json">{"@type":"WebPage"}</script>' . "\n"
    . '<script type="application/ld+json">{"@type":"Organization"}</script>',
    $multipleSchemasOutput,
);

$clearedSchemasOutput = (new FluentSeoBuilder())
    ->title('Cleared schemas')
    ->schema(['@type' => 'WebPage'])
    ->clearSchemas()
    ->render($renderer);

testPhase7CFluentSeoBuilderTestAssertSameValue(
    'clearSchemas removes all schema output',
    '<title>Cleared schemas</title>' . "\n" . '<meta name="robots" content="index,follow">',
    $clearedSchemasOutput,
);

testPhase7CFluentSeoBuilderTestAssertThrowsSeoException('missing title throws module exception', static function (): void {
    (new FluentSeoBuilder())->buildMetaTags();
});

testPhase7CFluentSeoBuilderTestAssertThrowsSeoException('empty title throws module exception', static function (): void {
    (new FluentSeoBuilder())->title('');
});

testPhase7CFluentSeoBuilderTestAssertThrowsSeoException('invalid schema input throws module exception', static function (): void {
    (new FluentSeoBuilder())->title('Invalid schema')->schemas([['not associative']]);
});

testPhase7CFluentSeoBuilderTestAssertSameValue(
    'Phase 7A renderer behavior remains unchanged',
    '<title>Regression</title>' . "\n" . '<meta name="robots" content="index,follow">',
    $renderer->render(new MetaTagsDTO('Regression', null, null)),
);

echo "Phase 7C fluent SEO builder tests passed.\n";
