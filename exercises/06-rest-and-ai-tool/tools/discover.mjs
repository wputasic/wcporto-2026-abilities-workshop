#!/usr/bin/env node
// Demonstrates AI-agent-style discovery and invocation of WordPress abilities
// over the built-in /wp-abilities/v1 REST API.
//
// Usage:
//   node discover.mjs <wp-base-url> <username> <application-password>
//
// Example:
//   node discover.mjs http://localhost:8888 admin "xxxx xxxx xxxx xxxx xxxx xxxx"
//
// Generate the application password under wp-admin → Users → Profile →
// Application Passwords. Paste it (with spaces) as the third argument.

const [, , baseUrlArg, username, appPassword] = process.argv;

if (!baseUrlArg || !username || !appPassword) {
	console.error('Usage: node discover.mjs <wp-base-url> <username> <application-password>');
	console.error('Example: node discover.mjs http://localhost:8888 admin "xxxx xxxx xxxx xxxx xxxx xxxx"');
	process.exit(1);
}

const baseUrl = baseUrlArg.replace(/\/$/, '');
const authHeader = 'Basic ' + Buffer.from(`${username}:${appPassword}`).toString('base64');
const headers = { Authorization: authHeader, 'Content-Type': 'application/json' };

console.log(`\n[1/2] Discovering abilities at ${baseUrl}/wp-json/wp-abilities/v1/abilities\n`);

const listRes = await fetch(`${baseUrl}/wp-json/wp-abilities/v1/abilities`, { headers });

if (!listRes.ok) {
	console.error(`Discovery failed: HTTP ${listRes.status} ${listRes.statusText}`);
	console.error(await listRes.text());
	process.exit(1);
}

const abilities = await listRes.json();

if (!Array.isArray(abilities) || abilities.length === 0) {
	console.error('No abilities returned. Is the plugin activated and is your user authenticated?');
	process.exit(1);
}

for (const a of abilities) {
	const slug = a.name ?? a.slug ?? '(unknown)';
	console.log(`  • ${slug}`);
	console.log(`      ${a.description || '(no description)'}`);
	console.log(`      input_schema: ${a.input_schema ? 'yes' : 'no'} | output_schema: ${a.output_schema ? 'yes' : 'no'}`);
}

const target =
	abilities.find((a) => (a.name ?? a.slug) === 'wcporto/echo-with-metadata') ??
	abilities[0];

const targetSlug = target.name ?? target.slug;
// The route accepts the slash literally — do NOT URL-encode it as %2F.
const runUrl = `${baseUrl}/wp-json/wp-abilities/v1/abilities/${targetSlug}/run`;

console.log(`\n[2/2] Invoking ${targetSlug} at ${runUrl}\n`);

const execRes = await fetch(runUrl, {
	method: 'POST',
	headers,
	// Run endpoint wraps params under an `input` key.
	body: JSON.stringify({ input: { message: 'Hello from the Node discovery script!' } }),
});

const result = await execRes.json();
console.log('Result:', JSON.stringify(result, null, 2));
