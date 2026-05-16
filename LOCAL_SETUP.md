# Local Site Setup — Rehearsal Mode

End-to-end recipe for standing up a Local site, dropping all six workshop plugins in (solutions pre-filled), and being ready to demo every exercise in slide order. Designed so you can do this once the night before the talk and have a clean rehearsal environment.

---

## 1. Install Local

1. Download from [https://localwp.com](https://localwp.com) (free; macOS, Windows, Linux).
2. Install and launch.
3. Sign in or skip the account prompt — not required.

## 2. Create the site

1. Click **+ Create a new site** → **Create a new site**.
2. **Site name:** `wcporto-rehearsal` (anything works).
3. **Environment:** choose **Custom**.
   - **PHP:** 8.1 or newer.
   - **Web server:** nginx (or Apache, either fine).
   - **Database:** the default MariaDB is fine.
4. **WordPress username:** `admin` · **password:** anything you'll remember · **email:** your address.
5. Click **Add Site** and wait for it to provision.

> **WordPress version note.** The Abilities API requires WP **6.9+**. Local installs the latest stable. If your fresh site is on 6.8 or below, click the site → top right kebab → **Open Site Shell** and run:
> ```bash
> wp core update --version=6.9 --force
> wp core update-db
> ```
> If 6.9 isn't released as stable yet, grab the nightly build:
> ```bash
> wp core download --version=nightly --force
> wp core update-db
> ```

## 3. Open Site Shell

In Local, click your site, then the **... menu → Open Site Shell**. This opens a terminal where:
- `wp` (WP-CLI) is on PATH
- the working directory is the WordPress root (e.g. `app/public`)

You'll do everything from this shell.

## 4. Run the install script

The workshop repo lives at `/Users/utasic/Desktop/WordCamp Porto 2026/wcporto-2026-abilities-workshop`. From the Local site shell:

```bash
bash "/Users/utasic/Desktop/WordCamp Porto 2026/wcporto-2026-abilities-workshop/scripts/install-rehearsal.sh" \
     "/Users/utasic/Desktop/WordCamp Porto 2026/wcporto-2026-abilities-workshop"
```

The script will:
1. Verify WP 6.9+ and WP-CLI are available.
2. Copy all six exercise plugins into `wp-content/plugins/`, replacing the starter `includes/` with the completed `solution/includes/`.
3. Activate every plugin.
4. Create a subscriber user `wsub` (password `test1234`) for the exercise 3 rejection demo.
5. Create one published post and one draft post (capture the draft ID for the exercise 5 publish demo).
6. Generate an Application Password for your admin user (for the exercise 6 REST demo).
7. Verify every ability is registered.

**At the end you'll see something like:**

```
============================================================
  Rehearsal install complete.
============================================================

Site:           http://wcporto-rehearsal.local
Admin user:     admin
Subscriber:     wsub  /  test1234
Draft post ID:  42
```

**Save the Application Password** printed in the previous step — you can't view it again. Paste it into a sticky note alongside your slides.

## 5. Run the demos

Open [`REHEARSAL_COMMANDS.md`](./REHEARSAL_COMMANDS.md) in a second window — that file has the exact command sequence in slide order. Run them in the Site Shell as you rehearse.

## 6. Tear down or reuse

- **Reset between rehearsals:** re-run `install-rehearsal.sh`. It wipes prior plugin folders and re-copies — safe to run repeatedly.
- **Clean teardown (keep the site):** run the cleanup script to remove the six plugins, the subscriber user, the seeded posts, and the rehearsal Application Password — leaves WordPress itself alone.
  ```bash
  bash "/Users/utasic/Desktop/WordCamp Porto 2026/wcporto-2026-abilities-workshop/scripts/cleanup.sh"
  # add --yes to skip the per-step confirmations
  ```
- **Hard reset:** in Local, right-click the site → **Reset**. Then re-run from step 2.
- **Delete entirely:** right-click → **Delete**.

---

## Troubleshooting

**`wp: command not found`**
You opened a regular Terminal, not Local's Site Shell. Close it and use **Open Site Shell** from inside Local.

**`bash: ...install-rehearsal.sh: Permission denied`**
The script was extracted without the executable bit. Either run it with `bash <path>` (as shown above — works either way) or `chmod +x` it first.

**An ability isn't registering**
Deactivate and reactivate that one plugin:
```bash
wp plugin deactivate wcporto-01-hello-ability
wp plugin activate   wcporto-01-hello-ability
```
Then re-run the verify command from `REHEARSAL_COMMANDS.md`.

**Application Password command fails**
On older WP-CLI you may need to upgrade: `wp cli update`. As a fallback, generate the app password in `wp-admin → Users → Profile → Application Passwords`.
