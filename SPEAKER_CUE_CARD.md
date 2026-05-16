# Speaker Cue Card — One Page
**WordCamp Porto 2026 · Abilities API Workshop**

Glance-only. Bullets are triggers, not scripts.

---

## OPEN (5m)
- Six plugins. Zero `register_rest_route`. AI-ready WordPress.
- Four gates: **Input → Permission → Engine → Output**.
- Confirm: WP 6.9+, PHP 8.1+, WP-CLI. Point at `solution/` folders.

## EX 1 — Hello Ability (15m)
- Two hooks, ordered: categories THEN abilities.
- **Ability slug** = `vendor/name` (exactly one slash). **Category slug** = `vendor-name` (NO slash). Both lowercase + dashes, no underscores.
- `permission_callback` required → `'__return_true'` for now.
- No `input_schema` → call `->execute()` with NO arg. Passing `[]` errors as `ability_missing_input_schema`.
- No-schema callbacks: typed `array $input` needs a default → `function (array $input = []): ...`. Core invokes them with zero args.
- **Demo:** run `wp eval`. Break with underscore → show `_doing_it_wrong`. Fix.
- **Bridge:** "anyone, any input → fix input first."

## EX 2 — Input Schema (15m)
- JSON Schema = same vocab as REST `args`, OpenAPI, AI tool defs.
- `required` is its own array. Easy to forget.
- Gate 1 fail → engine NEVER runs → `WP_Error`.
- **Demo:** success + empty-input fail. Add `error_log` in engine, tail log, prove silence.
- **Bridge:** "input clean, auth next."

## EX 3 — Permission Gate (15m)
- ONE callback gates REST + AJAX + CLI + cron simultaneously.
- Signature: `function($input): bool|WP_Error`. Never `null`.
- Runs AFTER input validation — `$input` is trusted.
- **Demo:** admin success → subscriber fail. Anti-pattern: move check into engine → "how would an agent know in advance?" → revert.
- **Bridge:** "output is still the wild west."

## EX 4 — Output Schema (15m)
- Protects CONSUMERS from your future regressions.
- AI agents are unforgiving — contract is load-bearing.
- Use `additionalProperties: false` on outputs.
- Gate 3 fires only on successful return, not on `WP_Error`.
- **Demo:** broken engine (`wrong_key`) → `WP_Error`. Fix → success. Re-break (type mismatch) → caught.
- **Bridge:** "hello-world's done — wire to real data."

## EX 5 — Real Data Ability (20m)
- Two abilities: read (lenient) + write (`publish_posts`).
- NEVER return `WP_Post`. Map to `{ id, title, link }`.
- `publish_posts` ≠ `edit_posts`. Contributors can edit, not publish.
- `get_permalink()` AFTER status flip.
- **Demo:** list posts. Create draft → publish via ability → `wp post get` confirms.
- **Bridge:** "now the magic moment — REST for free."

## EX 6 — REST & AI Tool (20m) ★ CLIMAX
- Three endpoints: `GET /abilities`, `GET /abilities/{name}`, `POST .../run`.
- Auth-only. Application Passwords = simplest external auth.
- Slash in slug is LITERAL — do NOT encode as `%2F`.
- POST body wraps params: `{"input": {...}}` not `{...}`.
- `show_in_rest` defaults to `false` — opt in with `'meta' => ['show_in_rest' => true]`.
- `GET /abilities` returns full schemas → this is the AI tool catalog.
- **Demo:** Application Password → cURL `/abilities` → scroll JSON live → point at schemas. Run `node tools/discover.mjs`.
- **Punchline:** swap script for MCP server / LangChain wrapper — ability unchanged.

## CLOSE (5m)
- Recite the four gates one more time.
- "Zero `register_rest_route` calls. You got REST + discovery + auth + validation for free."
- CTA: re-implement one feature in a plugin you maintain as an ability.
- Repo URL on final slide. Q&A.

---

## EMERGENCY KIT
- **Behind schedule?** Cut anti-pattern demos in 3 & 4. NEVER cut the EX 6 discovery moment.
- **Demo broken >60s?** Drop `solution/` in, activate, move on.
- **Common Qs:** MCP wrappers exist · cache inside engine · blocks call abilities via REST · multisite = per-site registry · row-level perms = `permission_callback` sees `$input`.

## SLUGS YOU'LL TYPE LIVE
```
wcporto-workshop-actions          (cat, ex 1)
wcporto/say-hello                 (ex 1)
wcporto/say-hello-to              (ex 2)
wcporto/admin-only-greeting       (ex 3)
wcporto/structured-greeting       (ex 4)
wcporto-content                   (cat, ex 5)
wcporto/list-recent-posts         (ex 5)
wcporto/publish-draft             (ex 5)
wcporto-agent-tools               (cat, ex 6)
wcporto/echo-with-metadata        (ex 6)
```
