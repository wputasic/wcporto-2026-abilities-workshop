# Rehearsal Commands — In Slide Order

Every command you'll run live during the workshop, grouped by exercise. Run them in **Local → Open Site Shell**. Assumes you've already run `scripts/install-rehearsal.sh` (see [`LOCAL_SETUP.md`](./LOCAL_SETUP.md)).

Before you start, set these for the exercise 6 cURL demos:

```bash
export SITE_URL="$(wp option get siteurl)"
export ADMIN_USER="admin"                                  # your admin login
export APP_PASS="xxxx xxxx xxxx xxxx xxxx xxxx"            # paste from install output
```

---

## Exercise 1 — Hello Ability

### Success
```bash
wp eval '
$result = wp_get_ability( "wcporto/say-hello" )->execute();
print_r( $result );
'
```
Expected: `[message] => Hello, WordCamp Porto 2026!`

### "Break it on purpose" beat (optional)
Open `wp-content/plugins/wcporto-01-hello-ability/includes/class-abilities.php`, change the slug to `wcporto/say_hello` (underscore), reload. Run:
```bash
wp plugin deactivate wcporto-01-hello-ability && wp plugin activate wcporto-01-hello-ability
```
You should see a `_doing_it_wrong` notice in `wp-content/debug.log` (or in the shell if `WP_DEBUG_DISPLAY` is on). Revert the file.

---

## Exercise 2 — Input Schema

### Success
```bash
wp eval '
$result = wp_get_ability( "wcporto/say-hello-to" )->execute( [ "name" => "Porto" ] );
print_r( $result );
'
```
Expected: `[message] => Hello, Porto!`

### Gate 1 failure (missing required field)
```bash
wp eval '
$result = wp_get_ability( "wcporto/say-hello-to" )->execute();
echo is_wp_error( $result ) ? $result->get_error_message() : "ENGINE RAN!";
echo PHP_EOL;
'
```
Expected: a validation error message — **not** "ENGINE RAN!"

### Engine-never-ran proof
```bash
tail -f wp-content/debug.log &              # in one shell, or just keep it open
wp eval '
$result = wp_get_ability( "wcporto/say-hello-to" )->execute();   // missing name
'
```
Nothing appears in the log because the engine never executed.

---

## Exercise 3 — Permission Gate

### As admin (success)
```bash
wp eval '
$result = wp_get_ability( "wcporto/admin-only-greeting" )->execute();
print_r( $result );
' --user=1
```
Expected: `[message] => Welcome, administrator!` plus `[user_id]`.

### As subscriber (rejection)
```bash
wp eval '
$result = wp_get_ability( "wcporto/admin-only-greeting" )->execute();
echo is_wp_error( $result ) ? $result->get_error_message() : "ENGINE RAN!";
echo PHP_EOL;
' --user=wsub
```
Expected: a permission error message — **not** "ENGINE RAN!"

---

## Exercise 4 — Output Schema

### Success
```bash
wp eval '
$result = wp_get_ability( "wcporto/structured-greeting" )->execute( [ "name" => "Porto" ] );
print_r( $result );
'
```
Expected:
```
[greeting] => Hello, Porto!
[length]   => 13
```

### Gate 3 failure demo
Open `wp-content/plugins/wcporto-04-output-schema/includes/class-abilities.php` and temporarily change the engine `return` to `return [ 'wrong' => 'shape' ];`. Re-run the success command — you should see a `WP_Error` with a schema-violation message. Revert.

---

## Exercise 5 — Real Data Ability

### Read (list recent posts)
```bash
wp eval '
$result = wp_get_ability( "wcporto/list-recent-posts" )->execute( [ "count" => 3 ] );
print_r( $result );
'
```
Expected: an array with the seeded "Hello Porto" post and any others.

### Write (publish the seeded draft)
The install script printed your draft post's ID. Substitute it below:

```bash
DRAFT_ID=<id-from-install-output>

wp eval "
\$result = wp_get_ability( 'wcporto/publish-draft' )->execute( [ 'post_id' => $DRAFT_ID ] );
print_r( \$result );
" --user=1

wp post get $DRAFT_ID --field=post_status
```
Expected: result shows `status => publish`; the second command prints `publish`.

> Forgot the ID? Grab it with `wp post list --post_status=draft --field=ID`.

---

## Exercise 6 — REST and AI Tool

### Discover all abilities (cURL)
```bash
curl -s -u "$ADMIN_USER:$APP_PASS" "$SITE_URL/wp-json/wp-abilities/v1/abilities" | python3 -m json.tool
```
Expected: JSON with every registered ability, each carrying its `input_schema` and `output_schema`. Scroll through it on screen — this is your AI-tool-catalog moment.

### Invoke `echo-with-metadata` over REST
```bash
curl -s -X POST -u "$ADMIN_USER:$APP_PASS" \
  "$SITE_URL/wp-json/wp-abilities/v1/abilities/wcporto/echo-with-metadata/run" \
  -H 'Content-Type: application/json' \
  -d '{"input":{"message":"Hello, Porto!"}}'
```
Expected: `{"echoed":"Hello, Porto!","length":13,"received_at":"2026-..."}`

### Node discovery script (the AI-agent pattern)
```bash
node "/Users/utasic/Desktop/WordCamp Porto 2026/wcporto-2026-abilities-workshop/exercises/06-rest-and-ai-tool/tools/discover.mjs" \
     "$SITE_URL" "$ADMIN_USER" "$APP_PASS"
```
Expected: lists all abilities, then invokes `wcporto/echo-with-metadata` and prints the result.

---

## Reset between rehearsals

```bash
bash "/Users/utasic/Desktop/WordCamp Porto 2026/wcporto-2026-abilities-workshop/scripts/install-rehearsal.sh" \
     "/Users/utasic/Desktop/WordCamp Porto 2026/wcporto-2026-abilities-workshop"
```
Idempotent — wipes prior plugin folders, re-copies fresh, re-activates, reseeds.

---

## Quick reference — slugs

| Exercise | Slug |
| --- | --- |
| 1 | `wcporto/say-hello` |
| 2 | `wcporto/say-hello-to` |
| 3 | `wcporto/admin-only-greeting` |
| 4 | `wcporto/structured-greeting` |
| 5a | `wcporto/list-recent-posts` |
| 5b | `wcporto/publish-draft` |
| 6 | `wcporto/echo-with-metadata` |
