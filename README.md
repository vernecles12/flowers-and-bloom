# Flowers and Bloom

A responsive flower shop website with a PHP backend. It runs on XAMPP with local JSON storage and uses a private Vercel Blob store for persistent data when deployed.

## Run locally with XAMPP

1. Keep this folder at `C:/xampp/htdocs/flowers and bloom`.
2. Start **Apache** in the XAMPP Control Panel. MySQL is not required.
3. Open `http://localhost/flowers%20and%20bloom/` in a browser.

The `storage` directory must be writable by Apache. Its `.htaccess` blocks direct browser access.

## Deploy to Vercel

The Vercel entry points are `api/index.php` and `api/shop.php`. `vercel.json` routes the homepage and JSON API through the community PHP runtime, while images and stylesheets stay static assets.

The Vercel project uses a private Blob store for accounts, carts, orders, newsletter sign-ups, and PHP sessions. Vercel supplies `BLOB_READ_WRITE_TOKEN` when the store is connected to the project; `BLOB_STORE_ID` identifies that store. Local XAMPP continues to use `storage/shop.json`. That file is excluded from deployment and version control so local account data is not published.

## Shop features

- Browse and filter 12 bouquet cards with next and previous pagination.
- Register and sign in with hashed passwords and PHP sessions.
- See a floral "Welcome" on the first sign-in and "Welcome back" on later sign-ins.
- Request a password reset, receive a one-time code that expires after 10 minutes, and set a new password.
- Add products, change quantities, and place orders after signing in.
- Edit profile details, change the password, upload or remove a private profile photo, and review saved orders.
- Join the flower notes list; email addresses are validated, stored privately, and de-duplicated.
- Pay for orders on delivery, or use PayMongo-hosted GCash and debit-card checkout.

Password reset codes are displayed on screen for this demo. The app also attempts to email the code through PHP `mail()` when mail is configured. Cash-on-delivery orders are saved by the shop. GCash and card payments redirect to PayMongo's hosted checkout; no card details are collected by this app. Online checkout stays unavailable until `PAYMONGO_SECRET_KEY` and `PAYMONGO_WEBHOOK_SECRET` are set. Use a PayMongo test secret key first, and register `https://your-domain/api/shop.php?action=paymongo_webhook` for `checkout_session.payment.paid`. Set `APP_BASE_URL` to the public HTTPS site URL if Vercel's automatic URL is not the address customers use. Payment success is confirmed by a signed webhook, never by the browser return alone.

## Project files

- `index.php` - storefront and dialogs
- `api.php` - account, reset-code, cart, profile, and order endpoints
- `api/shop.php` - JSON API for sessions, accounts, password resets, carts, orders, and newsletter subscriptions
- `api/lib/catalog.php` - product catalogue and customer-friendly descriptions
- `api/lib/newsletter.php` - private newsletter subscription storage
- `assets/images/payment-*.svg` - local payment method marks
- `.env.example` - local configuration names for PayMongo hosted checkout
- `api/index.php` - Vercel storefront entry point
- `assets/css/styles.css` and `assets/js/app.js` - responsive UI and interactions
- `assets/images/` - local JPG backgrounds and flower photography
- `storage/shop.json` - local users, carts, and orders (ignored by Git)

Product names, descriptions, and prices live in `api/lib/catalog.php`. The site uses system font stacks and does not download fonts from Google.
