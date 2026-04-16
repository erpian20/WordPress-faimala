#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <url> [count]"
  exit 1
fi

URL="$1"
COUNT="${2:-3}"
TMP_FILE="$(mktemp)"
trap 'rm -f "$TMP_FILE"' EXIT

for ((i=1; i<=COUNT; i++)); do
  echo "Request #$i -> $URL"
  RESPONSE="$(curl -sSI "$URL")"
  echo "$RESPONSE" | awk 'BEGIN{IGNORECASE=1} /^HTTP\// || /^x-fastcgi-cache:/ || /^cache-control:/ {print}'

  CACHE_STATUS="$(echo "$RESPONSE" | awk 'BEGIN{IGNORECASE=1} /^x-fastcgi-cache:/ {print toupper($2)}' | tr -d '\r')"
  if [[ -z "$CACHE_STATUS" ]]; then
    CACHE_STATUS="NONE"
  fi
  echo "$CACHE_STATUS" >> "$TMP_FILE"

  echo
  sleep 0.2
done

echo "Summary (X-FastCGI-Cache):"
sort "$TMP_FILE" | uniq -c | awk '{printf "  %s: %s\n", $2, $1}'
