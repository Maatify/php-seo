# CI operations

CI validates the library against current Composer dependencies, the declared platform, security advisories, PHPStan, PHP syntax, the standalone test suite, and the GitHub Actions workflow syntax.

## Prerequisites

Local parity requires PHP 8.2 or newer with the `xmlwriter` extension, Composer 2, Bash, `curl`, `tar`, and either `sha256sum` or `shasum`. Running real persistence Integration additionally requires `pdo_mysql` and a local MySQL service with a dedicated `_test` database and non-production user. The repository does not track `composer.lock`; dependency checks resolve a fresh compatible set.

## Current dependencies

Run strict metadata validation and resolve the newest compatible dependency set:

```bash
composer validate --strict
composer update --no-interaction --prefer-dist --no-progress
composer check-platform-reqs
```

Run the blocking security audit:

```bash
composer audit --no-interaction --abandoned=fail
```

Then check PHP syntax, analyze source and tests, and run the maintained standalone suite:

```bash
while IFS= read -r -d '' file; do
  php -l "$file"
done < <(find src tests examples -type f -name '*.php' -print0 | sort -z)

vendor/bin/phpstan analyse
find tests \
  -type f \
  -name '*Test.php' \
  ! -path 'tests/Integration/*' \
  -print0 \
  | sort -z \
  | xargs -0 -n1 php
```

## Lowest supported dependencies

Resolve the lowest stable versions allowed by the package constraints, then run the platform, audit, static-analysis, and standalone-test gates:

```bash
composer update \
  --prefer-lowest \
  --prefer-stable \
  --no-interaction \
  --prefer-dist \
  --no-progress

composer check-platform-reqs
composer audit --no-interaction --abandoned=fail
vendor/bin/phpstan analyse
find tests \
  -type f \
  -name '*Test.php' \
  ! -path 'tests/Integration/*' \
  -print0 \
  | sort -z \
  | xargs -0 -n1 php
```

## Real MySQL persistence Integration

The standalone suite excludes `tests/Integration/*`; the Integration test is a separate, fail-closed gate. It requires a real MySQL server and the PHP `pdo_mysql` extension. SQLite, mocks, and fakes are not valid substitutes. The test uses the three shipped SQL files, cleans up only the package-owned tables, verifies no residue, and must not be pointed at a production database.

Create a dedicated local test database and a non-production user with rights scoped to that database. For example, connect as a local MySQL administrator and run:

```sql
CREATE DATABASE maatify_seo_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'maatify_seo'@'127.0.0.1' IDENTIFIED BY 'maatify_seo_test_password';
GRANT ALL PRIVILEGES ON maatify_seo_test.* TO 'maatify_seo'@'127.0.0.1';
```

Set the test-only connection values and run the repeatability script:

```bash
export MAATIFY_SEO_TEST_DB_DSN='mysql:host=127.0.0.1;port=3306;dbname=maatify_seo_test;charset=utf8mb4'
export MAATIFY_SEO_TEST_DB_USER='maatify_seo'
export MAATIFY_SEO_TEST_DB_PASSWORD='maatify_seo_test_password'
bash scripts/ci/mysql-persistence-integration.sh
```

All three environment variables are required. The runner executes the Integration test twice against the same database; each run creates the shipped schemas, verifies persistence, cleans the three package tables, and checks `information_schema` for residue. Missing configuration, an invalid DSN, an unavailable service, or a failing assertion is an error, never a skip.

CI uses the official `mysql:8.4.11` image as a reproducible test fixture. This fixture version does not declare the package's minimum supported MySQL version. The required persistence matrix covers PHP 8.2 with current dependencies, PHP 8.5 with current dependencies, and PHP 8.2 with lowest supported dependencies.

## Workflow and change-range checks

Run the pinned actionlint release against all repository workflows:

```bash
bash scripts/ci/actionlint.sh
```

The CI whitespace check uses the actual event range. For a pull request it checks the complete change from the merge base to the PR head:

```bash
git diff --check "$PR_BASE_SHA...$PR_HEAD_SHA"
```

For a push to `main`, it checks the event's before and after commits:

```bash
git diff --check "$PUSH_BEFORE_SHA" "$PUSH_AFTER_SHA"
```

Both endpoints must be available commits. CI fetches full history for this check and fails if the event range cannot be verified.

## CI contract

The maintained standalone test matrix covers PHP 8.2, 8.3, 8.4, and 8.5. The real-MySQL persistence matrix covers PHP 8.2/current dependencies, PHP 8.5/current dependencies, and PHP 8.2/lowest dependencies. The stable terminal required check is **CI Gate**; it succeeds only when the quality, complete standalone test matrix, lowest-dependency, persistence-integration, and workflow-lint jobs all succeed.

Security advisories block CI. Abandoned packages block CI. Vulnerable direct dependencies block CI. Vulnerable transitive dependencies block CI. No security-audit ignore list is approved.

The maintained test runner is the standalone suite invoked with the sorted `find` command above. PHPUnit is not an optional or required runner in this repository.

The Consumer Verification Harness remains pending WU-7. Final package compliance therefore remains incomplete after WU-6 until that Harness is implemented and included in the required aggregate gate.
