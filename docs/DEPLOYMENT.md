# Deployment

## XAMPP (local / LAN)

1. Place the project under `htdocs/eTab`.
2. Point `config/config.php` `base_path` to `/eTab` and `app_url` to the public URL (e.g. `http://192.168.1.10/eTab`).
3. `upload_url` should match (`/eTab/public/uploads`).
4. Run `install.php` once, then delete it.
5. Apache vhost (optional):

```apache
<VirtualHost *:80>
  ServerName etab.local
  DocumentRoot "C:/xampp/htdocs/eTab"
  <Directory "C:/xampp/htdocs/eTab">
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
```

If DocumentRoot is the app folder, set `base_path` to `''` and `upload_url` to `/public/uploads`.

## Vercel

This is a PHP/MySQL app. Vercel is built for Node/static sites, which is why you saw `404: NOT_FOUND`. The repo now includes `vercel.json` and `api/` so PHP can run.

In the Vercel project:

1. **Framework Preset:** Other
2. **Root Directory:** repository root (where `index.php` and `vercel.json` are)
3. Redeploy after pushing these files

Set **Environment Variables**:

| Name | Value |
|------|--------|
| `ETAB_ENV` | `hostinger` |
| `ETAB_DB_HOST` | Hostinger **remote** MySQL host (not `localhost`) from hPanel → Databases |
| `ETAB_DB_NAME` | `u934483906_etab` |
| `ETAB_DB_USER` | `u934483906_etab` |
| `ETAB_DB_PASS` | your MySQL password |
| `ETAB_DEBUG` | `0` |

In Hostinger, enable **Remote MySQL** and allow Vercel (or `%`). `localhost` will not work from Vercel.

Sessions and photo uploads are unreliable on Vercel (serverless). **Hostinger PHP hosting is the correct place** for eTab. Use Vercel only for a temporary preview.

## Hostinger (`etab.digoscity.gov.ph`)

The app detects this host and uses the Hostinger MySQL database automatically. Local XAMPP is unchanged.

1. In hPanel, point **etab.digoscity.gov.ph** at the site’s document root (usually `public_html` for that domain).
2. Upload the **entire eTab folder contents** into that document root (so `index.php` and `.htaccess` sit at the root, not inside another `/eTab` folder).
3. Make `public/uploads` writable (permission `755` or `775`).
4. Open [https://etab.digoscity.gov.ph/install.php](https://etab.digoscity.gov.ph/install.php) and click **Install now**. This creates tables and demo users in `u934483906_etab`.
5. Sign in at [https://etab.digoscity.gov.ph/login](https://etab.digoscity.gov.ph/login) with `admin@etab.local` / `Admin@123`, then change the password.
6. Delete `install.php` from the server.

If login pages 404, confirm `.htaccess` uploaded and **Apache rewrite** is on (Hostinger default).

## Production checklist

- PHP 8.1+, extensions: `pdo_mysql`, `mbstring`, `json`, `session`
- HTTPS
- MySQL user with rights only on `etab`
- `debug` => false
- Writable `public/uploads`
- Cron not required (polling is client-side)
- Backups: dump `etab` daily

## Performance notes

Indexes cover `scores (event_id, round)` and unique judge/contestant/criteria/round. Averaging is done in PHP after one scores query per event, which is appropriate for 1,000 contestants × tens of judges. For very large boards, prefer filtering by category/round on the results page.

Score POST is a single transactional upsert (target well under 500ms on local MySQL).

## Tests

```bash
php tests/run.php
```

No extra Composer packages are required to run the app.
