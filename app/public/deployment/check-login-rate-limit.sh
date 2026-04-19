#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <base-url> [attempts]"
  echo "Example: $0 https://example.com 30"
  exit 1
fi

BASE_URL="${1%/}"
ATTEMPTS="${2:-30}"
TARGET="${BASE_URL}/wp-login.php"

count_200=0
count_302=0
count_403=0
count_429=0
count_other=0

echo "Testing wp-login POST rate limit: $TARGET"
echo "Total attempts: $ATTEMPTS"

for ((i=1; i<=ATTEMPTS; i++)); do
  code="$(curl -s -o /dev/null -w "%{http_code}" -X POST "$TARGET" \
    --data "log=rate_limit_probe&pwd=invalid_password&wp-submit=Log+In")"

  case "$code" in
    200) ((count_200+=1)) ;;
    302) ((count_302+=1)) ;;
    403) ((count_403+=1)) ;;
    429) ((count_429+=1)) ;;
    *) ((count_other+=1)) ;;
  esac

  printf "Attempt %02d -> HTTP %s\n" "$i" "$code"
  sleep 0.1
done

echo
echo "Summary:"
echo "  200: $count_200"
echo "  302: $count_302"
echo "  403: $count_403"
echo "  429: $count_429"
echo "  other: $count_other"

if [[ "$count_429" -gt 0 ]]; then
  echo "Rate limit is active (429 observed)."
else
  echo "No 429 observed. Check Nginx limit_req config and reload status."
fi
