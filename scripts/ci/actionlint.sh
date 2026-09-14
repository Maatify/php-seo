#!/usr/bin/env bash

set -euo pipefail

version='1.7.12'
os="$(uname -s)"
architecture="$(uname -m)"

case "${os}/${architecture}" in
    Linux/x86_64)
        platform='linux_amd64'
        expected_sha256='8aca8db96f1b94770f1b0d72b6dddcb1ebb8123cb3712530b08cc387b349a3d8'
        ;;
    Darwin/arm64|Darwin/aarch64)
        platform='darwin_arm64'
        expected_sha256='aba9ced2dee8d27fecca3dc7feb1a7f9a52caefa1eb46f3271ea66b6e0e6953f'
        ;;
    Darwin/x86_64)
        platform='darwin_amd64'
        expected_sha256='5b44c3bc2255115c9b69e30efc0fecdf498fdb63c5d58e17084fd5f16324c644'
        ;;
    *)
        printf 'Unsupported actionlint platform: %s/%s\n' "$os" "$architecture" >&2
        exit 1
        ;;
esac

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$repo_root"

temporary_directory="$(mktemp -d)"
trap 'rm -rf "$temporary_directory"' EXIT

archive="${temporary_directory}/actionlint_${version}_${platform}.tar.gz"
url="https://github.com/rhysd/actionlint/releases/download/v${version}/actionlint_${version}_${platform}.tar.gz"
curl --fail --location --silent --show-error "$url" --output "$archive"

if command -v sha256sum >/dev/null 2>&1; then
    actual_sha256="$(sha256sum "$archive" | awk '{print $1}')"
elif command -v shasum >/dev/null 2>&1; then
    actual_sha256="$(shasum -a 256 "$archive" | awk '{print $1}')"
else
    echo 'Neither sha256sum nor shasum is available to verify the actionlint archive.' >&2
    exit 1
fi

if [[ "$actual_sha256" != "$expected_sha256" ]]; then
    printf 'actionlint archive SHA-256 mismatch: expected %s, received %s\n' "$expected_sha256" "$actual_sha256" >&2
    exit 1
fi

tar -xzf "$archive" -C "$temporary_directory" actionlint

shopt -s nullglob
workflow_files=(.github/workflows/*.yml .github/workflows/*.yaml)
if ((${#workflow_files[@]} == 0)); then
    echo 'No GitHub Actions workflow files found under .github/workflows.' >&2
    exit 1
fi

"${temporary_directory}/actionlint" "${workflow_files[@]}"
