#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <base-url>"
  echo "Example: $0 https://example.com"
  exit 1
fi

BASE_URL="${1%/}"

check_url() {
  local path="$1"
  local url="${BASE_URL}${path}"
  echo "Checking: $url"
  curl -sSI "$url" | awk 'BEGIN{IGNORECASE=1} /^HTTP\// || /^content-type:/ || /^cache-control:/ || /^x-fastcgi-cache:/ {print}'
  echo
}

check_url "/robots.txt"
check_url "/wp-sitemap.xml"
check_url "/"
