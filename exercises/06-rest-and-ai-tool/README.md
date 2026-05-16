# Exercise 06 — REST and AI Tool

## Goal

Discover and invoke registered abilities **over the built-in REST API**, the way an AI agent or external service would. This is the payoff exercise — proof that the registry is self-describing and machine-consumable.

## Concepts

- WordPress 6.9 ships built-in REST endpoints for abilities under `/wp-json/wp-abilities/v1/...`. No `register_rest_route` needed.
- All Abilities REST endpoints **require an authenticated user** (use Application Passwords for external clients).
- **Abilities are private by default.** `show_in_rest` defaults to `false` — an ability must explicitly opt in with `'meta' => [ 'show_in_rest' => true ]` to appear in the list or accept `/run` calls.
- A well-written `description` and complete `input_schema`/`output_schema` are what make an ability **discoverable** to AI tools.

## Built-in REST endpoints (WP 6.9+)

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/wp-json/wp-abilities/v1/abilities` | List all abilities (with schemas) |
| `GET` | `/wp-json/wp-abilities/v1/abilities/{name}` | Single ability metadata |
| `GET` `POST` `DELETE` | `/wp-json/wp-abilities/v1/abilities/{name}/run` | Execute |

## The task

1. Register category `wcporto-agent-tools`.
2. Register ability `wcporto/echo-with-metadata` — input: `{ message: string (1..200) }`; output: `{ echoed: string, length: integer, received_at: string }`. Permission: `is_user_logged_in()`. **Opt it into REST** with `'meta' => [ 'show_in_rest' => true ]`.
3. Generate an Application Password for your admin user: `wp-admin → Users → Profile → Application Passwords → "AI Agent" → Add`.
4. Run the Node discovery script: `node tools/discover.mjs http://localhost:8888 admin <app-password>`.

## Verify

**Discovery via cURL (replace credentials):**

```bash
curl -s -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  http://localhost:8888/wp-json/wp-abilities/v1/abilities | python3 -m json.tool
```

Expected: JSON with all registered abilities, each including `input_schema` and `output_schema`.

**Invocation via cURL:**

```bash
curl -s -X POST -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  http://localhost:8888/wp-json/wp-abilities/v1/abilities/wcporto/echo-with-metadata/run \
  -H 'Content-Type: application/json' \
  -d '{"input":{"message":"Hello, Porto!"}}'
```

> The slash in `wcporto/echo-with-metadata` is passed literally in the URL — the route pattern captures it as part of the ability name. **Don't URL-encode it as `%2F`** (some servers reject `%2F` in paths, and the controller wouldn't decode it back).
>
> Input parameters must be wrapped under an `input` key in the JSON body — the run endpoint accepts a single `input` field whose value is your ability's schema-validated payload.

Expected: `{"echoed":"Hello, Porto!","length":13,"received_at":"2026-..."}`.

**Node discovery script:**

```bash
node tools/discover.mjs http://localhost:8888 admin "xxxx xxxx xxxx xxxx xxxx xxxx"
```

Expected: lists all abilities, then invokes `wcporto/echo-with-metadata` and prints the result. This is the same pattern an AI agent or MCP client uses.

## Common mistakes

- URL-encoding the `/` in the ability slug as `%2F`. **Don't** — pass the literal slash. The route pattern is built to capture it.
- Sending POST params at the top level of the body. The run endpoint expects them wrapped: `{"input":{...your params...}}`, not `{...your params...}` directly.
- **Forgetting to opt the ability into REST.** `show_in_rest` defaults to `false`. An ability you didn't explicitly opt in won't appear in `GET /abilities` *and* won't accept calls on the `/run` endpoint. Add `'meta' => [ 'show_in_rest' => true ]` to expose it.
- Hitting the endpoint anonymously — all Abilities REST endpoints require auth. Application Passwords are the simplest option.

## Solution

See [`solution/`](./solution/) and [`tools/discover.mjs`](./tools/discover.mjs).
