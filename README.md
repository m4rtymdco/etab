# eTab — Event Judging Tabulator

PHP/MySQL app for judged events. It is already in `C:\xampp\htdocs\eTab` for **XAMPP**.

## Run on XAMPP (this PC)

IIS is using port **80**, so XAMPP Apache is on **8080**. Use these URLs (not `http://localhost/eTab` without a port).

1. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Install the database (once):

```bat
C:\xampp\php\php.exe C:\xampp\htdocs\eTab\install.php
```

Or open [http://localhost:8080/eTab/install.php](http://localhost:8080/eTab/install.php) and click **Install now**.

3. Sign in at [http://localhost:8080/eTab/login](http://localhost:8080/eTab/login)

   - Admin: `admin@etab.local` / `Admin@123`
   - Judge: `judge@etab.local` / `Judge@123`

Fallback if rewrite fails: [http://localhost:8080/eTab/index.php?r=login](http://localhost:8080/eTab/index.php?r=login)

phpMyAdmin: [http://localhost:8080/phpmyadmin](http://localhost:8080/phpmyadmin)

## Features

- Admin dashboard, events, contestants (CSV import/export), judges, criteria templates
- Weighted scoring (criteria must total 100%), optional drop highest/lowest judge
- Exclusive and Open contestant categories with separate rankings
- Judge scoring with confirmation, live weighted total, auto-saved drafts
- Hidden results until published; judges never see other judges’ scores
- Live standings (polling), Excel/CSV/PDF-print, winner certificates
- Analytics charts
- Light/dark theme, responsive layout

## Documentation

- [Admin setup](docs/ADMIN_SETUP.md)
- [User guide](docs/USER_GUIDE.md)
- [API](docs/API.md)
- [Deployment](docs/DEPLOYMENT.md)

## Tests

```bat
C:\xampp\php\php.exe C:\xampp\htdocs\eTab\tests\run.php
```

## Configuration

Database defaults match XAMPP (`root`, empty password, database `etab`). The app detects `/eTab` and the current host/port automatically.
