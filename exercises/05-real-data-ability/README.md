# Exercise 05 — Real Data Ability

## Goal

Build two abilities that read and write real WordPress data, using **all four gates** (input, permission, engine, output) together.

## Concepts

- A read-only ability (`wcporto/list-recent-posts`) — open to any logged-in user, returns latest posts.
- A write ability (`wcporto/publish-draft`) — requires `publish_posts`, takes a draft post ID, publishes it.
- Combining all gates is the realistic shape of a production ability.

## The task

1. Register category `wcporto-content`.
2. Register ability `wcporto/list-recent-posts` — input: optional `count` (1–20, default 5); output: `{ posts: [{ id, title, link }] }`.
3. Register ability `wcporto/publish-draft` — input: required `post_id` (integer); permission: `publish_posts`; output: `{ post_id, status, link }`.

## Verify

**Read:**

```bash
wp post create --post_title="Hello Porto" --post_status=publish
wp eval '
$result = wp_get_ability( "wcporto/list-recent-posts" )->execute( [ "count" => 3 ] );
print_r( $result );
'
```

Expected: array of up to 3 published posts.

**Write:**

```bash
DRAFT_ID=$(wp post create --post_title="Draft to publish" --post_status=draft --porcelain)
wp eval "
\$result = wp_get_ability( 'wcporto/publish-draft' )->execute( [ 'post_id' => $DRAFT_ID ] );
print_r( \$result );
" --user=1
wp post get $DRAFT_ID --field=post_status
```

Expected: result shows `status => publish`; `wp post get` confirms `publish`.

## Common mistakes

- Forgetting that `permission_callback` runs **before** the engine. Don't re-check capabilities inside `execute_callback`.
- Returning `WP_Post` objects directly. Gate 3 expects plain arrays/scalars; map to `{ id, title, link }`.

## Solution

See [`solution/`](./solution/).
