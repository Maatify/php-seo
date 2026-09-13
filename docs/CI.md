# CI operations

CI validates the library against current Composer dependencies, the declared platform, security advisories, PHPStan, PHP syntax, the standalone test suite, and the GitHub Actions workflow syntax.

## Prerequisites

Local parity requires PHP 8.2 or newer with the `xmlwriter` extension, Composer 2, Bash, `curl`, `tar`, and either `sha256sum` or `shasum`. The repository does not track `composer.lock`; dependency checks resolve a fresh compatible set.

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
find tests -name '*Test.php' -print0 | sort -z | xargs -0 -n1 php
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
find tests -name '*Test.php' -print0 | sort -z | xargs -0 -n1 php
```

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

The maintained test matrix covers PHP 8.2, 8.3, 8.4, and 8.5. The stable terminal required check is **CI Gate**; it succeeds only when the quality, complete test matrix, lowest-dependency, and workflow-lint jobs all succeed.

Security advisories block CI. Abandoned packages block CI. Vulnerable direct dependencies block CI. Vulnerable transitive dependencies block CI. No security-audit ignore list is approved.

The maintained test runner is the standalone suite invoked with the sorted `find` command above. PHPUnit is not an optional or required runner in this repository.

MySQL and real-persistence Integration are intentionally outside WU-5 and belong to WU-6. The Consumer Verification Harness is intentionally outside WU-5 and belongs to WU-7. The final compliance gate is therefore incomplete after WU-5 alone.
