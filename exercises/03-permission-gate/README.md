# Exercise 03 — Permission Gate

## Goal

Tighten **Gate 2** — replace the lenient `'__return_true'` with a real `permission_callback` that enforces capability checks declaratively, in one place.

## Concepts

- `permission_callback` is `function(array $input): bool|WP_Error`. Return `false` or a `WP_Error` to reject; `true` to proceed.
- Compare with **legacy** WP: capability checks scattered inside REST callbacks, AJAX handlers, and Gutenberg endpoints.
- Permission rejection happens **after** input validation but **before** the engine runs.

## The task

1. Register an ability `wcporto/admin-only-greeting` whose `permission_callback` returns `current_user_can( 'manage_options' )`.
2. The execute callback returns `[ 'message' => 'Welcome, administrator!', 'user_id' => get_current_user_id() ]`.
3. Verify both the success path (as admin) and the rejection (as a non-admin user).

## Verify

**As admin (success):**

```bash
wp eval '
$result = wp_get_ability( "wcporto/admin-only-greeting" )->execute( [] );
print_r( $result );
' --user=1
```

Expected: `[message] => Welcome, administrator!` plus `[user_id]`.

**As subscriber (Gate 2 rejection):**

```bash
# Create a subscriber if you do not have one
wp user create wsub wsub@example.com --role=subscriber --user_pass=test1234

wp eval '
$result = wp_get_ability( "wcporto/admin-only-greeting" )->execute( [] );
echo is_wp_error( $result ) ? $result->get_error_message() : "ENGINE RAN!";
echo PHP_EOL;
' --user=wsub
```

Expected: prints a permission error message — **not** "ENGINE RAN!"

## Common mistakes

- Calling `current_user_can()` **inside** the execute callback. Don't — that's the legacy pattern. Put it in `permission_callback`.
- Returning `null` instead of `bool`. The callback must return `bool` or `WP_Error`.

## Solution

See [`solution/`](./solution/).
