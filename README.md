# Physio Academy

Physio Academy is a Laravel-based CMS and learning platform for physiotherapy academic content, topics, exam aid, pages, media, menus, and admin-managed site settings.

## Project

- Application name: `Physio Academy`
- Composer package: `physio/physio-academy`
- Local URL: `http://physio-academy.test`

## Setup Notes

Copy `.env.example` to `.env`, configure the database connection for your local environment, then run the normal Laravel setup commands:

```bash
composer install
php artisan key:generate
php artisan migrate
```

## Maintenance

After configuration or branding changes, clear Laravel caches:

```bash
composer dump-autoload
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
```
