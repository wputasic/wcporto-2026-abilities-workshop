# Exercise 04 — Output Schema

## Goal

Add **Gate 3** to your ability — an `output_schema` that enforces the response contract before data leaves WordPress.

## Concepts

- `output_schema` is a JSON Schema fragment describing the **return value** of `execute_callback`.
- AI agents and integrators **rely** on a stable response shape. Without Gate 3, your ability's contract is implicit and fragile.
- Gate 3 fires **after** the engine runs. A schema violation produces `WP_Error` instead of malformed data being returned.

## The task

The starter intentionally registers an ability whose `execute_callback` **returns the wrong shape** — it puts the greeting under a `wrong_key` and forgets to compute the length.

1. Add an `output_schema` declaring the response shape (`{ greeting: string, length: integer }`, both required, no additional properties).
2. Fix the engine to return data conforming to the schema.
3. Verify Gate 3 catches the broken version (revert your engine change temporarily and watch the error).

## Verify

**Success path:**

```bash
wp eval '
$result = wp_get_ability( "wcporto/structured-greeting" )->execute( [ "name" => "Porto" ] );
print_r( $result );
'
```

Expected:

```
Array
(
    [greeting] => Hello, Porto!
    [length] => 13
)
```

**Gate 3 failure (temporarily break the engine):** change the engine to `return [ 'wrong' => 'shape' ];` and re-run. Expected: `WP_Error` with a schema-violation message.

## Common mistakes

- Defining `output_schema` but forgetting to return all `required` properties from the engine.
- Returning a non-array (e.g., a plain string) from the engine — Gate 3 expects whatever the schema says, typically `type: object`.

## Solution

See [`solution/`](./solution/).
