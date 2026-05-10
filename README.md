# Mastering the WordPress Abilities API — WordCamp Porto 2026

Hands-on workshop materials for **WordCamp Porto 2026**. Six self-contained WordPress plugins that walk you through the **WP 6.9+ Abilities API** end-to-end: registry, input/output schemas, permission callbacks, real data, and AI/MCP-style discovery.

## Prerequisites

- WordPress **6.9 or newer**
- PHP **8.1+**
- Comfortable writing a small WordPress plugin (you can read PHP, register hooks, and use the WP REST API).

## Quick start

1. Stand up a local WP 6.9+ site — see [`SETUP.md`](./SETUP.md).
2. Open [`exercises/01-hello-ability/README.md`](./exercises/01-hello-ability/README.md) and follow along.
3. Work through the six exercises in order. Each one introduces one concept from the slide deck.

## Exercises

Work through these in order. Each is a standalone, installable WordPress plugin in its own folder.

| # | Folder | Concept |
| --- | --- | --- |
| 01 | [`exercises/01-hello-ability`](./exercises/01-hello-ability) | Register your first category and ability |
| 02 | [`exercises/02-input-schema`](./exercises/02-input-schema) | Gate 1: validate input with JSON Schema |
| 03 | [`exercises/03-permission-gate`](./exercises/03-permission-gate) | Gate 2: declarative `permission_callback` |
| 04 | [`exercises/04-output-schema`](./exercises/04-output-schema) | Gate 3: enforce the response contract |
| 05 | [`exercises/05-real-data-ability`](./exercises/05-real-data-ability) | List and publish real WordPress posts |
| 06 | [`exercises/06-rest-and-ai-tool`](./exercises/06-rest-and-ai-tool) | Consume abilities via the built-in REST API + Node AI-agent demo |

Each exercise has a `README.md` with the goal, the task, verification commands, and a `solution/` subfolder if you get stuck.

## API reference

A condensed, verified reference of the Abilities API surface used across all exercises lives at [`docs/api-reference.md`](./docs/api-reference.md). Use it as a cheat sheet during the workshop.

## License

GPL-2.0-or-later. See [`LICENSE`](./LICENSE).

## Credits

Workshop authored by Uros Tasic ([@wputasic](https://github.com/wputasic)) for WordCamp Porto 2026.
