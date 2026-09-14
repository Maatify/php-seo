<?php

declare(strict_types=1);

namespace Maatify\Seo\Tests;

use Maatify\Seo\Web\JsonLd\Builder\ReviewJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\AggregateRatingJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\OfferJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\ServiceJsonLdBuilder;
use Maatify\Seo\Web\JsonLd\Builder\LocalBusinessJsonLdBuilder;
use RuntimeException;

require_once __DIR__ . '/../src/Web/JsonLd/Builder/JsonLdBuilderInterface.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/JsonLdBuilderTrait.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/AbstractJsonLdBuilder.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/ReviewJsonLdBuilder.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/AggregateRatingJsonLdBuilder.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/OfferJsonLdBuilder.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/ServiceJsonLdBuilder.php';
require_once __DIR__ . '/../src/Web/JsonLd/Builder/LocalBusinessJsonLdBuilder.php';

function testPhase13ICommerceJsonLdBuildersTestAssertSameValue(mixed $expected, mixed $actual, string $message = ''): void {
    if ($expected !== $actual) {
        throw new RuntimeException("Assertion failed: $message. Expected " . json_encode($expected) . ", got " . json_encode($actual));
    }
}

// 1. ReviewJsonLdBuilder
$review = (new ReviewJsonLdBuilder())
    ->setItemReviewed('Product Name')
    ->setReviewRating(4, 5.0, 1.0)
    ->setAuthor('John Doe')
    ->setName('Great product!')
    ->setReviewBody('I really loved using this product.')
    ->setDatePublished('2023-10-15')
    ->setPublisher('Awesome Review Site')
    ->toArray();

testPhase13ICommerceJsonLdBuildersTestAssertSameValue('Review', $review['@type']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('Product Name', $review['itemReviewed']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Rating', 'ratingValue' => 4, 'bestRating' => 5.0, 'worstRating' => 1.0], $review['reviewRating']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Person', 'name' => 'John Doe'], $review['author']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('Great product!', $review['name']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('I really loved using this product.', $review['reviewBody']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('2023-10-15', $review['datePublished']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Organization', 'name' => 'Awesome Review Site'], $review['publisher']);

// Array usages
$review2 = (new ReviewJsonLdBuilder())
    ->setItemReviewed(['@type' => 'Product', 'name' => 'Product 2'])
    ->setReviewRating(['@type' => 'Rating', 'ratingValue' => 3])
    ->setAuthor(['@type' => 'Person', 'name' => 'Jane Doe'])
    ->setPublisher(['@type' => 'Organization', 'name' => 'Reviewer Inc'])
    ->toArray();

testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Product', 'name' => 'Product 2'], $review2['itemReviewed']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Rating', 'ratingValue' => 3], $review2['reviewRating']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Person', 'name' => 'Jane Doe'], $review2['author']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Organization', 'name' => 'Reviewer Inc'], $review2['publisher']);

// 2. AggregateRatingJsonLdBuilder
$aggRating = (new AggregateRatingJsonLdBuilder())
    ->setRatingValue(4.5)
    ->setReviewCount(120)
    ->setRatingCount(150)
    ->setBestRating(5)
    ->setWorstRating(1)
    ->toArray();

testPhase13ICommerceJsonLdBuildersTestAssertSameValue('AggregateRating', $aggRating['@type']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(4.5, $aggRating['ratingValue']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(120, $aggRating['reviewCount']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(150, $aggRating['ratingCount']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(5, $aggRating['bestRating']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(1, $aggRating['worstRating']);

// 3. OfferJsonLdBuilder
$offer = (new OfferJsonLdBuilder())
    ->setPrice(29.99)
    ->setPriceCurrency('USD')
    ->setAvailability('https://schema.org/InStock')
    ->setUrl('https://example.com/offer')
    ->setValidFrom('2023-11-01')
    ->setPriceValidUntil('2023-12-31')
    ->setItemCondition('https://schema.org/NewCondition')
    ->setSeller('Store Name')
    ->toArray();

testPhase13ICommerceJsonLdBuildersTestAssertSameValue('Offer', $offer['@type']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(29.99, $offer['price']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('USD', $offer['priceCurrency']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('https://schema.org/InStock', $offer['availability']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('https://example.com/offer', $offer['url']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('2023-11-01', $offer['validFrom']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('2023-12-31', $offer['priceValidUntil']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('https://schema.org/NewCondition', $offer['itemCondition']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Organization', 'name' => 'Store Name'], $offer['seller']);

$offer2 = (new OfferJsonLdBuilder())
    ->setSeller(['@type' => 'Organization', 'name' => 'Store Name 2'])
    ->toArray();
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Organization', 'name' => 'Store Name 2'], $offer2['seller']);

// 4. ServiceJsonLdBuilder
$service = (new ServiceJsonLdBuilder())
    ->setName('Plumbing Service')
    ->setDescription('Professional plumbing services.')
    ->setServiceType('Home Repair')
    ->setProvider('Plumbers Inc')
    ->setAreaServed('New York')
    ->setOffers([
        ['@type' => 'Offer', 'price' => 50, 'priceCurrency' => 'USD']
    ])
    ->setAggregateRating([
        'ratingValue' => 4.8, 'reviewCount' => 20
    ])
    ->toArray();

testPhase13ICommerceJsonLdBuildersTestAssertSameValue('Service', $service['@type']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('Plumbing Service', $service['name']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('Professional plumbing services.', $service['description']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('Home Repair', $service['serviceType']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Organization', 'name' => 'Plumbers Inc'], $service['provider']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'Place', 'name' => 'New York'], $service['areaServed']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue([['@type' => 'Offer', 'price' => 50, 'priceCurrency' => 'USD']], $service['offers']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['ratingValue' => 4.8, 'reviewCount' => 20, '@type' => 'AggregateRating'], $service['aggregateRating']);

// 5. LocalBusinessJsonLdBuilder
$localBusiness = (new LocalBusinessJsonLdBuilder())
    ->setName('My Local Shop')
    ->setUrl('https://example.com/shop')
    ->setLogo('https://example.com/logo.png')
    ->setImage('https://example.com/image.jpg')
    ->setDescription('A great local shop.')
    ->setTelephone('555-1234')
    ->setEmail('contact@example.com')
    ->setAddress([
        'streetAddress' => '123 Main St'
    ])
    ->setGeo(40.7128, -74.0060)
    ->setOpeningHours([
        'Mo-Fr 09:00-17:00'
    ])
    ->addOpeningHours('Sa 10:00-14:00')
    ->setPriceRange('$$')
    ->setSameAs([
        'https://facebook.com/myshop'
    ])
    ->addSameAs('https://twitter.com/myshop')
    ->setAggregateRating([
        'ratingValue' => 4.9, 'reviewCount' => 50
    ])
    ->toArray();

testPhase13ICommerceJsonLdBuildersTestAssertSameValue('LocalBusiness', $localBusiness['@type']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('My Local Shop', $localBusiness['name']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('https://example.com/shop', $localBusiness['url']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('https://example.com/logo.png', $localBusiness['logo']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('https://example.com/image.jpg', $localBusiness['image']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('A great local shop.', $localBusiness['description']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('555-1234', $localBusiness['telephone']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('contact@example.com', $localBusiness['email']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['streetAddress' => '123 Main St', '@type' => 'PostalAddress'], $localBusiness['address']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['@type' => 'GeoCoordinates', 'latitude' => 40.7128, 'longitude' => -74.0060], $localBusiness['geo']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['Mo-Fr 09:00-17:00', 'Sa 10:00-14:00'], $localBusiness['openingHours']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue('$$', $localBusiness['priceRange']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['https://facebook.com/myshop', 'https://twitter.com/myshop'], $localBusiness['sameAs']);
testPhase13ICommerceJsonLdBuildersTestAssertSameValue(['ratingValue' => 4.9, 'reviewCount' => 50, '@type' => 'AggregateRating'], $localBusiness['aggregateRating']);

$localBusiness2 = (new LocalBusinessJsonLdBuilder())
    ->setPostalAddress('456 Elm St', 'Cityville', 'ST', '12345', 'US')
    ->toArray();
testPhase13ICommerceJsonLdBuildersTestAssertSameValue([
    '@type' => 'PostalAddress',
    'streetAddress' => '456 Elm St',
    'addressLocality' => 'Cityville',
    'addressRegion' => 'ST',
    'postalCode' => '12345',
    'addressCountry' => 'US'
], $localBusiness2['address']);

echo "Phase 13I Commerce JSON-LD Builders tests passed successfully.\n";
