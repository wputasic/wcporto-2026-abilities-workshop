# Exercise 01 — Hello Ability

## Goal

Register your first **Abilities API category** and your first **ability**, returning a fixed greeting.

## Concepts

- The two init hooks: `wp_abilities_api_categories_init` and `wp_abilities_api_init`.
- Categories must be registered **before** abilities that reference them — the Abilities API enforces this via separate hooks.
- Slug rule: lowercase + dashes only, namespaced as `<namespace>/<name>`.
- `permission_callback` is **required** by `wp_register_ability()`. For a public ability, use `'__return_true'`.

## The task

1. Open `includes/class-categories.php`. Inside `register()`, call `wp_register_ability_category()` to register a category with slug **`wcporto-workshop-actions`**, label **`Workshop Actions`**, and a one-sentence description.
2. Open `includes/class-abilities.php`. Inside `register()`, call `wp_register_ability()` to register an ability with:
   - slug: **`wcporto/say-hello`**
   - label: **`Say Hello`**
   - category: **`wcporto-workshop-actions`**
   - `permission_callback`: `'__return_true'`
   - `execute_callback`: returns `[ 'message' => 'Hello, WordCamp Porto 2026!' ]`
3. Activate the plugin in `wp-admin → Plugins`.

## Verify

Run the snippet below against your local WP. It should print the greeting and exit without errors.

```bash
wp eval '
$result = wp_get_ability( "wcporto/say-hello" )->execute();
print_r( $result );
'
```

Expected output:

```
Array
(
    [message] => Hello, WordCamp Porto 2026!
)
```

## Common mistakes

- **Underscores in the slug** (e.g., `wcporto/say_hello`) — the API rejects them. Use dashes.
- **Registering on the `init` hook** instead of `wp_abilities_api_init`. The registry isn't ready on `init`; you'll get a `_doing_it_wrong` notice.
- **Omitting `permission_callback`** — it's required, even when the ability is public. Use `'__return_true'`.

## Solution

If you get stuck, the completed code lives in [`solution/`](./solution/). Compare side-by-side with your edits.
