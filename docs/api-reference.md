# WordPress Abilities API — verified surface (workshop reference)

> Verified 2026-05-10 against:
> - https://developer.wordpress.org/reference/functions/wp_register_ability/ — official function reference
> - https://developer.wordpress.org/apis/abilities-api/ — API handbook
> - https://make.wordpress.org/core/2025/11/10/abilities-api-in-wordpress-6-9/ — Make/Core announcement (Nov 2025)
> - https://developer.wordpress.org/news/2025/11/introducing-the-wordpress-abilities-api/ — Developer Blog intro
> - https://github.com/WordPress/abilities-api — canonical repo
>
> WordPress version: 6.9+.

## Action hooks (in firing order)

1. **`wp_abilities_api_categories_init`** — register categories here. Categories must exist before abilities reference them.
2. **`wp_abilities_api_init`** — register abilities here.

> Do **not** use the standard `init` hook. The Abilities API initializes on its own sequence; registering elsewhere triggers a `_doing_it_wrong` notice.

## Registration functions

### `wp_register_ability_category( string $slug, array $args ): void`

- `$slug` — namespaced lowercase slug (e.g., `wcporto/workshop-actions`).
- `$args`:
  - `label` (string, required) — human-readable name.
  - `description` (string, required) — one-sentence description.

### `wp_register_ability( string $name, array $args ): WP_Ability|null`

- `$name` — `<namespace>/<ability-name>`. Lowercase alphanumeric, dashes, and forward slashes only.
- `$args`:

| Key | Type | Required | Notes |
| --- | --- | --- | --- |
| `label` | string | yes | Human-readable name |
| `description` | string | yes | One-sentence description |
| `category` | string | yes | Slug of a category registered on the categories hook |
| `execute_callback` | callable | yes | `function( $input ): mixed\|WP_Error` |
| `permission_callback` | callable | **yes** | `function( $input ): bool\|WP_Error` |
| `input_schema` | array | no | JSON Schema fragment |
| `output_schema` | array | no | JSON Schema fragment |
| `meta` | array | no | Optional metadata: `annotations`, `show_in_rest`, `ability_class` |

Returns a `WP_Ability` instance on success, `null` on failure.

> **`permission_callback` is required.** For abilities meant to be public (no auth check), use `'permission_callback' => '__return_true'`.

## Retrieving and executing abilities programmatically

```php
$ability = wp_get_ability( 'my-plugin/my-ability' );
$result  = $ability->execute( $input ); // returns mixed or WP_Error
```

If the ability is unknown, `wp_get_ability()` returns `null`.

## Slug rules

- Lowercase alphanumeric + dashes only.
- **No** underscores, **no** uppercase, **no** spaces.
- Namespace and name separated by a single forward slash: `<namespace>/<ability-name>`.

## Execution pipeline

```
Request → Gate 1: input_schema → Gate 2: permission_callback → Engine: execute_callback → Gate 3: output_schema → Response
```

Any gate failure short-circuits with `WP_Error`. The engine never runs if Gates 1 or 2 fail.

## Built-in REST API

Abilities are **automatically** exposed over REST under namespace `wp-abilities/v1`:

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/wp-json/wp-abilities/v1/abilities` | List all abilities (with schemas) |
| `GET` | `/wp-json/wp-abilities/v1/abilities/{name}` | Read a single ability's metadata |
| `GET` `POST` `DELETE` | `/wp-json/wp-abilities/v1/abilities/{name}/run` | Execute an ability |

- **Authentication**: all endpoints require an authenticated user.
- The `{name}` path segment uses the namespaced slug (URL-encode the `/`, e.g., `my-plugin%2Fmy-ability`).
- An ability can opt out of REST exposure by passing `'meta' => [ 'show_in_rest' => false ]` to `wp_register_ability()`.

## Schema dialect

JSON Schema (Draft-04 fragment style is widely used in the existing examples; both shapes — object-with-properties and plain `{ type: ... }` — are accepted).

## Quick reference — minimal valid ability

```php
add_action( 'wp_abilities_api_categories_init', function () {
    wp_register_ability_category( 'demo/actions', [
        'label'       => 'Demo Actions',
        'description' => 'Demo category.',
    ] );
} );

add_action( 'wp_abilities_api_init', function () {
    wp_register_ability( 'demo/hello', [
        'label'               => 'Say Hello',
        'description'         => 'Returns a greeting.',
        'category'            => 'demo/actions',
        'permission_callback' => '__return_true',
        'execute_callback'    => function () {
            return [ 'message' => 'Hello!' ];
        },
    ] );
} );
```
