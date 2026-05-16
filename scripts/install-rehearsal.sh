#!/usr/bin/env bash
# install-rehearsal.sh — drops all six workshop plugins (solution-filled) into
# the active WordPress site, activates them, seeds demo data, and generates an
# Application Password for the admin user.
#
# Run this from inside Local's "Open site shell" (which sets PWD to the WP
# root and puts wp-cli on PATH). Pass the path to the workshop repo as $1.
#
#   ./install-rehearsal.sh "/Users/you/Desktop/WordCamp Porto 2026/wcporto-2026-abilities-workshop"

set -euo pipefail

WORKSHOP_DIR="${1:-}"

if [[ -z "$WORKSHOP_DIR" ]]; then
  echo "Usage: $0 <path-to-wcporto-2026-abilities-workshop>" >&2
  exit 1
fi

if [[ ! -d "$WORKSHOP_DIR/exercises" ]]; then
  echo "Error: $WORKSHOP_DIR does not look like the workshop repo (no exercises/ subdir)." >&2
  exit 1
fi

if ! command -v wp >/dev/null 2>&1; then
  echo "Error: wp-cli not on PATH. Open this shell via Local's 'Open site shell' menu." >&2
  exit 1
fi

# Confirm we're talking to a WordPress install.
wp core is-installed >/dev/null 2>&1 || {
  echo "Error: no WordPress install detected in current directory." >&2
  exit 1
}

# Confirm WP 6.9+.
WP_VERSION=$(wp core version)
echo "WordPress version: $WP_VERSION"
case "$WP_VERSION" in
  6.9*|7.*|8.*|9.*) ;;
  *)
    echo "Warning: this workshop requires WordPress 6.9+. You are on $WP_VERSION." >&2
    echo "Continue at your own risk (the Abilities API will not exist on older versions)." >&2
    read -r -p "Continue anyway? [y/N] " yn
    case "$yn" in [Yy]*) ;; *) exit 1 ;; esac
    ;;
esac

PLUGIN_DIR="$(wp plugin path)"
echo "Plugin directory: $PLUGIN_DIR"

EXERCISES=(
  "01-hello-ability"
  "02-input-schema"
  "03-permission-gate"
  "04-output-schema"
  "05-real-data-ability"
  "06-rest-and-ai-tool"
)

echo
echo "==> Copying plugins (solutions filled in)"
for ex in "${EXERCISES[@]}"; do
  SRC="$WORKSHOP_DIR/exercises/$ex"
  DEST="$PLUGIN_DIR/wcporto-$ex"

  if [[ ! -d "$SRC" ]]; then
    echo "  ! skipping $ex (source not found at $SRC)"
    continue
  fi

  # Wipe any prior install so reruns are idempotent.
  rm -rf "$DEST"
  mkdir -p "$DEST"

  # Copy the exercise's top-level files (main plugin file, tools/, etc.) — but
  # not the solution/ folder, and not the starter includes/.
  for entry in "$SRC"/*; do
    name="$(basename "$entry")"
    case "$name" in
      solution|includes|README.md) continue ;;
      *) cp -R "$entry" "$DEST/" ;;
    esac
  done

  # Replace starter includes/ with the solution version.
  cp -R "$SRC/solution/includes" "$DEST/includes"

  echo "  ✓ wcporto-$ex"
done

echo
echo "==> Activating plugins"
for ex in "${EXERCISES[@]}"; do
  wp plugin activate "wcporto-$ex" --quiet
  echo "  ✓ wcporto-$ex activated"
done

echo
echo "==> Seeding demo data"

# Subscriber user for exercise 3 (Gate 2 rejection demo).
if ! wp user get wsub --field=ID >/dev/null 2>&1; then
  wp user create wsub wsub@example.com --role=subscriber --user_pass=test1234 --quiet
  echo "  ✓ subscriber user 'wsub' created (password: test1234)"
else
  echo "  • subscriber user 'wsub' already exists"
fi

# Published post + draft for exercise 5.
wp post create --post_title="Hello Porto" --post_status=publish --porcelain >/dev/null
echo "  ✓ published post 'Hello Porto' created"

DRAFT_ID=$(wp post create --post_title="Draft to publish" --post_status=draft --porcelain)
echo "  ✓ draft post created with ID $DRAFT_ID (use this in the exercise 5 write demo)"

echo
echo "==> Generating Application Password for admin (exercise 6)"
ADMIN_LOGIN=$(wp user list --role=administrator --field=user_login --format=csv | head -n1)
if [[ -z "$ADMIN_LOGIN" ]]; then
  echo "  ! no administrator user found; create one and re-run."
else
  APP_PASS_JSON=$(wp user application-password create "$ADMIN_LOGIN" "WCPorto Rehearsal" --porcelain 2>/dev/null || true)
  if [[ -n "$APP_PASS_JSON" ]]; then
    echo "  ✓ Application Password for '$ADMIN_LOGIN': $APP_PASS_JSON"
    echo "    Save this somewhere — you cannot view it again."
  else
    echo "  • An 'WCPorto Rehearsal' app password may already exist. List with:"
    echo "    wp user application-password list $ADMIN_LOGIN"
  fi
fi

SITE_URL=$(wp option get siteurl)

echo
echo "==> Verifying each ability"
for ability in \
  "wcporto/say-hello" \
  "wcporto/say-hello-to" \
  "wcporto/admin-only-greeting" \
  "wcporto/structured-greeting" \
  "wcporto/list-recent-posts" \
  "wcporto/echo-with-metadata"; do
  if wp eval "echo wp_get_ability( '$ability' ) ? 'OK' : 'MISSING';" 2>/dev/null | grep -q OK; then
    echo "  ✓ $ability"
  else
    echo "  ✗ $ability NOT REGISTERED"
  fi
done

cat <<EOF

============================================================
  Rehearsal install complete.
============================================================

Site:           $SITE_URL
Admin user:     $ADMIN_LOGIN
Subscriber:     wsub  /  test1234
Draft post ID:  $DRAFT_ID   (use in exercise 5 publish demo)

Next: run the demos. See REHEARSAL_COMMANDS.md for the
exact command list, in slide order.
EOF
