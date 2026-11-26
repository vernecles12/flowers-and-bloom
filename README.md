# Flowers and Bloom

A responsive flower shop website for XAMPP, with a PHP backend and a JSON file store.

## Run it locally

1. Keep this folder at C:/xampp/htdocs/flowers and bloom.
2. Start **Apache** in the XAMPP Control Panel. MySQL is not required.
3. Open http://localhost/flowers%20and%20bloom/ in a browser.

The storage directory must be writable by Apache. It is protected from direct browser access by its .htaccess file.

## Shop features

- Browse and filter 12 bouquet cards, with next and previous pagination.
- Register and sign in with hashed passwords and PHP sessions.
- See a floral “Welcome” on the first sign-in and “Welcome back” on later sign-ins.
- Request a password reset, receive a one-time code that expires after 10 minutes, and set a new password.
- Add products, change quantities, and place a demo order after signing in.
- Edit profile details and review saved orders.

Password-reset codes are displayed on screen for this local demo. The app also attempts to email the code through PHP mail() when mail is configured; without SMTP, the displayed demo code still works. Checkout records the order but does not collect payment.

## Project files

- index.php — storefront and dialogs
- api.php — account, reset-code, cart, profile and order endpoints
- assets/css/styles.css and assets/js/app.js — responsive UI and interactions
- assets/images/ — local JPG backgrounds and flower photography
- storage/shop.json — local users, carts and order records

Product names, descriptions and PHP prices are defined in api.php.
