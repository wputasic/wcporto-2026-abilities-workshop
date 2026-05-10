# Local WordPress 6.9+ setup

The workshop exercises require a local WP install on **WP 6.9 or newer** with PHP **8.1+**. Pick whichever option you're comfortable with.

## Option 1 — `wp-env` (recommended)

Official WP tool, one-command setup. Requires Node.js 18+ and Docker.

```bash
npm install -g @wordpress/env
mkdir my-wcporto-site && cd my-wcporto-site
echo '{"core": "WordPress/WordPress#6.9"}' > .wp-env.json
wp-env start
```

WP runs on `http://localhost:8888`. To activate an exercise plugin:

```bash
cp -r /path/to/wcporto-2026-abilities-workshop/exercises/01-hello-ability \
      $(wp-env install-path)/WordPress/wp-content/plugins/
```

Then activate it in `wp-admin → Plugins`.

## Option 2 — Local by Flywheel

GUI app for non-CLI users. Download from <https://localwp.com>. Create a new site on PHP 8.1+, upgrade WP to 6.9+ from the WP admin, then drop exercise folders into `app/public/wp-content/plugins/`.

## Option 3 — Studio by WordPress.com

Free, cross-platform. Download from <https://developer.wordpress.com/studio/>. Create a new site, ensure it's on 6.9+, then drag exercise folders into the site's `wp-content/plugins/` directory shown in the Studio UI.

## Verifying the Abilities API is available

After activating any exercise plugin, run:

```bash
wp eval 'echo function_exists("wp_register_ability") ? "OK" : "MISSING"; echo PHP_EOL;'
```

You should see `OK`. If you see `MISSING`, your WordPress version is below 6.9.

## Useful commands

List all registered abilities:

```bash
wp eval '
foreach ( wp_get_abilities() as $a ) {
    printf( "%-40s %s\n", $a->get_name(), $a->get_label() );
}
'
```

Hit the built-in REST endpoint (requires authentication):

```bash
curl -s http://localhost:8888/wp-json/wp-abilities/v1/abilities \
  --user "admin:application-password" | python3 -m json.tool
```
