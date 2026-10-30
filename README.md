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

## Group Webinar Requests

Group webinar requests are managed separately from scheduled webinar registrations and one-on-one doubt sessions. Enable the feature in **Admin → Site Settings → Group Webinars**, then set the per-participant price, minimum and maximum group size, duration, and invitation expiry.

Each participant (including the organizer) receives an individual email invitation. Guest participants can use the unique expiring link to apply their own coupon and pay their own amount through Razorpay; payment status is updated only after server-side signature and payment verification. Admins can review participant payments, confirm or cancel a request, set its schedule and meeting link, and notify participants.

Before enabling paid requests in production, configure the existing Razorpay credentials and mail provider, and run a Laravel queue worker for invitation and schedule emails:

```bash
php artisan queue:work
```

Apply database changes using the standard `php artisan migrate` command.
