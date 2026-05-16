# Exercise 02 — Input Schema

## Goal

Add **Gate 1** to your ability — a JSON Schema that validates input before your engine code ever runs.

## Concepts

- `input_schema` is a JSON Schema fragment passed to `wp_register_ability()`.
- Gate 1 of the execution pipeline: invalid input → `WP_Error`, engine never executes.
- Required vs. optional parameters; types; constraints (`minLength`, `pattern`, etc.).

## The task

1. Inside `register()` of `includes/class-abilities.php`, register an ability `wcporto/say-hello-to` that takes a `name` parameter (required string, 1–50 characters) and returns `[ 'message' => "Hello, $name!" ]`.
2. Activate the plugin and verify the failure mode (Gate 1 rejecting bad input).

## Verify

**Success path:**

```bash
wp eval '
$result = wp_get_ability( "wcporto/say-hello-to" )->execute( [ "name" => "Porto" ] );
print_r( $result );
'
```

Expected: `[message] => Hello, Porto!`

**Gate 1 failure (missing required field):**

```bash
wp eval '
$result = wp_get_ability( "wcporto/say-hello-to" )->execute();
echo is_wp_error( $result ) ? $result->get_error_message() : "ENGINE RAN!";
echo PHP_EOL;
'
```

Expected: prints a validation error message — **not** "ENGINE RAN!" The engine never executed because Gate 1 failed.

## Common mistakes

- Forgetting `'required' => [ 'name' ]` at the top of the schema. Without it, an empty input is valid.
- Putting the schema fragment under the wrong key (it's `input_schema`, not `input` or `schema`).

## Solution

See [`solution/`](./solution/).
