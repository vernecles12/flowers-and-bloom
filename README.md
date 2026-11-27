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
- Save orders with a cash-on-delivery, manual GCash-transfer, or debit-card-at-delivery preference.

Password reset codes are displayed on screen for this demo. The app also attempts to email the code through PHP `mail()` when mail is configured. Orders save the customer's payment preference. Payment is not collected or confirmed by this site: GCash transfers need details from the shop and manual confirmation, and debit-card payment is only at delivery if the shop has a card terminal. Card details are never requested. Cash on delivery is available without a payment provider.

## Project files

- `index.php` - storefront and dialogs
- `api.php` - account, reset-code, cart, profile, and order endpoints
- `api/shop.php` - JSON API for sessions, accounts, password resets, carts, orders, and newsletter subscriptions
- `api/lib/catalog.php` - product catalogue and customer-friendly descriptions
- `api/lib/newsletter.php` - private newsletter subscription storage
- `assets/images/payment-*.svg` - local payment method marks
- `api/index.php` - Vercel storefront entry point
- `assets/css/styles.css` and `assets/js/app.js` - responsive UI and interactions
- `assets/images/` - local JPG backgrounds and flower photography
- `storage/shop.json` - local users, carts, and orders (ignored by Git)

Product names, descriptions, and prices live in `api/lib/catalog.php`. The site uses system font stacks and does not download fonts from Google.


## Private Blob storage and account recovery

The deployed shop intentionally keeps customer records, password hashes, carts, orders, newsletter subscriptions, and PHP sessions in a **private** Vercel Blob store. Do not make this store public. Public Blob access is for public media such as product photos; customer and session data must remain protected.

For each Vercel project that serves this shop, confirm that `BLOB_STORE_ID` and `BLOB_READ_WRITE_TOKEN` identify the same private Blob store. The PHP REST client uses the configured read/write token first and can fall back to the request-scoped Vercel OIDC token when a project is configured for OIDC. Neither credential belongs in the browser or Git.

If a private-session read fails, the API returns a temporary storage/session error rather than silently treating the failed read as a new empty session. If an old CSRF token is detected, the browser refreshes the session state once and retries the original request once. Registration signs the new customer in immediately; retrying the same email/password after a lost response restores that account session instead of creating a duplicate account. A different password for an existing email is directed to sign-in or password reset.

## Deployment checks

After updating the code, verify the production and preview Vercel projects use the intended private store. Do not delete or recreate the store to troubleshoot a 403: that could separate the website from its existing customer and order records.


## Cyan storefront and SweetAlert updates

The `flowers-and-bloom-cyan.vercel.app` project shares the existing populated **private** Blob store used by `flowers-and-bloom-live`. Keep both `BLOB_STORE_ID` and `BLOB_READ_WRITE_TOKEN` aligned in the cyan project's production, preview, and development environments. Do not make customer data public.

SweetAlert notices use a green success check for successful sign-in and completed actions. Authentication-required actions use a red error cross and provide a sign-in action. Alert buttons use high-contrast labels, larger hit areas, and reduced-motion-aware transitions.
