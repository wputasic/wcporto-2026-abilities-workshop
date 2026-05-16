# Speaker Notes — Mastering the WordPress Abilities API
**WordCamp Porto 2026 · Uros Tasic (@wputasic)**

A talk-track for delivering the six-exercise workshop. Each section has: what to say, what to show, what to emphasize, what people will get wrong, and how to bridge to the next exercise.

---

## 0. Opening (≈5 min)

### What to say
- "Today we're going to build six small plugins. By the end you'll have a working WordPress ability that an AI agent can discover and call over REST — without you writing a single `register_rest_route`."
- "WordPress 6.9 introduced the Abilities API. It's a **registry** for 'things WordPress can do' — read a post, publish a draft, send an email — described in a way that **both humans and machines** can consume."
- "Why does this matter? Because the new wave of AI-assisted WordPress isn't about bolting a chatbot into wp-admin. It's about giving agents a **safe, declarative surface** they can introspect and act on. The Abilities API is that surface."

### What to show
- Slide: the **four gates** diagram (Input → Permission → Engine → Output). Tell them: *"keep this picture in your head all day — every exercise we add or tighten one of these gates."*
- Slide: side-by-side of legacy REST callback (capability check, manual validation, ad-hoc response) vs. an ability (declarative). Land the point: *"the legacy version works. The ability version is **introspectable**."*

### Logistics to cover before exercise 1
- Confirm everyone has WP 6.9+, PHP 8.1+, and WP-CLI working. Point at `SETUP.md`.
- Tell them where the `solution/` folders are — *"if you fall behind, copy the solution and keep up; we'll come back to it."*
- Pace promise: each exercise is ~10–15 min. We do, then we discuss.

---

## 1. Exercise 01 — Hello Ability (≈15 min)

### Headline message
> "Categories first, abilities second. Two different hooks. The API enforces it."

### Walk-through talking points
1. **Two hooks, in order.** `wp_abilities_api_categories_init` runs first, `wp_abilities_api_init` after. Explain *why*: the registry needs categories to exist before an ability can claim one. This is the same pattern as taxonomies before post types.
2. **Two slug rules — and they differ.** Ability *names* use a slash: `vendor/name` (exactly one slash). Category *slugs* are dash-only: `vendor-name` (NO slash). The regexes are literally different in core. Get this wrong and the registration silently fails with a `_doing_it_wrong` notice — point at this on stage as a real-world "the API tells you exactly what it accepts" moment.
3. **`permission_callback` is required even when the ability is public.** Use `'__return_true'` for now. Foreshadow: *"in exercise 3 we'll replace this with a real check."*
4. **What `execute_callback` returns.** Just an associative array. No schema yet — that comes in exercise 4.
5. **Calling without a schema: `->execute()` with no arg.** This ability has no `input_schema`, so the call is `->execute()` with nothing. Passing `->execute([])` errors as `ability_missing_input_schema` — core's rule is "if you sent input, I need a schema to validate it against." Empty input means no argument, not empty array.
6. **Typed `$input` parameters need defaults when there's no schema.** If you write `function (array $input): bool {...}` for the permission or execute callback, core will fatal because it calls those callbacks with zero arguments when no schema is defined. Either drop the param or default it: `function (array $input = []): bool`. We'll use this pattern in exercise 3.

### Live demo
- Run the `wp eval` snippet. Show the greeting.
- Then **break it on purpose**: change the slug to `wcporto/say_hello` (underscore) and re-activate. Show the `_doing_it_wrong` notice. Re-fix.
- Optional: open `wp shell` and call `wp_get_ability( 'wcporto/say-hello' )` — show the object that comes back. *"This is the registry handing you a first-class object."*

### Audience questions you'll get
- **"How is this different from a REST endpoint?"** — *"REST is the transport. An ability is a contract. In exercise 6 you'll see WordPress auto-expose abilities over REST for free."*
- **"Can I register from inside a class without a static method?"** — Yes; show that we use a class with a `register()` method called from the hook.
- **"What if the category already exists?"** — The API is idempotent on the slug; first registration wins. Keep namespaces unique per plugin.

### Transition
> "Right now anyone can call this ability with any input. That's two problems. Let's fix the first one: input."

---

## 2. Exercise 02 — Input Schema (≈15 min)

### Headline message
> "Gate 1 — JSON Schema. Bad input never reaches your engine."

### Walk-through talking points
1. **JSON Schema is not new.** It's the same vocabulary the REST API uses (`args`), the same vocabulary OpenAPI uses, the same vocabulary OpenAI/Anthropic tool definitions use. *"You're not learning a WordPress thing — you're learning a portable thing."*
2. **`required` is its own array.** This bites people — they set `type: string` and assume that means "must be present". It doesn't. Show the schema where `name` is in `properties` and again in `required`.
3. **Constraints carry intent.** `minLength: 1` prevents empty strings. `maxLength: 50` prevents abuse and accidentally giant prompts. *"Every constraint here is a sentence of documentation an AI agent will read."*
4. **Failure semantics.** When Gate 1 fails, the engine **never runs**. The caller gets a `WP_Error`. This is the WordPress way — no exceptions, no half-completed work.

### Live demo
- Success path with `[ "name" => "Porto" ]`.
- Failure path with `[]` — show the `WP_Error` message.
- **The "engine never ran" moment.** Add a `error_log( 'ENGINE!' );` line inside `execute_callback`, run the failing case, tail the log — silence. Land the point: *"this isn't a try/catch wrapped around your function. The engine literally didn't execute."*

### Common mistakes to call out
- Forgetting `required`.
- Putting the schema under `input` or `schema` (wrong keys) — must be `input_schema`.
- Using `pattern` regex without anchoring (`^...$`).

### Transition
> "Input is clean. But anyone who can call WordPress can call this ability. Let's add the auth gate."

---

## 3. Exercise 03 — Permission Gate (≈15 min)

### Headline message
> "Gate 2 — one declarative permission callback. No more `current_user_can()` scattered across REST handlers, AJAX endpoints, and block editor controllers."

### Walk-through talking points
1. **Where capability checks used to live.** Sketch on a slide or whiteboard: same logical permission ("only editors can publish") repeated in `register_rest_route`'s `permission_callback`, inside an `admin-ajax` handler, inside a Gutenberg `register_block_type` callback. *"Three places, three chances to drift."*
2. **One ability, one permission_callback.** This is the architectural payoff. Even when the ability is later invoked via REST, via MCP, via WP-CLI, via cron — the same callback gates it.
3. **Signature: `function(array $input): bool|WP_Error`.** Return `true`/`false`, or a `WP_Error` if you want a specific message. **Don't return `null`.**
4. **Order matters.** Input validation runs *before* permission — so by the time `permission_callback` runs, the `$input` array has already passed Gate 1. You can trust it.

### Live demo
- Run as `--user=1` (admin). Success.
- Create the subscriber. Run as `--user=wsub`. Show the permission error.
- **Anti-pattern demo:** comment out the permission check, move `current_user_can( 'manage_options' )` *inside* `execute_callback`. Show that it still "works." Then ask: *"how would an AI agent know in advance whether it's allowed to call this?"* — it can't. The capability is invisible until execution. **That's the bug.** Move it back.

### Audience questions you'll get
- **"Can I do row-level checks (e.g. 'can this user edit this specific post')?"** — Yes; `$input` is passed to the callback. Show: `function( $input ) { return current_user_can( 'edit_post', $input['post_id'] ); }`.
- **"Application Passwords?"** — Coming in exercise 6.

### Transition
> "Input is gated, permissions are gated, the engine runs safely. But what comes *out*? Right now — anything we want. That's a problem for anyone consuming this."

---

## 4. Exercise 04 — Output Schema (≈15 min)

### Headline message
> "Gate 3 — the response contract. If your engine returns the wrong shape, the caller gets a `WP_Error`, not garbage."

### Walk-through talking points
1. **Why this matters more than input schema.** Input schema protects *you* from bad callers. Output schema protects *them* from a regression *you* introduce six months from now. *"Your future self renames a key and ships it on a Friday — Gate 3 catches it."*
2. **AI agents are unforgiving consumers.** A human user sees a malformed response and sighs. An AI agent sees a malformed response and either crashes the chain or hallucinates around it. The contract is load-bearing.
3. **Schema discipline.** `additionalProperties: false` is a sharp tool — use it on outputs. It says: *"this is exhaustive."*
4. **Where Gate 3 fires.** *After* the engine. So if your engine throws/returns `WP_Error`, Gate 3 is short-circuited. Only successful returns are validated.

### Live demo (this is the most fun one)
- Start with the broken engine (returns `wrong_key`, forgets `length`). Run it. **Show the `WP_Error`.** Pause and let the room read it.
- Fix the engine to return `{ greeting, length }`. Re-run. Show success.
- Temporarily break it again — change `length` to a string. Show Gate 3 catching the type mismatch.
- Land the point: *"this is unit-test-grade safety, declared once, enforced at every call site."*

### Audience questions you'll get
- **"What if I need different output shapes depending on input?"** — Use JSON Schema `oneOf` / `anyOf`. Show a one-liner.
- **"Performance overhead?"** — Negligible for typical responses. Don't pre-optimize.

### Transition
> "We've talked entirely about a 'hello' ability. Let's wire one to real WordPress data."

---

## 5. Exercise 05 — Real Data Ability (≈20 min)

### Headline message
> "All four gates together, twice — once for read, once for write. This is the shape of a real production ability."

### Walk-through talking points
1. **Two abilities side by side.** `list-recent-posts` is read-only and lenient. `publish-draft` is a write and requires `publish_posts`. *"Same API, two completely different risk profiles."*
2. **Shape the response.** `WP_Post` objects don't belong in ability output. Map to `{ id, title, link }`. Reasons: (a) Gate 3 won't accept an object that doesn't match the schema, (b) JSON serialization of `WP_Post` exposes way too much, (c) AI agents only need the fields you name.
3. **Capability hygiene.** `publish_posts` is the right check — *not* `edit_posts`. Show the difference quickly: a contributor can edit but not publish.
4. **`get_permalink()` after the status change.** Order matters — fetch the link *after* you publish, so it's the published URL.

### Live demo
- Read flow: create a post, list it, show the response.
- Write flow: create a draft, capture its ID, publish it via the ability, verify status with `wp post get`.
- **Failure demo:** call `publish-draft` with a non-existent `post_id`. Show how you handle it — `WP_Error` from inside `execute_callback`, not an exception.

### Common mistakes to call out
- Calling `current_user_can()` inside `execute_callback`. Don't — `permission_callback` already ran.
- Returning a `WP_Post` directly.
- Forgetting that `count` is optional but bounded (1–20). Default in schema, not in PHP.

### Transition (the big one)
> "Here's the magic moment. We've registered these abilities. We haven't written a line of REST routing. Watch what WordPress gives us for free."

---

## 6. Exercise 06 — REST and AI Tool (≈20 min)

### Headline message
> "Every registered ability is **automatically** a REST endpoint. The registry is **self-describing**. This is the AI-tool surface."

### Walk-through talking points
1. **The three endpoints.** `GET /abilities` lists everything (with full schemas). `GET /abilities/{name}` is one ability. `POST /abilities/{name}/run` executes. *"That's it. Three endpoints, all your abilities."*
2. **Authentication.** All Abilities REST endpoints require an authenticated user. The simplest way for external clients: **Application Passwords**. Walk through generating one in `wp-admin → Users → Profile`. Stress: *"copy it once, store it like a secret, you can't see it again."*
3. **The slash in the slug is literal.** `wcporto/echo-with-metadata` goes into the URL as-is — `/abilities/wcporto/echo-with-metadata/run`. Do **not** URL-encode it as `%2F`; the route is built to capture the slash directly, and some servers reject `%2F` outright.
4. **Discovery is the whole point.** When you hit `GET /abilities`, you don't just get names — you get the full input and output schemas. *"This is exactly what an MCP server or a Claude/OpenAI tool catalog needs. The description, the schema, the permission posture — all there."*
5. **`show_in_rest` is opt-in.** Default is `false`. To expose an ability over REST you must pass `'meta' => [ 'show_in_rest' => true ]`. This is secure-by-default: nothing leaks to external clients unless you say so. Make the design point — every ability you ship is an attack surface decision.

6. **POST body wraps params under `input`.** The run endpoint signature is `{"input": {...your params...}}`, not the params themselves at the top level. A common 30-second debug: "I'm sending the right JSON but getting `invalid input`." It's almost always the missing `input` wrapper.

### Live demo (the climax)
- Open a terminal, hit `GET /abilities` with cURL + Application Password. Pipe through `python3 -m json.tool`. **Scroll through the JSON in front of the room.** Point at `input_schema`, `output_schema`, `description`. Say: *"this is the contract. An AI agent reads this and now knows how to call your code safely."*
- Run `node tools/discover.mjs ...`. Show it listing abilities, then invoking `echo-with-metadata`, then printing the response.
- **Bridge to the future:** *"swap that Node script for an MCP server and you've got an agent-ready WordPress. Swap it for a LangChain tool wrapper and you've got the same thing for a different stack. The ability didn't change."*

### Common mistakes to call out
- Anonymous requests — Abilities REST is auth-only. No, you can't make it public.
- Wrong content-type. `application/json` and a JSON body, not form-encoded.
- Passing the slug unencoded.

---

## 7. Closing (≈5 min)

### What to leave them with
- The four gates: **Input, Permission, Engine, Output.** Recite once more. *"Every ability you ever write goes through these four. Two of them are declarative — let the schemas do the work."*
- *"You wrote zero `register_rest_route` calls. You got REST, AI-tool discovery, capability gating, schema validation, and a self-describing registry — all from one `wp_register_ability()`."*
- The bigger picture: WordPress is becoming the **agent runtime** for the open web. The Abilities API is how plugins participate. Yours can too.

### Call to action
- "Pick one thing in a plugin you already maintain — a CSV export, a 'send test email' button, a cache flush — and re-implement it as an ability this month. You'll find half your code disappears."
- Point at the [`docs/api-reference.md`](./docs/api-reference.md) cheat sheet.
- Repo URL on the final slide. Stickers.

### Q&A buffer (~10 min)
Likely questions:
- **MCP?** "Yes, there are early MCP server wrappers around the Abilities REST surface. Watch the WordPress core repo."
- **Caching?** "Cache inside the engine, not around the ability. Respect the gates."
- **Block editor integration?** "Abilities are *not* a Gutenberg thing. They're orthogonal. A block can *call* an ability via REST."
- **Multisite?** "Per-site registry. Network-wide abilities aren't first-class yet."

---

## Speaker housekeeping

- **Total runtime:** 5 + (15×3) + 15 + 20 + 20 + 5 = ~110 min plus Q&A. A standard 2-hour slot fits comfortably; a 90-min slot means dropping the failure-mode demos in 2 and 4.
- **If you're behind schedule:** the safe cuts are the anti-pattern demos in 3 and 4. Never cut the discovery moment in exercise 6 — that's the payoff.
- **If a demo breaks:** copy the matching `solution/` folder into `wp-content/plugins/`, activate, move on. Don't debug live past 60 seconds.
- **Energy curve:** exercises 1–2 are mechanical (input ramp). Exercise 3 is the architectural "aha." Exercise 4 is the most visually satisfying. Exercise 6 is the climax — pace yourself so you have full energy for it.
