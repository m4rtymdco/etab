# Admin setup guide

## 1. Prerequisites

- XAMPP (PHP 8.1+ recommended, MySQL/MariaDB, Apache)
- `mod_rewrite` enabled (optional but preferred)

## 2. Database

Default connection in `config/config.php`:

- Host: `127.0.0.1`
- Database: `etab`
- User: `root`
- Password: empty (typical local XAMPP)

Change these if your MySQL user is not `root` or has a password. You can also set `ETAB_DB_PASS` in the environment.

## 3. Install

On this PC XAMPP Apache uses **port 8080** (IIS occupies port 80).

1. Browse to `http://localhost:8080/eTab/install.php`, or run `C:\xampp\php\php.exe C:\xampp\htdocs\eTab\install.php`.
2. Confirm the schema and seed data were created.
3. Log in at `http://localhost:8080/eTab/login` as `admin@etab.local` / `Admin@123`.
4. **Change the admin password** under Profile.
5. Remove `install.php`.

Manual alternative: import `database/schema.sql` in phpMyAdmin, then create an admin user with `password_hash` (PHP `PASSWORD_DEFAULT`).

## 4. First event checklist

1. **Judges** → add accounts and note their passwords.
2. **Events** → create event (date, venue, status Ongoing, score 1–100).
3. Enable **Drop highest & lowest** only if you will have at least three judges.
4. Open the event → add **criteria** whose weights sum to **100%**, or apply a template.
5. **Contestants** → add as **Exclusive** or **Open**, or CSV-import, then assign to the event (entry numbers optional).
6. Assign judges on the event form.
7. Judges sign in and score. Watch **Dashboard** progress.
8. **Publish results** when scoring is complete.
9. Export CSV/Excel, print PDF report, or print certificates for top 3.

## 5. Password reset

Forgot-password generates a time-limited token (2 hours). On local XAMPP, the reset URL is shown on screen because SMTP is off (`config.php` → `mail.enabled`). Enable mail later if you add an SMTP library.

## 6. File uploads

Contestant photos go to `public/uploads/`. Ensure the folder is writable by Apache.

## 7. Security notes

- Use HTTPS in production.
- Set `debug` to false.
- Use a strong MySQL password and a dedicated database user.
- Keep CSRF tokens (already on forms) and do not disable sessions.
