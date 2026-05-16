#!/usr/bin/env bash
# cleanup.sh — undo everything install-rehearsal.sh did, without nuking the Local site.
#
# Deactivates + deletes the six workshop plugins, removes the subscriber user,
# revokes the rehearsal Application Password, and deletes the seeded posts.
#
# Run from inside Local's "Open site shell".
#   ./cleanup.sh                    # interactive — confirms before each destructive step
#   ./cleanup.sh --yes              # non-interactive — assumes yes to everything

set -euo pipefail

ASSUME_YES=0
if [[ "${1:-}" == "--yes" || "${1:-}" == "-y" ]]; then
  ASSUME_YES=1
fi

confirm() {
  local prompt="$1"
  if [[ "$ASSUME_YES" -eq 1 ]]; then
    return 0
  fi
  read -r -p "$prompt [y/N] " yn
  case "$yn" in [Yy]*) return 0 ;; *) return 1 ;; esac
}

if ! command -v wp >/dev/null 2>&1; then
  echo "Error: wp-cli not on PATH. Open this shell via Local's 'Open site shell'." >&2
  exit 1
fi

wp core is-installed >/dev/null 2>&1 || {
  echo "Error: no WordPress install detected in current directory." >&2
  exit 1
}

EXERCISES=(
  "01-hello-ability"
  "02-input-schema"
  "03-permission-gate"
  "04-output-schema"
  "05-real-data-ability"
  "06-rest-and-ai-tool"
)

echo "==> Deactivating and deleting workshop plugins"
if confirm "Remove all six wcporto-* plugins?"; then
  for ex in "${EXERCISES[@]}"; do
    slug="wcporto-$ex"
    if wp plugin is-installed "$slug" >/dev/null 2>&1; then
      wp plugin deactivate "$slug" --quiet 2>/dev/null || true
      wp plugin delete     "$slug" --quiet 2>/dev/null || true
      echo "  ✓ removed $slug"
    else
      echo "  • $slug not installed"
    fi
  done
else
  echo "  • skipped"
fi

echo
echo "==> Removing subscriber user 'wsub'"
if wp user get wsub --field=ID >/dev/null 2>&1; then
  if confirm "Delete user 'wsub' (and reassign their content to admin)?"; then
    ADMIN_ID=$(wp user list --role=administrator --field=ID --format=csv | head -n1)
    wp user delete wsub --reassign="$ADMIN_ID" --yes --quiet
    echo "  ✓ user 'wsub' deleted (content reassigned to user $ADMIN_ID)"
  else
    echo "  • skipped"
  fi
else
  echo "  • 'wsub' not present"
fi

echo
echo "==> Revoking rehearsal Application Password"
ADMIN_LOGIN=$(wp user list --role=administrator --field=user_login --format=csv | head -n1)
if [[ -n "$ADMIN_LOGIN" ]]; then
  # Find UUIDs whose name matches "WCPorto Rehearsal".
  UUIDS=$(wp user application-password list "$ADMIN_LOGIN" --name="WCPorto Rehearsal" --field=uuid 2>/dev/null || true)
  if [[ -n "$UUIDS" ]]; then
    if confirm "Revoke all 'WCPorto Rehearsal' app passwords for '$ADMIN_LOGIN'?"; then
      while read -r uuid; do
        [[ -z "$uuid" ]] && continue
        wp user application-password delete "$ADMIN_LOGIN" "$uuid" --quiet
        echo "  ✓ revoked $uuid"
      done <<<"$UUIDS"
    else
      echo "  • skipped"
    fi
  else
    echo "  • no 'WCPorto Rehearsal' app password to revoke"
  fi
fi

echo
echo "==> Deleting seeded demo posts"
SEEDED_IDS=$(wp post list \
  --post_type=post \
  --post_status=any \
  --post_title__in="Hello Porto","Draft to publish" \
  --field=ID 2>/dev/null || true)

# `--post_title__in` is not a stock WP_Query arg; fall back to a meta-free match.
if [[ -z "$SEEDED_IDS" ]]; then
  SEEDED_IDS=$(wp post list --post_status=any --field=ID --format=csv \
    --s='Hello Porto' 2>/dev/null || true)
  SEEDED_IDS+=$'\n'$(wp post list --post_status=any --field=ID --format=csv \
    --s='Draft to publish' 2>/dev/null || true)
fi

SEEDED_IDS=$(echo "$SEEDED_IDS" | tr ',' '\n' | sort -u | grep -E '^[0-9]+$' || true)

if [[ -n "$SEEDED_IDS" ]]; then
  echo "  Candidate posts:"
  while read -r pid; do
    [[ -z "$pid" ]] && continue
    title=$(wp post get "$pid" --field=post_title 2>/dev/null || echo "?")
    echo "    - #$pid  $title"
  done <<<"$SEEDED_IDS"

  if confirm "Permanently delete these posts?"; then
    while read -r pid; do
      [[ -z "$pid" ]] && continue
      wp post delete "$pid" --force --quiet
      echo "  ✓ deleted post #$pid"
    done <<<"$SEEDED_IDS"
  else
    echo "  • skipped"
  fi
else
  echo "  • no seeded posts found"
fi

echo
echo "============================================================"
echo "  Cleanup complete."
echo "============================================================"
echo
echo "The Local site itself is untouched. Run install-rehearsal.sh"
echo "again whenever you want to redo the setup."
