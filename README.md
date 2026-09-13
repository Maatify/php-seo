<div align="center">

# Maatify SEO Library

![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

[![Status](https://img.shields.io/badge/Status-Pre--Stable%20%2F%20Development-orange?style=for-the-badge)](SEO_PACKAGE_REFERENCE.md)
[![PHP](https://img.shields.io/badge/PHP-%5E8.2-777BB4?style=for-the-badge)](composer.json)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-4E8CAE?style=for-the-badge)](docs/CI.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-blueviolet?style=for-the-badge)](https://github.com/Maatify)

[![Changelog](https://img.shields.io/badge/Changelog-View-blue?style=for-the-badge)](CHANGELOG.md)
[![Package Reference](https://img.shields.io/badge/Package%20Reference-Read-blue?style=for-the-badge)](SEO_PACKAGE_REFERENCE.md)
[![Security Policy](https://img.shields.io/badge/Security-Policy-blue?style=for-the-badge)](SECURITY.md)
[![Contributing Guide](https://img.shields.io/badge/Contributing-Guide-blue?style=for-the-badge)](CONTRIBUTING.md)

Framework-agnostic SEO tools for PHP: metadata, structured data, sitemaps, validation, and host-integrated persistence.

</div>

---

## Package Summary

`maatify/php-seo` is a standalone PHP library that builds SEO metadata and structured output for a host application. It provides framework-neutral builders, validators, renderers, service contracts, and PDO adapters for its own SEO tables. The host owns HTTP routing and responses, its entities and application data, credentials, and delivery decisions.

The package is **Pre-Stable / Development** and is being prepared for a corrected SemVer Release Candidate lifecycle. This status does not claim that a corrected RC or Stable release has been published.

## Key Features

- Create metadata, canonical links, hreflang links, robots directives, HTML head output, and sitemap XML strings.
- Build Schema.org-oriented JSON-LD through typed builders. Validation is scoped; generation does not establish complete Schema.org validation or Google Rich Results or Merchant eligibility.
- Generate sitemap data and XML through base/strict DTO fields; provider/profile validation boundaries remain separate from generic generation.
- Validate SEO metadata and selected protocol/profile boundaries, with reports, scores, and exports.
- Use package-owned PDO repositories and schemas for redirects, SEO overrides, and slug history. The host supplies PDO and connection configuration; the package ships concrete PDO repositories and its own schemas.
- Generate Open Graph and Twitter Card compatibility output; Twitter/X provider conformance was not source-verified.
- Integrate optional Search Console and Merchant Center transport contracts while keeping HTTP, OAuth, credentials, and network behavior in the host.
- Use optional `spatie/schema-org` adaptation when that integration is needed.

## Requirements

- PHP `^8.2`
- PHP extensions: `ext-json`, `ext-pdo`, and `ext-xmlwriter`
- Runtime package: `maatify/exceptions ^1.0`
- Optional integration: `spatie/schema-org`
- The persistence adapters use MySQL-compatible schemas through PDO. CI's MySQL `8.4.11` image is a reproducibility fixture, not a declared minimum supported MySQL version.

## Installation and Current Access

The Composer identity is `maatify/php-seo`. The repository is currently in Pre-Stable / Development. As of 2026-09-13, Packagist has no record for this identity, and no other external Composer distribution has been verified. Therefore, no public `composer require` command is provided yet.

To work from the repository checkout:

```bash
git clone https://github.com/Maatify/php-seo.git
cd php-seo
composer update --no-interaction --prefer-dist --no-progress
```

See [CONTRIBUTING.md](CONTRIBUTING.md) and [docs/CI.md](docs/CI.md) for local setup and verification requirements.

## Quick Usage

Create metadata and render the HTML head output through the public runtime API:

```php
use Maatify\Seo\Shared\DTO\MetaTagsDTO;
use Maatify\Seo\Web\Render\SeoHeadHtmlRenderer;

$metaTags = new MetaTagsDTO(
    title: 'About Us',
    description: 'Learn more about our website.',
    canonicalUrl: 'https://example.com/about',
    openGraphTitle: 'About Us',
    openGraphDescription: 'Learn more about our website.',
    openGraphUrl: 'https://example.com/about',
    openGraphType: 'website',
);

$renderer = new SeoHeadHtmlRenderer();
echo $renderer->render($metaTags);
```

The host decides how to deliver the returned output. See the [usage guide](docs/guides/USAGE_GUIDE.md) for more examples.

## Documentation

- [Documentation index](docs/README.md)
- [Canonical package reference](SEO_PACKAGE_REFERENCE.md)
- [Usage guide](docs/guides/USAGE_GUIDE.md)
- [Integration guide](docs/guides/INTEGRATION_GUIDE.md)
- [CI operations and local verification](docs/CI.md)
- [Engineering handbook](docs/SEO/library/README.md)
- [Changelog](CHANGELOG.md)
- [Security policy](SECURITY.md)
- [Contributing guide](CONTRIBUTING.md)

## Quality Status

The repository configures these verification gates:

- Standalone test matrix on PHP 8.2, 8.3, 8.4, and 8.5.
- PHPStan level max over `src` and `tests`.
- Strict Composer validation, platform requirement checks, compatible dependency resolution, Composer security and abandoned-package audit, and lowest dependencies on PHP 8.2.
- Real MySQL persistence Integration, including PHP 8.2/current dependencies, PHP 8.5/current dependencies, and PHP 8.2/lowest dependencies.
- Consumer Verification Harness on PHP 8.2 and PHP 8.5.
- GitHub Actions workflow lint and the terminal required check, `CI Gate`.

These are configured checks. Their presence does not establish that a particular CI run passed, that a corrected RC is published, or that the package is eligible for Stable release.

## License

This package is licensed under the [MIT License](LICENSE).

## 👤 Author

Engineered by **Mohamed Abdulalim** ([@megyptm](https://github.com/megyptm))<br>
Backend Lead & Technical Architect<br>
[https://www.maatify.dev](https://www.maatify.dev)

---

<div align="center">

[Built with ❤️ by Maatify.dev — Unified Ecosystem for Modern PHP Libraries](https://www.maatify.dev)

</div>
