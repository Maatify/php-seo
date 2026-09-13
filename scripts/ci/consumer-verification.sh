#!/usr/bin/env bash

set -euo pipefail

for variable in \
  MAATIFY_SEO_TEST_DB_DSN \
  MAATIFY_SEO_TEST_DB_USER \
  MAATIFY_SEO_TEST_DB_PASSWORD
do
  if [[ -z "${!variable:-}" ]]; then
    printf 'Required consumer verification environment variable [%s] is missing or empty.\n' "$variable" >&2
    exit 1
  fi
done

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
fixture_directory="${repo_root}/consumer-harness"
cd "$repo_root"

if ! command -v composer >/dev/null 2>&1; then
  echo 'Consumer verification requires Composer.' >&2
  exit 1
fi
if ! command -v rsync >/dev/null 2>&1; then
  echo 'Consumer verification requires rsync.' >&2
  exit 1
fi
if [[ ! -f "${fixture_directory}/composer.json" || ! -f "${fixture_directory}/verify.php" ]]; then
  echo 'The committed consumer harness fixture is incomplete.' >&2
  exit 1
fi
if [[ -e "${fixture_directory}/vendor" || -e "${fixture_directory}/composer.lock" ]]; then
  echo 'The committed consumer harness fixture must not contain vendor/ or composer.lock.' >&2
  exit 1
fi

temporary_directory="$(mktemp -d)"
trap 'rm -rf "$temporary_directory"' EXIT

package_under_test="${temporary_directory}/package-under-test"
mkdir -p "$package_under_test"
rsync -a \
  --exclude='/.git' \
  --exclude='/vendor' \
  --exclude='/composer.lock' \
  --exclude='/.phpstan.cache' \
  "${repo_root}/" "${package_under_test}/"

for consumer_number in 1 2
do
  consumer_directory="${temporary_directory}/consumer-${consumer_number}"
  mkdir -p "$consumer_directory"
  cp -R "${fixture_directory}/." "$consumer_directory/"

  if [[ -e "${consumer_directory}/vendor" || -e "${consumer_directory}/composer.lock" ]]; then
    echo "Fresh consumer ${consumer_number} unexpectedly contains vendor/ or composer.lock before install." >&2
    exit 1
  fi

  printf 'Installing and verifying clean external consumer %s of 2.\n' "$consumer_number"
  (
    cd "$consumer_directory"
    composer validate --strict
    composer update --no-dev --no-interaction --prefer-dist --no-progress
    composer check-platform-reqs --no-dev
    composer audit --no-dev --no-interaction --abandoned=fail
    composer show maatify/php-seo
    php verify.php
  )
done
