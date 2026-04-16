#!/usr/bin/env bash
set -euo pipefail

CACHE_DIR="${1:-/tmp/nginx-fastcgi-cache}"

if [[ ! -d "$CACHE_DIR" ]]; then
  echo "Cache directory not found: $CACHE_DIR"
  exit 1
fi

find "$CACHE_DIR" -mindepth 1 -delete

echo "FastCGI cache cleared: $CACHE_DIR"
