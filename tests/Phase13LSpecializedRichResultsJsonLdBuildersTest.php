<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use Maatify\Seo\Web\JsonLd\Builder\RecipeJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\JobPostingJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\CourseJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\SoftwareApplicationJsonLdBuilder;

/** @param array<array-key, mixed> $array */
function testPhase13LSpecializedRichResultsJsonLdBuildersTestRecursiveKsort(array &$array): void
{
    foreach ($array as &$value) {
        if (is_array($value)) {
            testPhase13LSpecializedRichResultsJsonLdBuildersTestRecursiveKsort($value);
        }
    }
    ksort($array);
}

function testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(mixed $expected, mixed $actual, string $message): void
{
    $expectedSorted = $expected;
    $actualSorted = $actual;

    if (is_array($expectedSorted) && is_array($actualSorted)) {
        testPhase13LSpecializedRichResultsJsonLdBuildersTestRecursiveKsort($expectedSorted);
        testPhase13LSpecializedRichResultsJsonLdBuildersTestRecursiveKsort($actualSorted);
    }

    if ($expectedSorted !== $actualSorted) {
        $expectedStr = var_export($expectedSorted, true);
        $actualStr = var_export($actualSorted, true);
        throw new \RuntimeException("$message\nExpected: $expectedStr\nActual: $actualStr");
    }
}

echo "Testing Phase 13L Specialized Rich Results JSON-LD Builders...\n";

// 1. RecipeJsonLdBuilder
$recipe = new RecipeJsonLdBuilder();
$recipe->setName('Pancakes')
    ->setDescription('Delicious pancakes')
    ->setImage('pancakes.jpg')
    ->setAuthor('John Doe')
    ->setDatePublished('2023-01-01')
    ->setPrepTime('PT15M')
    ->setCookTime('PT10M')
    ->setTotalTime('PT25M')
    ->setRecipeYield('4 servings')
    ->setRecipeCategory('Breakfast')
    ->setRecipeCuisine('American')
    ->setRecipeIngredient(['Flour', 'Milk'])
    ->addRecipeIngredient('Eggs')
    ->setRecipeInstructions([
        'Mix ingredients',
        ['@type' => 'HowToStep', 'text' => 'Cook on pan']
    ])
    ->addRecipeInstruction('Serve hot')
    ->setNutrition(['calories' => '250 calories'])
    ->setAggregateRating(['ratingValue' => '5']);

$recipeOutput = $recipe->toArray();

testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Recipe', $recipeOutput['@type'] ?? null, 'Recipe @type');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Pancakes', $recipeOutput['name'] ?? null, 'Recipe name');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Delicious pancakes', $recipeOutput['description'] ?? null, 'Recipe description');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('pancakes.jpg', $recipeOutput['image'] ?? null, 'Recipe image');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['@type' => 'Person', 'name' => 'John Doe'], $recipeOutput['author'] ?? null, 'Recipe author normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('2023-01-01', $recipeOutput['datePublished'] ?? null, 'Recipe datePublished');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('PT15M', $recipeOutput['prepTime'] ?? null, 'Recipe prepTime');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('PT10M', $recipeOutput['cookTime'] ?? null, 'Recipe cookTime');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('PT25M', $recipeOutput['totalTime'] ?? null, 'Recipe totalTime');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('4 servings', $recipeOutput['recipeYield'] ?? null, 'Recipe recipeYield');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Breakfast', $recipeOutput['recipeCategory'] ?? null, 'Recipe recipeCategory');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('American', $recipeOutput['recipeCuisine'] ?? null, 'Recipe recipeCuisine');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['Flour', 'Milk', 'Eggs'], $recipeOutput['recipeIngredient'] ?? null, 'Recipe ingredients');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue([
    ['@type' => 'HowToStep', 'text' => 'Mix ingredients'],
    ['@type' => 'HowToStep', 'text' => 'Cook on pan'],
    ['@type' => 'HowToStep', 'text' => 'Serve hot']
], $recipeOutput['recipeInstructions'] ?? null, 'Recipe instructions normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['calories' => '250 calories', '@type' => 'NutritionInformation'], $recipeOutput['nutrition'] ?? null, 'Recipe nutrition normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['ratingValue' => '5', '@type' => 'AggregateRating'], $recipeOutput['aggregateRating'] ?? null, 'Recipe aggregateRating normalized');

// 2. JobPostingJsonLdBuilder
$jobPosting = new JobPostingJsonLdBuilder();
$jobPosting->setTitle('Software Engineer')
    ->setDescription('Develop cool stuff')
    ->setDatePosted('2023-01-01')
    ->setValidThrough('2024-01-01')
    ->setEmploymentType('FULL_TIME')
    ->setHiringOrganization('Tech Corp')
    ->setJobLocation('New York')
    ->setBaseSalary(['currency' => 'USD', 'value' => ['@type' => 'QuantitativeValue', 'value' => 100000]])
    ->setApplicantLocationRequirements('US')
    ->setJobLocationType('TELECOMMUTE')
    ->setDirectApply(true);

$jobPostingOutput = $jobPosting->toArray();

testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('JobPosting', $jobPostingOutput['@type'] ?? null, 'JobPosting @type');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Software Engineer', $jobPostingOutput['title'] ?? null, 'JobPosting title');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Develop cool stuff', $jobPostingOutput['description'] ?? null, 'JobPosting description');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('2023-01-01', $jobPostingOutput['datePosted'] ?? null, 'JobPosting datePosted');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('2024-01-01', $jobPostingOutput['validThrough'] ?? null, 'JobPosting validThrough');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('FULL_TIME', $jobPostingOutput['employmentType'] ?? null, 'JobPosting employmentType');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['@type' => 'Organization', 'name' => 'Tech Corp'], $jobPostingOutput['hiringOrganization'] ?? null, 'JobPosting hiringOrganization normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['@type' => 'Place', 'name' => 'New York'], $jobPostingOutput['jobLocation'] ?? null, 'JobPosting jobLocation normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['currency' => 'USD', 'value' => ['@type' => 'QuantitativeValue', 'value' => 100000], '@type' => 'MonetaryAmount'], $jobPostingOutput['baseSalary'] ?? null, 'JobPosting baseSalary normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['@type' => 'Country', 'name' => 'US'], $jobPostingOutput['applicantLocationRequirements'] ?? null, 'JobPosting applicantLocationRequirements normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('TELECOMMUTE', $jobPostingOutput['jobLocationType'] ?? null, 'JobPosting jobLocationType');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(true, $jobPostingOutput['directApply'] ?? null, 'JobPosting directApply');

// 3. CourseJsonLdBuilder
$course = new CourseJsonLdBuilder();
$course->setName('Intro to PHP')
    ->setDescription('Learn PHP basics')
    ->setProvider('University')
    ->setCourseCode('CS101')
    ->setEducationalCredentialAwarded('Certificate')
    ->setHasCourseInstance(['courseMode' => 'online'])
    ->addCourseInstance(['courseMode' => 'onsite'])
    ->setOffers([['@type' => 'Offer', 'price' => '100.00']])
    ->setAggregateRating(['ratingValue' => '4.5']);

$courseOutput = $course->toArray();

testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Course', $courseOutput['@type'] ?? null, 'Course @type');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Intro to PHP', $courseOutput['name'] ?? null, 'Course name');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Learn PHP basics', $courseOutput['description'] ?? null, 'Course description');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['@type' => 'Organization', 'name' => 'University'], $courseOutput['provider'] ?? null, 'Course provider normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('CS101', $courseOutput['courseCode'] ?? null, 'Course courseCode');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Certificate', $courseOutput['educationalCredentialAwarded'] ?? null, 'Course credential');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue([
    ['courseMode' => 'online', '@type' => 'CourseInstance'],
    ['courseMode' => 'onsite', '@type' => 'CourseInstance']
], $courseOutput['hasCourseInstance'] ?? null, 'Course instances normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue([['@type' => 'Offer', 'price' => '100.00']], $courseOutput['offers'] ?? null, 'Course offers');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['ratingValue' => '4.5', '@type' => 'AggregateRating'], $courseOutput['aggregateRating'] ?? null, 'Course aggregateRating normalized');

// 4. SoftwareApplicationJsonLdBuilder
$software = new SoftwareApplicationJsonLdBuilder();
$software->setName('My App')
    ->setDescription('Best app ever')
    ->setApplicationCategory('UtilitiesApplication')
    ->setOperatingSystem('Android')
    ->setSoftwareVersion('1.0')
    ->setOffers([['@type' => 'Offer', 'price' => '0']])
    ->setAggregateRating(['ratingValue' => '4.8'])
    ->setAuthor('App Dev')
    ->setPublisher('App Studio')
    ->setDownloadUrl('https://example.com/download')
    ->setScreenshot('screenshot.jpg');

$softwareOutput = $software->toArray();

testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('SoftwareApplication', $softwareOutput['@type'] ?? null, 'SoftwareApplication @type');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('My App', $softwareOutput['name'] ?? null, 'SoftwareApplication name');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Best app ever', $softwareOutput['description'] ?? null, 'SoftwareApplication description');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('UtilitiesApplication', $softwareOutput['applicationCategory'] ?? null, 'SoftwareApplication category');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('Android', $softwareOutput['operatingSystem'] ?? null, 'SoftwareApplication os');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('1.0', $softwareOutput['softwareVersion'] ?? null, 'SoftwareApplication version');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue([['@type' => 'Offer', 'price' => '0']], $softwareOutput['offers'] ?? null, 'SoftwareApplication offers');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['ratingValue' => '4.8', '@type' => 'AggregateRating'], $softwareOutput['aggregateRating'] ?? null, 'SoftwareApplication aggregateRating');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['@type' => 'Person', 'name' => 'App Dev'], $softwareOutput['author'] ?? null, 'SoftwareApplication author normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue(['@type' => 'Organization', 'name' => 'App Studio'], $softwareOutput['publisher'] ?? null, 'SoftwareApplication publisher normalized');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('https://example.com/download', $softwareOutput['downloadUrl'] ?? null, 'SoftwareApplication downloadUrl');
testPhase13LSpecializedRichResultsJsonLdBuildersTestAssertSameValue('screenshot.jpg', $softwareOutput['screenshot'] ?? null, 'SoftwareApplication screenshot');

echo "All Phase 13L Specialized Rich Results tests passed!\n";
