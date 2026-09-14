# Contributing to Maatify SEO

The Composer package identity is `maatify/php-seo`. This standalone, framework-agnostic library owns its SEO APIs and its own persistence behavior. Host applications own HTTP routes and responses, application entities and data, credentials, and framework-specific wiring. Contributions should preserve those boundaries and the current public contracts documented in [SEO_PACKAGE_REFERENCE.md](SEO_PACKAGE_REFERENCE.md).

## Ways to Contribute

Code, tests, examples, and documentation improvements are welcome. Before changing a public API, package boundary, dependency contract, or architecture, raise the design question with the maintainers so the intended compatibility impact is clear. A GitHub Issue is not required for every contribution.

Keep changes focused, update relevant documentation and tests, and describe the behavior and verification in the pull request. Do not include unrelated generated files or a `composer.lock` file.

## Local Setup

The repository does not track `composer.lock`; resolve the current compatible development dependencies from the repository root:

```bash
git clone https://github.com/Maatify/php-seo.git
cd php-seo
composer validate --strict
composer update --no-interaction --prefer-dist --no-progress
composer check-platform-reqs
```

Use PHP 8.2 or newer with `ext-json`, `ext-pdo`, and `ext-xmlwriter`, Composer 2, Bash, `curl`, `tar`, `rsync`, and `sha256sum` or `shasum`. The runtime Composer requirements are also listed in [composer.json](composer.json).

## Local Verification

Run the non-mutating security audit, PHP syntax check, PHPStan, standalone test suite, workflow lint, and whitespace check:

```bash
set -euo pipefail

composer audit --no-interaction --abandoned=fail

while IFS= read -r -d '' file; do
  php -l "$file"
done < <(find src tests examples consumer-harness -type f -name '*.php' -print0 | sort -z)

vendor/bin/phpstan analyse
find tests \
  -type f \
  -name '*Test.php' \
  ! -path 'tests/Integration/*' \
  -print0 \
  | sort -z \
  | xargs -0 -n1 php

bash scripts/ci/actionlint.sh
git diff --check
```

The maintained runner is the standalone PHP test suite above; this repository does not use PHPUnit. The standalone command intentionally excludes `tests/Integration/*`.

### Real MySQL Integration and Consumer Verification

The persistence Integration test and Consumer Verification Harness require `pdo_mysql` and a real local MySQL service with a dedicated non-production test database whose name ends in `_test`. Configure `MAATIFY_SEO_TEST_DB_DSN`, `MAATIFY_SEO_TEST_DB_USER`, and `MAATIFY_SEO_TEST_DB_PASSWORD` for that database, then run:

```bash
bash scripts/ci/mysql-persistence-integration.sh
bash scripts/ci/consumer-verification.sh
```

Both scripts fail when the required local database configuration or service is unavailable. The Integration script repeats its test and checks cleanup; the Consumer Verification Harness installs the package through Composer from a separate consumer root and uses the package's production autoload. SQLite, mocks, and fakes are not substitutes for these real persistence gates. See [docs/CI.md](docs/CI.md) for database setup, matrix details, and complete local/CI parity requirements.

## Pull Requests and Public Contracts

Explain the problem, the scope of the change, and the checks you ran. Include tests and documentation when observable behavior or a supported contract changes. Keep runtime dependencies direct and justified; do not introduce framework coupling or require host-specific bindings.

Changes to public API, compatibility, persistence semantics, package identity, or architecture need maintainer review before implementation proceeds. Link the relevant design discussion when one exists; an issue is not a prerequisite for every change.

## Security Reports

Do not report vulnerabilities in a public GitHub issue. Send them privately to **support@maatify.com**. See [SECURITY.md](SECURITY.md) for the reporting policy.

## Lock-File Policy

This reusable Composer library does not commit `composer.lock`. Composer may generate a local lock file during dependency resolution; do not add it to a commit.
