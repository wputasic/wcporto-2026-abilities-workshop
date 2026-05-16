# Workshop Materials — Reality-Test Corrections

**Date:** 2026-05-16
**Tested against:** WordPress 6.9.4 (Local by Flywheel, PHP 8.1+)

Originally the workshop was authored from the Abilities API specification. After standing up a real WP 6.9 site and running every exercise end-to-end, six API behaviors turned out to differ from the spec-derived docs. This file is the record of what changed and why, so it's easy to write commit messages and a clean changelog entry.

---

## Summary of corrections

| # | What we thought | What core actually does | Severity |
|---|---|---|---|
| 1 | Category slug = `vendor/name` (with slash) | Category slug = `[a-z0-9-]+` only — **no slash**. Only ability *names* allow `/`. | Blocker — silent failure cascade |
| 2 | Calling `->execute([])` on any ability is fine | Schema-less abilities reject any non-null input with `ability_missing_input_schema`. Use `->execute()`. | Blocker |
| 3 | Typed `array $input` is the canonical callback signature | Core invokes callbacks with **zero arguments** when no `input_schema` is defined. Required `array $input` → PHP fatal. | Blocker |
| 4 | Abilities are exposed in REST by default | `show_in_rest` defaults to **`false`**. Opt in via `'meta' => ['show_in_rest' => true]`. | Blocker for ex 6 |
| 5 | URL-encode `/` in `/run` URL as `%2F` | Pass the literal `/` — the route pattern captures it. `%2F` is rejected by many servers. | Blocker for ex 6 |
| 6 | POST body is the params themselves | POST body wraps params under an `input` key: `{"input": {...params...}}`. | Blocker for ex 6 |

All six are also strong on-stage talking points — "the API enforces this strictly" moments that demonstrate the design philosophy.

---

## Detailed change log

### Correction 1 — Category slugs cannot contain slashes

**Core source:** `wp-includes/abilities-api/class-wp-ability-categories-registry.php`, line 68
```php
if ( ! preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug ) ) { ... }
```

**vs. ability names** (`class-wp-abilities-registry.php` line 81):
```php
if ( ! preg_match( '/^[a-z0-9-]+\/[a-z0-9-]+$/', $name ) ) { ... }
```

Categories: dashes only. Abilities: exactly one slash.

**Files updated:**
- `exercises/01-hello-ability/{includes,solution/includes}/class-categories.php`
- `exercises/01-hello-ability/{includes,solution/includes}/class-abilities.php`
- `exercises/02-input-schema/{includes,solution/includes}/class-categories.php`
- `exercises/02-input-schema/{includes,solution/includes}/class-abilities.php`
- `exercises/03-permission-gate/{includes,solution/includes}/class-categories.php`
- `exercises/03-permission-gate/{includes,solution/includes}/class-abilities.php`
- `exercises/04-output-schema/{includes,solution/includes}/class-categories.php`
- `exercises/04-output-schema/{includes,solution/includes}/class-abilities.php`
- `exercises/05-real-data-ability/{includes,solution/includes}/class-categories.php`
- `exercises/05-real-data-ability/{includes,solution/includes}/class-abilities.php`
- `exercises/06-rest-and-ai-tool/{includes,solution/includes}/class-categories.php`
- `exercises/06-rest-and-ai-tool/{includes,solution/includes}/class-abilities.php`
- `exercises/01-hello-ability/README.md`
- `exercises/05-real-data-ability/README.md`
- `exercises/06-rest-and-ai-tool/README.md`
- `docs/api-reference.md`
- `SPEAKER_CUE_CARD.md` / `SPEAKER_CUE_CARD.txt`

**Substitutions:**
- `wcporto/workshop-actions` → `wcporto-workshop-actions`
- `wcporto/content` → `wcporto-content`
- `wcporto/agent-tools` → `wcporto-agent-tools`

**Symptom before fix:** all `wcporto/*` categories silently fail to register. Abilities that reference them also silently fail (with a `_doing_it_wrong` notice that wouldn't show up without `WP_DEBUG_LOG`). End result: 0 wcporto abilities in the registry despite all six plugins activating cleanly.

---

### Correction 2 — `->execute()` with no argument for no-schema abilities

**Core source:** `wp-includes/abilities-api/class-wp-ability.php`, `validate_input()`:
```php
if ( empty( $input_schema ) ) {
    if ( null === $input ) {
        return true;        // OK — no input, no schema, pass through
    }
    return new WP_Error( 'ability_missing_input_schema', ... );
}
```

If you pass anything (including `[]`) to a schema-less ability, core errors. Schema-bearing abilities are unaffected.

**Files updated (verify commands changed from `->execute( [] )` → `->execute()`):**
- `exercises/01-hello-ability/README.md`
- `exercises/02-input-schema/README.md`
- `exercises/03-permission-gate/README.md`
- `REHEARSAL_COMMANDS.md`

**Applies to:** exercises 1 (`wcporto/say-hello`) and 3 (`wcporto/admin-only-greeting`) — the two abilities with no `input_schema`.

---

### Correction 3 — Typed callback parameters need defaults when no schema is defined

**Core source:** `class-wp-ability.php`, `invoke_callback()`:
```php
protected function invoke_callback( callable $callback, $input = null ) {
    $args = array();
    if ( ! empty( $this->get_input_schema() ) ) {
        $args[] = $input;
    }
    return $callback( ...$args );
}
```

If no schema → callback invoked with zero args. A signature like `function (array $input): bool` fatals because PHP requires the argument.

**File updated:**
- `exercises/03-permission-gate/{includes,solution/includes}/class-abilities.php`

**Change:** both callbacks (`permission_callback` and `execute_callback`) now declare `array $input = []` instead of `array $input`.

**Note:** the only file affected was exercise 3's ability, because every other ability either takes no typed `$input` (exercise 1) or has an `input_schema` defined (exercises 2, 4, 5, 6).

---

### Correction 4 — `show_in_rest` defaults to false

**Core source:** `class-wp-ability.php`:
```php
* @type bool $show_in_rest  Optional. Whether to expose this ability in the REST API. Default false.
```

And the list/run controllers filter on it:
```php
if ( ! $ability || ! $ability->get_meta_item( 'show_in_rest' ) ) {
    // 404
}
```

Workshop docs claimed default was `true`. Reality: opt-in.

**Files updated (all 7 ability registrations now declare `'meta' => ['show_in_rest' => true]`):**
- `exercises/01-hello-ability/{includes,solution/includes}/class-abilities.php`
- `exercises/02-input-schema/{includes,solution/includes}/class-abilities.php`
- `exercises/03-permission-gate/{includes,solution/includes}/class-abilities.php`
- `exercises/04-output-schema/{includes,solution/includes}/class-abilities.php`
- `exercises/05-real-data-ability/{includes,solution/includes}/class-abilities.php` (2 ability registrations)
- `exercises/06-rest-and-ai-tool/{includes,solution/includes}/class-abilities.php`

**Doc text updated** (default flipped from "exposed" / "true" to "private" / "false"):
- `exercises/06-rest-and-ai-tool/README.md`
- `docs/api-reference.md`
- `SPEAKER_NOTES.md`
- `SLIDE_NOTES.md` / `SLIDE_NOTES.txt`
- `SPEAKER_CUE_CARD.md` / `SPEAKER_CUE_CARD.txt`

**Pedagogical reframing:** the docs originally said "you'd opt out for internal abilities." Now they say "opt in deliberately because every ability is an attack surface" — which is a stronger, more accurate framing.

---

### Correction 5 — `/run` URL uses literal `/`, not `%2F`

**Core source:** `class-wp-rest-abilities-v1-run-controller.php`:
```php
'/' . $this->rest_base . '/(?P<name>[a-zA-Z0-9\-\/]+?)/run'
```

The capture group explicitly allows `/`. Backtracking matches `wcporto/echo-with-metadata` as the name. Encoding the slash as `%2F` triggers `rest_no_route` because Apache/nginx default to rejecting `%2F` in paths (`AllowEncodedSlashes Off`).

**Files updated:**
- `REHEARSAL_COMMANDS.md`
- `exercises/06-rest-and-ai-tool/README.md`
- `exercises/06-rest-and-ai-tool/tools/discover.mjs` (removed `encodeURIComponent` on the slug)
- `docs/api-reference.md`
- `SPEAKER_NOTES.md`
- `SLIDE_NOTES.md` / `SLIDE_NOTES.txt`
- `SPEAKER_CUE_CARD.md` / `SPEAKER_CUE_CARD.txt`

---

### Correction 6 — POST body wraps params under `input`

**Core source:** `class-wp-rest-abilities-v1-run-controller.php`, `get_input_from_request()`:
```php
// For POST requests, look for 'input' in JSON body.
$json_params = $request->get_json_params();
return $json_params['input'] ?? null;
```

And the controller's run-args schema (`get_run_args()`):
```php
'input' => array(
    'description' => __( 'Input parameters for the ability execution.' ),
    'type' => array( 'integer', 'number', 'boolean', 'string', 'array', 'object', 'null' ),
    'default' => null,
),
```

So a request like `{"message":"Hello"}` is parsed as having no `input` key, which means `null` reaches the ability's `validate_input()`. For an ability that *has* an `input_schema` (like `wcporto/echo-with-metadata`), `null` fails the schema check → `ability_invalid_input` with "input is not of type object."

**Correct body:** `{"input":{"message":"Hello"}}`.

**Files updated:**
- `REHEARSAL_COMMANDS.md`
- `exercises/06-rest-and-ai-tool/README.md`
- `exercises/06-rest-and-ai-tool/tools/discover.mjs`
- `docs/api-reference.md`
- `SPEAKER_NOTES.md`
- `SLIDE_NOTES.md` / `SLIDE_NOTES.txt`
- `SPEAKER_CUE_CARD.md` / `SPEAKER_CUE_CARD.txt`

---

## New files added during this session

- `scripts/install-rehearsal.sh` — one-shot installer for Local sites (copies all 6 plugins with solutions, activates, seeds demo data, generates Application Password, verifies registrations).
- `scripts/cleanup.sh` — teardown counterpart (removes plugins, subscriber user, seeded posts, app password) without nuking the Local site.
- `LOCAL_SETUP.md` — step-by-step recipe for standing up a Local site and running the install script.
- `REHEARSAL_COMMANDS.md` — every demo command in slide order, copy-paste-ready (Site Shell).
- `SPEAKER_NOTES.md` — long-form speaker notes per exercise (~110 min talk).
- `SLIDE_NOTES.md` / `.txt` — one paragraph per slide, paste-ready for the deck (~25 slides).
- `SPEAKER_CUE_CARD.md` / `.txt` — single-page glance card (phone-friendly text version included).
- `CHANGES.md` — this file.

---

## Suggested commit structure (when pushing to GitHub)

If you want logical commits rather than one big "fix everything":

1. `fix(abilities): use dash-only slugs for categories (no slash)` — touches all `class-categories.php` + `class-abilities.php` `category` references + docs (correction 1).
2. `fix(callbacks): default array $input parameter for no-schema abilities` — ex 3 only (correction 3).
3. `fix(verify): use ->execute() with no arg for schema-less abilities` — README + REHEARSAL_COMMANDS verify commands (correction 2).
4. `feat(rest): opt all workshop abilities into show_in_rest` — all 6 ability files (correction 4).
5. `fix(docs): show_in_rest defaults to false, slash is literal, body wraps under input` — doc-only changes for corrections 4, 5, 6 + `discover.mjs`.
6. `docs: add speaker notes, slide notes, cue card, local setup guide, rehearsal commands, install/cleanup scripts` — all new files.

Or one big `fix: reality-test against WP 6.9 stable` commit referencing this file.

---

## Verification baseline (everything passing as of 2026-05-16)

On the Local rehearsal site at http://localhost:10047/:

- ✅ All 6 plugins activate
- ✅ All 7 abilities register (`wp_get_ability()` returns object for each)
- ✅ Ex 1 (`wcporto/say-hello`) — execute with no arg returns greeting
- ✅ Ex 2 (`wcporto/say-hello-to`) — execute with `name` returns personalized greeting; empty input rejected at Gate 1
- ✅ Ex 3 (`wcporto/admin-only-greeting`) — admin succeeds, subscriber rejected at Gate 2
- ✅ Ex 4 (`wcporto/structured-greeting`) — returns `{greeting, length}` validated against output_schema
- ✅ Ex 5a (`wcporto/list-recent-posts`) — returns mapped post array
- ✅ Ex 5b (`wcporto/publish-draft`) — flips draft to published, returns `{post_id, status, link}`
- ✅ Ex 6 — `GET /abilities` returns 9 abilities (3 core + 7 wcporto) with full schemas; `POST /abilities/wcporto/echo-with-metadata/run` returns `{echoed, length, received_at}`; Node `discover.mjs` succeeds end-to-end
