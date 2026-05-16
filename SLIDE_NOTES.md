# Slide Notes — Paste Directly Into Deck
**WordCamp Porto 2026 · Abilities API Workshop**

One block per slide. Paste into the speaker-notes field of your deck app. No timings, no stage directions — just what you'd say.

---

### SLIDE: Title — "Mastering the WordPress Abilities API"
Hi everyone, I'm Uros. For the next couple of hours we're going to build six small WordPress plugins together. By the end you'll have a working ability that an AI agent can discover and call over REST — and you will not have written a single line of REST routing code. That's the promise. Let's get into it.

---

### SLIDE: Why this matters
WordPress 6.9 introduced something called the Abilities API. It's a registry — a structured catalog — of "things WordPress can do." Read a post. Publish a draft. Send an email. The point is not just that you can register these actions. The point is that they're described in a way that **both humans and machines** can consume. That second word is what's new. We are building the surface that AI agents will use to act on WordPress sites safely.

---

### SLIDE: The Four Gates
Keep this picture in your head for the whole workshop. Every ability has four gates. Input — validated against a JSON schema. Permission — checked against a single callback. Engine — your actual code. Output — validated against another JSON schema on the way out. Every exercise today adds or tightens one of these gates. By exercise five, all four are in play.

---

### SLIDE: Legacy WordPress vs. Abilities
On the left: how we used to do this. A `register_rest_route` call with a permission callback, manual input validation inside the handler, an ad-hoc response shape, no introspection. On the right: an ability. Declarative. Same gates, named explicitly. Same response, contract-enforced. Both versions work. Only one of them is something an AI agent can read and use without a human in the loop.

---

### SLIDE: Exercise 1 — Hello Ability
First exercise. We're going to register a category and an ability that returns a fixed greeting. Two things to internalize. First: categories register on one hook, abilities on another. The API enforces the order. Second: slugs are lowercase with dashes, namespaced like `vendor/name`. Underscores will be rejected. Think of the slug as a public API name — it will end up in REST URLs and AI tool catalogs. Let's write it.

---

### SLIDE: Two slug rules — abilities and categories differ
This trips people up. Ability *names* use a slash: `vendor/name` — exactly one slash, lowercase, dashes. Category *slugs* are dash-only: `vendor-name`, no slash. Different regex, different rule. So the category we're registering is `wcporto-workshop-actions` (no slash), but the ability that references it is `wcporto/say-hello` (one slash). Get this wrong and the registration silently fails with a `_doing_it_wrong` notice.

---

### SLIDE: `permission_callback` is mandatory
Even when your ability is fully public, you have to pass a permission callback. For now we'll use `'__return_true'`. We'll replace it with something real in exercise three. The reason it's mandatory: the registry refuses to let you accidentally ship an ability with no auth posture at all. Forgetting auth is the most common WordPress security mistake. The API closes that door.

---

### SLIDE: Calling abilities — `execute()` with no argument
This ability has no `input_schema`, so call it with no arguments: `->execute()`. If you pass `->execute([])` to a schema-less ability, you get `ability_missing_input_schema` — core's rule is "if you sent input, I need a schema to validate it against." Rule of thumb: empty input means no argument, not empty array. We'll define a schema in exercise two and the calling convention switches.

---

### SLIDE: Exercise 2 — Input Schema
Now we add Gate 1. We're going to take a `name` parameter and validate it before our engine ever runs. The schema language is JSON Schema — the same vocabulary the REST API uses for `args`, the same OpenAPI uses, the same OpenAI and Anthropic use for tool definitions. You are not learning a WordPress thing here. You are learning a portable thing.

---

### SLIDE: `required` is its own array
This is the most common mistake I see. People declare `name` as a string and assume that means it must be present. It doesn't. JSON Schema treats every property as optional unless you list it in the `required` array. Set `type` for shape, list in `required` for presence. Two separate concerns.

---

### SLIDE: Gate 1 failure semantics
When Gate 1 rejects input, your engine **never runs**. The caller gets a `WP_Error` back. We're going to prove that in a second by sticking an `error_log` line inside the engine, sending bad input, and watching nothing show up in the log. This isn't a try/catch wrapped around your code. The engine literally does not execute.

---

### SLIDE: Exercise 3 — Permission Gate
Now Gate 2. We replace `'__return_true'` with a real capability check. The signature is `function($input): bool|WP_Error`. Return false to reject, true to proceed, or a `WP_Error` if you want a specific message. One thing it must not return: `null`.

---

### SLIDE: One callback, every entry point
This is the architectural payoff. The same `permission_callback` gates this ability whether it's invoked via REST, via WP-CLI, via cron, via an MCP server, via a future transport we haven't invented yet. Compare with the legacy world: capability checks scattered across REST handlers, AJAX endpoints, and block editor controllers. Three places, three chances to drift. Here: one place.

---

### SLIDE: Why not check inside the engine?
You can. It will work. Watch what happens when we move the check into `execute_callback`. The ability still rejects unauthorized users. But ask yourself: how would an AI agent know *in advance* whether it's allowed to call this? It can't. The capability requirement is invisible from the outside. That's the bug. The permission callback is what makes auth posture **discoverable**.

---

### SLIDE: Exercise 4 — Output Schema
Gate 3. The response contract. We declare the shape of what comes back, and if our engine returns something different, the caller gets a `WP_Error` instead of malformed data. The input schema protects you from bad callers. The output schema protects them from a regression you ship six months from now when you accidentally rename a key on a Friday afternoon.

---

### SLIDE: AI agents are unforgiving consumers
A human user sees a broken response and sighs and moves on. An AI agent sees a broken response and either crashes the chain or — worse — hallucinates around it. Output contracts are not nice-to-have when you're building for agents. They're load-bearing. Use `additionalProperties: false` on outputs. It says: this list is exhaustive, this is everything.

---

### SLIDE: Demo — break it deliberately
The starter for this exercise ships with a broken engine on purpose. It returns the wrong key and forgets to compute the length. Run it — you get a `WP_Error`. Fix the engine — you get the right response. Re-break it by returning a string where an integer is expected — caught again. This is unit-test-grade safety, declared once, enforced at every call site.

---

### SLIDE: Exercise 5 — Real Data
All four gates together, twice. One read ability: `list-recent-posts`. One write ability: `publish-draft`. Same API, two completely different risk profiles. The read one is open to any logged-in user. The write one requires `publish_posts` — not `edit_posts`. Contributors can edit drafts but cannot publish. The capability you choose matters.

---

### SLIDE: Never return `WP_Post` directly
Map to plain arrays. `{ id, title, link }`. Three reasons. One: Gate 3 won't validate an object that doesn't match your schema. Two: serializing a full `WP_Post` exposes way more than you intended — author metadata, raw content, post status of related items. Three: AI agents only need the fields you name. Shape your output deliberately.

---

### SLIDE: Exercise 6 — REST and AI Tool
This is the payoff. We registered abilities. We did not write a single `register_rest_route`. Watch what WordPress gives us for free. Three endpoints. `GET /abilities` lists everything, including full schemas. `GET /abilities/{name}` is one ability. `POST /abilities/{name}/run` executes. That's the entire surface.

---

### SLIDE: Authentication
All Abilities REST endpoints require an authenticated user. There is no anonymous access — by design. For external clients the simplest path is Application Passwords. Generate one under Users → Profile. Copy it once. Store it like a secret. You cannot retrieve it again.

---

### SLIDE: The slash stays as a slash
The ability slug is `wcporto/echo-with-metadata`. In the URL the slash stays literal — you do not URL-encode it. The route was written specifically to capture a multi-segment name, so the call shape is `/abilities/wcporto/echo-with-metadata/run`. If you encode it as `%2F`, some servers reject the request outright.

---

### SLIDE: REST exposure is opt-in
One more thing to get the discovery to work. `show_in_rest` defaults to `false`. Every ability you've registered so far is invisible to REST until you explicitly say `'meta' => [ 'show_in_rest' => true ]`. This is the right default — your abilities are an attack surface and the API makes you opt in deliberately. The lesson: in production, only expose what an agent actually needs to call.

---

### SLIDE: POST body wraps under `input`
One last contract detail. POST params do not go at the top level of the body. They go under an `input` key: `{"input": {"message": "Hello"}}`. Same for GET — params arrive as a single `input` query field. This keeps the run endpoint generic across every ability.

---

### SLIDE: Discovery is the whole point
When you hit `GET /abilities`, you don't just get a list of names. You get the full input schema, the full output schema, the description, the auth posture, everything. This is exactly the shape an MCP server consumes. The shape OpenAI and Anthropic tool catalogs consume. The shape LangChain tool wrappers consume. The Abilities API is, structurally, an AI tool catalog. WordPress 6.9 made every plugin a potential tool surface.

---

### SLIDE: Demo — live discovery
We're going to hit the endpoint with cURL right now, pipe through `python3 -m json.tool`, and scroll through the JSON together. Look at the input schemas, the output schemas, the descriptions. Then we run a small Node script that does the same thing programmatically and invokes one of the abilities. This is the exact pattern an agent uses.

---

### SLIDE: Closing — the four gates again
Input. Permission. Engine. Output. Every ability you write from today goes through these four. Two of them — input and output — are declarative. Let the schemas do the work. You wrote zero `register_rest_route` calls today and you got REST endpoints, AI-tool discovery, capability gating, schema validation, and a self-describing registry. All from one function call.

---

### SLIDE: Call to action
Here's your homework. Pick one feature in a plugin you already maintain. A CSV export. A "send test email" button. A cache flush action. Re-implement it as an ability this month. You will find that half your code disappears, and the part that remains is the part you actually wanted to write. Repo URL is on this slide. Cheat sheet is in `docs/api-reference.md`. Find me afterward.

---

### SLIDE: Q&A
Open floor. A few I'll pre-empt: yes, there are early MCP wrappers around the Abilities REST surface — watch the core repo. Cache inside the engine, not around the ability. Blocks can call abilities via REST like any other client. Multisite is per-site registry today; network-wide isn't first-class yet. Row-level permission checks work fine — your `permission_callback` receives `$input`.
