#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <base-url>"
  echo "Example: $0 https://example.com"
  exit 1
fi

BASE_URL="${1%/}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

pass_count=0
fail_count=0

echo "Running health checks for: $BASE_URL"
echo

run_check() {
  local name="$1"
  local cmd="$2"

  echo "== $name =="
  local output
  if ! output="$(eval "$cmd" 2>&1)"; then
    echo "$output"
    echo "Result: FAIL (script execution error)"
    echo
    ((fail_count+=1))
    return
  fi

  echo "$output"

  case "$name" in
    "FastCGI cache")
      if echo "$output" | grep -Eq 'HIT:[[:space:]]*[1-9][0-9]*'; then
        echo "Result: PASS"
        ((pass_count+=1))
      else
        echo "Result: FAIL (no HIT observed)"
        ((fail_count+=1))
      fi
      ;;
    "SEO baseline")
      if echo "$output" | grep -Eq 'HTTP/[0-9.]+ 200'; then
        local ok_count
        ok_count="$(echo "$output" | grep -Ec 'HTTP/[0-9.]+ 200')"
        if [[ "$ok_count" -ge 2 ]]; then
          echo "Result: PASS"
          ((pass_count+=1))
        else
          echo "Result: FAIL (robots or sitemap is not HTTP 200)"
          ((fail_count+=1))
        fi
      else
        echo "Result: FAIL (robots or sitemap is not HTTP 200)"
        ((fail_count+=1))
      fi
      ;;
    "wp-login rate limit")
      if echo "$output" | grep -Eq '429:[[:space:]]*[1-9][0-9]*'; then
        echo "Result: PASS"
        ((pass_count+=1))
      else
        echo "Result: FAIL (no 429 observed)"
        ((fail_count+=1))
      fi
      ;;
    *)
      echo "Result: FAIL (unknown check)"
      ((fail_count+=1))
      ;;
  esac

  echo
}

run_check "FastCGI cache" "\"$SCRIPT_DIR/check-fastcgi-cache.sh\" \"$BASE_URL/\" 6"
run_check "SEO baseline" "\"$SCRIPT_DIR/check-seo-basics.sh\" \"$BASE_URL\""
run_check "wp-login rate limit" "\"$SCRIPT_DIR/check-login-rate-limit.sh\" \"$BASE_URL\" 30"

echo "=============================="
echo "Health check summary"
echo "PASS: $pass_count"
echo "FAIL: $fail_count"
echo "=============================="

if [[ "$fail_count" -gt 0 ]]; then
  exit 1
fi

echo "Overall result: PASS"
