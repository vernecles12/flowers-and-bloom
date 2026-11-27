<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f9f6ee">
    <meta name="description" content="Hand-tied flowers for birthdays, ordinary Tuesdays, and all the days in between. Made with care in Metro Manila.">
    <title>Flowers and Bloom | A little joy, sent your way</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=navbar-layout-20261127-r3">
</head>
<body>
    <div class="announcement">Fresh bunches, delivered around Metro Manila <span>âœ¿</span></div>
    <header class="site-header">
        <a class="brand" href="#home" aria-label="Flowers and Bloom home">
            <span class="brand-mark">f<span>âœ¿</span></span>
            <span class="brand-name">flowers <em>&</em> bloom<small>FLORAL STUDIO Â· MANILA</small></span>
        </a>
        <button class="mobile-menu-button" id="mobile-menu-button" aria-label="Open menu" aria-expanded="false"><span></span><span></span></button>
        <nav class="main-nav" id="main-nav" aria-label="Main navigation">
            <a href="#home" class="nav-link active">Home</a>
            <a href="#shop" class="nav-link" id="shop-nav-link">Shop</a>
            <a href="#account-view" class="nav-link" id="profile-nav-link">Profile settings</a>
            <a href="#our-story" class="nav-link">The studio</a>
            <a href="#journal" class="nav-link">Flower notes</a>
            <a href="#admin-view" class="nav-link hidden" id="admin-nav-link">Studio dashboard</a>
        </nav>
        <div class="header-actions">
            <button class="theme-toggle" id="theme-toggle" type="button" aria-label="Switch to dark mode" title="Switch to dark mode"><span class="theme-icon" aria-hidden="true">â˜¾</span><span class="theme-label">Dark</span></button>
            <button class="icon-button account-shortcut" id="account-shortcut" aria-label="Account settings"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.7-3.2 3.3-5 7-5s6.3 1.8 7 5"/></svg></button>
            <button class="wishlist-shortcut" id="wishlist-shortcut" type="button" aria-label="Saved flowers, 0 items"><span aria-hidden="true">â™¡</span><b id="wishlist-count">0</b></button>
            <button class="cart-shortcut" id="cart-shortcut" aria-label="My Cart, 0 items"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.1 11.1a2 2 0 0 0 2 1.6h8.4a2 2 0 0 0 1.9-1.4L21 9H6"/><circle cx="9.5" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg><span>My Cart</span><b id="cart-count" aria-live="polite">0</b></button>
            <button class="header-login" id="header-login">Sign in</button>
        </div>
    </header>

    <main>
        <section class="hero" id="home">
            <div class="hero-backdrop"></div>
            <div class="hero-content">
                <div class="eyebrow"><span></span> For the days worth celebrating, and the ones that aren't <span></span></div>
                <h1>A little joy,<br>sent <em>your way.</em></h1>
                <p>Birthday flowers, a thank-you, or just because. Tell us who theyâ€™re for and weâ€™ll put together a bunch worth opening the door for.</p>
                <div class="hero-actions">
                    <a href="#shop" class="button button-dark">Browse Shops <span>â†—</span></a>
                    <a href="#our-story" class="text-link">Meet the studio <span>â†“</span></a>
                </div>
                <div class="hero-caption"><span class="caption-flower">âœ¿</span><span>Picked with care.<br>Tied by hand.</span></div>
            </div>
            <div class="hero-side-note">GROWN WITH CARE<br><span>âœ³</span><br>GIVEN WITH LOVE</div>
            <a class="scroll-cue" href="#shop"><span></span> A little further</a>
        </section>

        <section class="promise-strip" aria-label="Our promises">
            <div><span class="promise-icon">âœ½</span><p><b>Seasonal flowers</b><small>Picked fresh each week</small></p></div>
            <i></i>
            <div><span class="promise-icon">â™¡</span><p><b>Arranged by hand</b><small>Made at our Manila studio</small></p></div>
            <i></i>
            <div><span class="promise-icon">â†—</span><p><b>City delivery</b><small>Across Metro Manila</small></p></div>
        </section>

        <section class="shop-section section-wrap hidden" id="shop">
            <div class="section-heading">
                <div><span class="eyebrow eyebrow-light">Made fresh at the studio</span><h2>Find your <em>flowers.</em></h2></div>
                <p>Have a look around. The right bunch<br>usually has a way of finding you.</p>
            </div>
            <div class="shop-toolbar">
                <div class="filter-list" role="group" aria-label="Filter flowers">
                    <button class="filter-chip active" data-category="all">All flowers</button>
                    <button class="filter-chip" data-category="romance">Roses & romance</button>
                    <button class="filter-chip" data-category="signature">Signature</button>
                    <button class="filter-chip" data-category="bright">Bright & happy</button>
                </div>
                <div class="shop-discovery-controls">
                    <label class="shop-search"><span aria-hidden="true">âŒ•</span><input id="product-search" type="search" placeholder="Search flowers" aria-label="Search flowers"></label>
                    <label class="shop-sort"><span>Sort</span><select id="product-sort" aria-label="Sort flower results"><option value="featured">Featured</option><option value="price-low">Price: low to high</option><option value="price-high">Price: high to low</option><option value="name">Name: A to Z</option><option value="rating">Top rated</option></select></label>
                    <div class="shop-count" id="shop-count"></div>
                </div>
            </div>
            <div class="product-grid" id="product-grid" aria-live="polite"></div>
            <div class="pagination-row">
                <span id="page-label">01 â€” 04</span>
                <div class="pagination-controls"><button id="page-prev" aria-label="Previous products" disabled>â†</button><span id="page-number">01 / 03</span><button id="page-next" aria-label="Next products">â†’</button></div>
                <span class="pagination-note">A fresh page of petals <b>âœ¿</b></span>
            </div>
        </section>

        <section class="story-section" id="our-story">
            <div class="story-image-wrap">
                <div class="story-image"></div>
                <div class="story-stamp" style="color:#263c31 !important; opacity:1;"><span>âœ¿</span><b style="color:#263c31 !important; -webkit-text-fill-color:#263c31 !important; opacity:1;">GATHERED<br>WITH LOVE</b><small style="color:#586b5d !important; -webkit-text-fill-color:#586b5d !important; opacity:1;">EVERY SINGLE TIME</small></div>
            </div>
            <div class="story-copy">
                <span class="eyebrow eyebrow-light">A small studio with a big heart</span>
                <h2>We believe in<br>the <em>little things.</em></h2>
                <p>We started Flowers and Bloom because a bunch of flowers can say the thing you meant to say all along. It can make a kitchen feel like home, or let a friend know you havenâ€™t forgotten about them.</p>
                <p>We buy what looks good that week, then make each order by hand here in Manila. No two bunches are exactly alike, and thatâ€™s part of the fun.</p>
                <a href="#journal" class="text-link">A few notes from us <span>â†—</span></a>
                <div class="signature">See you at the flower table, <em>the Flowers and Bloom team</em><span>âœ¿</span></div>
            </div>
        </section>

        <section class="little-things-section" id="journal">
            <span class="eyebrow eyebrow-light">A few things weâ€™ve learned along the way</span>
            <h2>Notes from the <em>flower table.</em></h2>
            <div class="journal-grid">
                <article class="journal-card"><div class="journal-illustration journal-one"><img class="journal-photo" src="assets/images/notes-flower-care.jpg" alt="A sunny jar of mixed seasonal flowers" loading="lazy"></div><small>FLOWER CARE</small><h3>Help your flowers last a little longer</h3><p>Fresh water, a clean vase, and a quick trim make a real difference.</p></article>
                <article class="journal-card"><div class="journal-illustration journal-two"><img class="journal-photo" src="assets/images/notes-gifting.jpg" alt="A hand-tied pink and peach bouquet" loading="lazy"></div><small>GIFTING</small><h3>Sending flowers just because</h3><p>You donâ€™t need a special date to let someone know theyâ€™re on your mind.</p></article>
                <article class="journal-card"><div class="journal-illustration journal-three"><img class="journal-photo" src="assets/images/notes-studio.jpg" alt="Pink carnations arranged with delicate white flowers" loading="lazy"></div><small>FROM THE STUDIO</small><h3>What weâ€™re bringing in this week</h3><p>A peek at the seasonal stems weâ€™ve been taking home ourselves.</p></article>
            </div>
        </section>

        <section class="newsletter-section hidden" id="studio-letters">
            <div class="newsletter-flower">âœ¿</div>
            <span class="eyebrow">Studio letters</span>
            <h2>Flowers, and a little <em>good news.</em></h2>
            <p>A note now and then about whatâ€™s in season, how to care for your flowers, and what weâ€™re making at the studio. Add your email to join; weâ€™ll confirm it here, so you can stay on this page.</p>
            <form id="newsletter-form" class="newsletter-form"><label class="visually-hidden" for="newsletter-email">Your email address</label><input id="newsletter-email" name="email" type="email" maxlength="254" autocomplete="email" placeholder="Your email address" required><button type="submit" aria-label="Join the flower notes list">Join the list <span>â†’</span></button></form>
            <p class="newsletter-result hidden" id="newsletter-result" role="status" aria-live="polite"></p>
            <button type="button" class="newsletter-change hidden" id="newsletter-change">Use another email address</button>
            <small class="newsletter-note">A short email every so often, from our studio to yours.</small>
        </section>

        <section class="cart-section section-wrap hidden" id="cart-view">
            <div class="section-heading"><div><span class="eyebrow eyebrow-light">Your selections</span><h2>Your <em>cart.</em></h2></div><a class="text-link" href="#shop">Keep browsing <span>â†—</span></a></div>
            <div class="cart-layout"><div id="cart-items" class="cart-items"></div><aside class="cart-summary"><span class="eyebrow eyebrow-light">Order summary</span><h3>Your total</h3><div class="summary-line"><span>Flowers</span><b id="summary-subtotal">â‚±0</b></div><div class="summary-line"><span>Delivery</span><span>At checkout</span></div><div class="summary-total"><span>Total</span><b id="summary-total">â‚±0</b></div><p>Add the recipient and address on the next screen.</p><button class="button button-dark button-full" id="checkout-button">Continue to checkout <span>â†’</span></button><small>Secure, account-protected checkout</small></aside></div>
        </section>

        <section class="wishlist-section section-wrap hidden" id="wishlist-view">
            <div class="section-heading">
                <div><span class="eyebrow eyebrow-light">Your little collection</span><h2>Saved <em>flowers.</em></h2></div>
                <p id="wishlist-count-label">Your favourite bouquets, ready when you are.</p>
            </div>
            <div class="product-grid wishlist-grid" id="wishlist-grid" aria-live="polite"></div>
        </section>

        <section class="account-section section-wrap hidden" id="account-view">
            <div class="section-heading"><div><span class="eyebrow eyebrow-light">Your details and orders</span><h2>Profile <em>settings.</em></h2></div><button class="text-button" id="logout-button">Sign out <span>â†—</span></button></div>
            <div class="account-layout"><div class="account-intro"><div class="account-avatar" id="account-avatar"><span id="account-avatar-initial">âœ¿</span><img id="account-avatar-image" class="hidden" alt="Your profile photo"></div><label class="avatar-upload-button" for="profile-photo-input">Choose a profile photo</label><p class="avatar-hint">JPG, PNG, or WebP Â· stored privately on your account</p><p class="eyebrow eyebrow-light">Your account</p><h3 id="account-welcome">Hello there</h3><p>Update your name and password here. Your order history is below.</p><div id="account-order-count" class="account-order-count">No orders yet. Orders you place will appear here.</div></div><form class="account-form" id="profile-form"><h3>Your details</h3><input type="file" id="profile-photo-input" class="visually-hidden" accept="image/jpeg,image/png,image/webp"><button class="text-button remove-avatar-button" type="button" id="remove-profile-photo">Remove profile photo</button><label>Your name<input type="text" id="profile-name" name="name" required minlength="2"></label><label>Email address<input type="email" id="profile-email" name="email" readonly></label><div class="form-divider"></div><h3>A new password <small>(optional)</small></h3><p class="form-hint">Only fill these in if youâ€™d like to change it.</p><label>Current password<input type="password" id="current-password" autocomplete="current-password"></label><label>New password<input type="password" id="new-password" autocomplete="new-password" minlength="8"></label><button class="button button-dark" type="submit">Save my details <span>â†’</span></button></form></div>
            <div class="order-history" id="order-history"></div>
        </section>

        <section class="admin-section section-wrap hidden" id="admin-view">
            <div class="section-heading">
                <div><span class="eyebrow eyebrow-light">Private staff area</span><h2>Studio <em>dashboard.</em></h2></div>
                <p>Manage fulfilment and available bouquets.<br>Only authorised staff can open this page.</p>
            </div>
            <div id="admin-content" class="admin-content">
                <p class="admin-empty">Loading your studio overviewâ€¦</p>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-top"><a class="brand footer-brand" href="#home"><span class="brand-mark">f<span>âœ¿</span></span><span class="brand-name">flowers <em>&</em> bloom<small>FLORAL STUDIO Â· MANILA</small></span></a><p>Flowers, arranged by hand<br>in Metro Manila.</p><div class="footer-links"><a href="#shop">Shop flowers</a><a href="#our-story">The studio</a><button id="footer-account">My account</button></div></div>
        <div class="footer-bottom"><span>Â© <span id="year"></span> Flowers and Bloom. </span><span>Metro Manila Â· Philippines <b>âœ¿</b></span></div>
    </footer>

    <div class="modal-backdrop hidden" id="reviews-modal" aria-hidden="true">
        <section class="reviews-modal" role="dialog" aria-modal="true" aria-labelledby="reviews-title">
            <button class="modal-close" data-close-reviews aria-label="Close flower details">Ã—</button>
            <div class="modal-flower" aria-hidden="true">âœ¿</div>
            <span class="eyebrow eyebrow-light">A closer look</span>
            <h2 id="reviews-title"></h2>
            <div class="reviews-overview" id="reviews-overview"></div>
            <div class="sample-review-note">Sample reviews for this website demo. These are not verified customer purchases.</div>
            <div class="product-review-list" id="product-review-list"></div>
        </section>
    </div>

    <div class="modal-backdrop hidden" id="auth-modal" aria-hidden="true">
        <section class="auth-modal" role="dialog" aria-modal="true" aria-labelledby="auth-title">
            <button class="modal-close" data-close-modal aria-label="Close">Ã—</button>
            <div class="modal-flower">âœ¿</div><span class="eyebrow eyebrow-light" id="auth-eyebrow">Your account</span><h2 id="auth-title">Sign in to <em>continue.</em></h2><p class="auth-subtitle" id="auth-subtitle">Sign in to see your orders and manage your details.</p>
            <div class="auth-tabs"><button class="auth-tab active" data-auth-tab="login">Sign in</button><button class="auth-tab" data-auth-tab="register">Create an account</button></div>
            <form id="login-form" class="auth-form"><label>Email address<input type="email" name="email" placeholder="you@example.com" required autocomplete="email"></label><label>Password<input type="password" name="password" placeholder="At least 8 characters" required autocomplete="current-password"></label><button type="button" class="forgot-link" id="forgot-link">Forgot your password?</button><button class="button button-dark button-full" type="submit">Sign in <span>â†’</span></button></form>
            <form id="register-form" class="auth-form hidden"><label>Your name<input type="text" name="name" placeholder="What should we call you?" required minlength="2" autocomplete="name"></label><label>Email address<input type="email" name="email" placeholder="you@example.com" required autocomplete="email"></label><label>Password<input type="password" name="password" placeholder="At least 8 characters" required minlength="8" autocomplete="new-password"></label><button class="button button-dark button-full" type="submit">Create my account <span>â†’</span></button></form>
            <div class="auth-footer">We only use your details to manage your account and orders.</div>
        </section>
    </div>

    <div class="modal-backdrop hidden" id="reset-modal" aria-hidden="true">
        <section class="auth-modal reset-modal" role="dialog" aria-modal="true" aria-labelledby="reset-title">
            <button class="modal-close" data-close-reset aria-label="Close">Ã—</button><div class="modal-flower">â€</div><span class="eyebrow eyebrow-light">Password help</span><h2 id="reset-title">Letâ€™s get you <em>back in.</em></h2>
            <form id="reset-request-form" class="auth-form"><p class="auth-subtitle">Enter the email on your account. Weâ€™ll show a one-time demo code here.</p><label>Email address<input type="email" name="email" placeholder="you@example.com" required></label><button class="button button-dark button-full" type="submit">Get a reset code <span>â†’</span></button></form>
            <div id="reset-code-note" class="reset-code-note hidden" aria-live="polite"></div>
            <form id="reset-complete-form" class="auth-form hidden"><label>Six-digit demo code<input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required></label><label>New password<input type="password" name="password" minlength="8" required autocomplete="new-password"></label><button class="button button-dark button-full" type="submit">Set a new password <span>â†’</span></button></form>
            <button class="forgot-link reset-back-link" id="reset-back">Back to sign in</button>
        </section>
    </div>

    <div class="modal-backdrop hidden" id="checkout-modal" aria-hidden="true">
        <section class="auth-modal checkout-modal" role="dialog" aria-modal="true" aria-labelledby="checkout-title"><button class="modal-close" data-close-checkout aria-label="Close">Ã—</button><div class="modal-flower">âœ½</div><span class="eyebrow eyebrow-light">Delivery and payment</span><h2 id="checkout-title">Where shall we <em>send them?</em></h2><p class="auth-subtitle">Enter the delivery details, then choose how youâ€™d like to pay.</p><form id="checkout-form" class="auth-form"><label>Recipientâ€™s name<input type="text" name="recipient" required minlength="2"></label><label>Delivery address<textarea name="address" rows="3" placeholder="Street, barangay, city" required minlength="8"></textarea></label><label>Contact number<input type="tel" name="phone" required minlength="7" placeholder="+63"></label><fieldset class="payment-options"><legend>Payment method</legend><label class="payment-choice"><input type="radio" name="paymentMethod" value="cod" checked><img class="payment-logo" src="assets/images/payment-cod.svg" alt=""><span><b>Cash on delivery</b><small>Pay when your flowers arrive</small></span></label><label class="payment-choice"><input type="radio" name="paymentMethod" value="gcash"><img class="payment-logo" src="assets/images/payment-gcash.svg" alt=""><span><b>GCash transfer</b><small>Manual payment; shop confirmation required</small></span></label><label class="payment-choice"><input type="radio" name="paymentMethod" value="card"><img class="payment-logo" src="assets/images/payment-card.svg" alt=""><span><b>Debit card at delivery</b><small>Only if a card terminal is available</small></span></label><p class="payment-demo-note" role="status">These are manual/demo options. This site does not collect GCash transfers or debit-card details, and no payment is taken when you place an order.</p></fieldset><button class="button button-dark button-full" id="place-order-button" type="submit">Place order <span>â†’</span></button></form><small class="checkout-demo-note">GCash account details are provided by the shop separately. Never enter card details on this site.</small></section>
    </div>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/app.js?v=navbar-layout-20261127-r3"></script>
</body>
</html>
