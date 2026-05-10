# Exercise 06 — REST and AI Tool

## Goal

Discover and invoke registered abilities **over the built-in REST API**, the way an AI agent or external service would. This is the payoff exercise — proof that the registry is self-describing and machine-consumable.

## Concepts

- WordPress 6.9 **automatically** exposes registered abilities under `/wp-json/wp-abilities/v1/...`. No `register_rest_route` needed.
- All Abilities REST endpoints **require an authenticated user** (use Application Passwords for external clients).
- An ability can opt out via `'meta' => [ 'show_in_rest' => false ]`.
- A well-written `description` and complete `input_schema`/`output_schema` are what make an ability **discoverable** to AI tools.

## Built-in REST endpoints (WP 6.9+)

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/wp-json/wp-abilities/v1/abilities` | List all abilities (with schemas) |
| `GET` | `/wp-json/wp-abilities/v1/abilities/{name}` | Single ability metadata |
| `GET` `POST` `DELETE` | `/wp-json/wp-abilities/v1/abilities/{name}/run` | Execute |

## The task

1. Register category `wcporto/agent-tools`.
2. Register ability `wcporto/echo-with-metadata` — input: `{ message: string (1..200) }`; output: `{ echoed: string, length: integer, received_at: string }`. Permission: `is_user_logged_in()`.
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
# Note: forward slash in the slug must be URL-encoded as %2F
curl -s -X POST -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  http://localhost:8888/wp-json/wp-abilities/v1/abilities/wcporto%2Fecho-with-metadata/run \
  -H 'Content-Type: application/json' \
  -d '{"message":"Hello, Porto!"}'
```

Expected: `{"echoed":"Hello, Porto!","length":13,"received_at":"2026-..."}`.

**Node discovery script:**

```bash
node tools/discover.mjs http://localhost:8888 admin "xxxx xxxx xxxx xxxx xxxx xxxx"
```

Expected: lists all abilities, then invokes `wcporto/echo-with-metadata` and prints the result. This is the same pattern an AI agent or MCP client uses.

## Common mistakes

- Forgetting to URL-encode the `/` in the ability slug when hitting `/abilities/{name}/run` — use `%2F`.
- Hitting the endpoint anonymously — all Abilities REST endpoints require auth. Application Passwords are the simplest option.
- Not specifying `show_in_rest` and being surprised when an ability is missing from the list — by default it's exposed; you'd only hide it explicitly.

## Solution

See [`solution/`](./solution/) and [`tools/discover.mjs`](./tools/discover.mjs).
