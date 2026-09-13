#!/usr/bin/env bash

set -euo pipefail

for variable in \
  MAATIFY_SEO_TEST_DB_DSN \
  MAATIFY_SEO_TEST_DB_USER \
  MAATIFY_SEO_TEST_DB_PASSWORD
do
  if [[ -z "${!variable:-}" ]]; then
    printf 'Required MySQL Integration environment variable [%s] is missing or empty.\n' "$variable" >&2
    exit 1
  fi
done

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$repo_root"

printf 'MySQL persistence Integration run 1 of 2.\n'
php tests/Integration/MySqlPersistenceIntegrationTest.php

printf 'MySQL persistence Integration run 2 of 2.\n'
php tests/Integration/MySqlPersistenceIntegrationTest.php
