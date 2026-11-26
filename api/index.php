<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f9f6ee">
    <meta name="description" content="Thoughtful flowers, gathered with care. Find your next bouquet at Flowers and Bloom.">
    <title>Flowers and Bloom — A little joy, delivered</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="announcement">A little note: delivering lovely bouquets around Metro Manila <span>✿</span></div>
    <header class="site-header">
        <a class="brand" href="#home" aria-label="Flowers and Bloom home">
            <span class="brand-mark">f<span>✿</span></span>
            <span class="brand-name">flowers <em>&</em> bloom<small>FLORAL STUDIO · MANILA</small></span>
        </a>
        <button class="mobile-menu-button" id="mobile-menu-button" aria-label="Open menu" aria-expanded="false"><span></span><span></span></button>
        <nav class="main-nav" id="main-nav" aria-label="Main navigation">
            <a href="#home" class="nav-link active">Home</a>
            <a href="#shop" class="nav-link">Shop</a>
            <a href="#our-story" class="nav-link">Our story</a>
            <a href="#journal" class="nav-link">The little things</a>
        </nav>
        <div class="header-actions">
            <button class="icon-button account-shortcut" id="account-shortcut" aria-label="Account settings"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.7-3.2 3.3-5 7-5s6.3 1.8 7 5"/></svg></button>
            <button class="cart-shortcut" id="cart-shortcut"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.1 11.1a2 2 0 0 0 2 1.6h8.4a2 2 0 0 0 1.9-1.4L21 9H6"/><circle cx="9.5" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg><span>Add to Cart</span><b id="cart-count">0</b></button>
            <button class="header-login" id="header-login">Sign in</button>
        </div>
    </header>

    <main>
        <section class="hero" id="home">
            <div class="hero-backdrop"></div>
            <div class="hero-content">
                <div class="eyebrow"><span></span> Flowers for the in-between moments <span></span></div>
                <h1>A little joy,<br>in full <em>bloom.</em></h1>
                <p>Hand-tied with heart. Delivered with care. Find something lovely for someone you love — even if that someone is you.</p>
                <div class="hero-actions">
                    <a href="#shop" class="button button-dark">Find your flowers <span>↗</span></a>
                    <a href="#our-story" class="text-link">Get to know us <span>↓</span></a>
                </div>
                <div class="hero-caption"><span class="caption-flower">✿</span><span>Always seasonal.<br>Always from the heart.</span></div>
            </div>
            <div class="hero-side-note">GROWN WITH CARE<br><span>✳</span><br>GIVEN WITH LOVE</div>
            <a class="scroll-cue" href="#shop"><span></span> A little further</a>
        </section>

        <section class="promise-strip" aria-label="Our promises">
            <div><span class="promise-icon">✽</span><p><b>Fresh, always</b><small>Flowers that arrive happy</small></p></div>
            <i></i>
            <div><span class="promise-icon">♡</span><p><b>Made by hand</b><small>Thoughtful from the first stem</small></p></div>
            <i></i>
            <div><span class="promise-icon">↗</span><p><b>On your doorstep</b><small>Delivering around Metro Manila</small></p></div>
        </section>

        <section class="shop-section section-wrap" id="shop">
            <div class="section-heading">
                <div><span class="eyebrow eyebrow-light">A few of our favorites</span><h2>Find your <em>flowers.</em></h2></div>
                <p>For big days, ordinary Tuesdays<br>and everything in between.</p>
            </div>
            <div class="shop-toolbar">
                <div class="filter-list" role="group" aria-label="Filter flowers">
                    <button class="filter-chip active" data-category="all">All flowers</button>
                    <button class="filter-chip" data-category="romance">Roses & romance</button>
                    <button class="filter-chip" data-category="signature">Signature</button>
                    <button class="filter-chip" data-category="bright">Bright & happy</button>
                </div>
                <div class="shop-count" id="shop-count"></div>
            </div>
            <div class="product-grid" id="product-grid" aria-live="polite"></div>
            <div class="pagination-row">
                <span id="page-label">01 — 04</span>
                <div class="pagination-controls"><button id="page-prev" aria-label="Previous products" disabled>←</button><span id="page-number">01 / 03</span><button id="page-next" aria-label="Next products">→</button></div>
                <span class="pagination-note">A fresh page of petals <b>✿</b></span>
            </div>
        </section>

        <section class="story-section" id="our-story">
            <div class="story-image-wrap">
                <div class="story-image"></div>
                <div class="story-stamp"><span>✿</span><b>GATHERED<br>WITH LOVE</b><small>EVERY SINGLE TIME</small></div>
            </div>
            <div class="story-copy">
                <span class="eyebrow eyebrow-light">A small studio with a big heart</span>
                <h2>We believe in<br>the <em>little things.</em></h2>
                <p>A fresh bunch on the kitchen table. A tiny note tucked under the ribbon. The moment someone opens the door and knows you were thinking of them.</p>
                <p>That’s why we make every bouquet by hand, with flowers picked for their season and the feeling you want to share.</p>
                <a href="#journal" class="text-link">A little more about us <span>↗</span></a>
                <div class="signature">With love, <em>the Flowers & Bloom girls</em><span>✿</span></div>
            </div>
        </section>

        <section class="little-things-section" id="journal">
            <span class="eyebrow eyebrow-light">Thoughts from the flower table</span>
            <h2>The little things <em>matter.</em></h2>
            <div class="journal-grid">
                <article class="journal-card"><div class="journal-illustration journal-one"><span>✿</span><i>♡</i></div><small>FLOWER NOTES · 3 MIN READ</small><h3>How to keep your flowers happy for longer</h3><p>A few easy things to do when your blooms get home.</p></article>
                <article class="journal-card"><div class="journal-illustration journal-two"><span>❀</span><i>✳</i></div><small>GIFTING GUIDE · 2 MIN READ</small><h3>A little something for no reason at all</h3><p>Because the nicest surprises don’t need a date.</p></article>
                <article class="journal-card"><div class="journal-illustration journal-three"><span>✼</span><i>♡</i></div><small>FROM THE STUDIO · 4 MIN READ</small><h3>What’s blooming this month</h3><p>A note on the seasonal stems we can’t stop loving.</p></article>
            </div>
        </section>

        <section class="newsletter-section">
            <div class="newsletter-flower">✿</div>
            <span class="eyebrow">A little love in your inbox</span>
            <h2>Good things <em>grow here.</em></h2>
            <p>Seasonal stems, gifting ideas and the occasional love note. Never too much, always lovely.</p>
            <form id="newsletter-form" class="newsletter-form"><label class="visually-hidden" for="newsletter-email">Your email address</label><input id="newsletter-email" type="email" placeholder="Your email address" required><button type="submit" aria-label="Subscribe">Count me in <span>→</span></button></form>
            <small class="newsletter-note">A small inbox treat, whenever we have something to say.</small>
        </section>

        <section class="cart-section section-wrap hidden" id="cart-view">
            <div class="section-heading"><div><span class="eyebrow eyebrow-light">A lovely choice</span><h2>Your flower <em>bag.</em></h2></div><a class="text-link" href="#shop">Keep looking <span>↗</span></a></div>
            <div class="cart-layout"><div id="cart-items" class="cart-items"></div><aside class="cart-summary"><span class="eyebrow eyebrow-light">A little summary</span><h3>Your total</h3><div class="summary-line"><span>Flowers</span><b id="summary-subtotal">₱0</b></div><div class="summary-line"><span>Delivery</span><span>At checkout</span></div><div class="summary-total"><span>Total</span><b id="summary-total">₱0</b></div><p>We’ll confirm your delivery details on the next step.</p><button class="button button-dark button-full" id="checkout-button">Continue to checkout <span>→</span></button><small>Secure, account-protected checkout</small></aside></div>
        </section>

        <section class="account-section section-wrap hidden" id="account-view">
            <div class="section-heading"><div><span class="eyebrow eyebrow-light">Your little corner</span><h2>Account <em>settings.</em></h2></div><button class="text-button" id="logout-button">Sign out <span>↗</span></button></div>
            <div class="account-layout"><div class="account-intro"><span class="account-avatar" id="account-avatar">✿</span><p class="eyebrow eyebrow-light">A warm welcome</p><h3 id="account-welcome">Hello there</h3><p>Keep your details current and we’ll take care of the flowers.</p><div id="account-order-count" class="account-order-count">No orders just yet. The first one could be a lovely one.</div></div><form class="account-form" id="profile-form"><h3>Your details</h3><label>Your name<input type="text" id="profile-name" name="name" required minlength="2"></label><label>Email address<input type="email" id="profile-email" name="email" readonly></label><div class="form-divider"></div><h3>A new password <small>(optional)</small></h3><p class="form-hint">Only fill these in if you’d like to change it.</p><label>Current password<input type="password" id="current-password" autocomplete="current-password"></label><label>New password<input type="password" id="new-password" autocomplete="new-password" minlength="8"></label><button class="button button-dark" type="submit">Save my details <span>→</span></button></form></div>
            <div class="order-history" id="order-history"></div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-top"><a class="brand footer-brand" href="#home"><span class="brand-mark">f<span>✿</span></span><span class="brand-name">flowers <em>&</em> bloom<small>FLORAL STUDIO · MANILA</small></span></a><p>Flowers for the little moments<br>that make a life.</p><div class="footer-links"><a href="#shop">Shop flowers</a><a href="#our-story">Our story</a><button id="footer-account">My account</button></div></div>
        <div class="footer-bottom"><span>© <span id="year"></span> Flowers and Bloom. Made with love, always.</span><span>Metro Manila · Philippines <b>✿</b></span></div>
    </footer>

    <div class="modal-backdrop hidden" id="auth-modal" aria-hidden="true">
        <section class="auth-modal" role="dialog" aria-modal="true" aria-labelledby="auth-title">
            <button class="modal-close" data-close-modal aria-label="Close">×</button>
            <div class="modal-flower">✿</div><span class="eyebrow eyebrow-light" id="auth-eyebrow">A little welcome</span><h2 id="auth-title">Come on <em>in.</em></h2><p class="auth-subtitle" id="auth-subtitle">Sign in to save your favorites and send something lovely.</p>
            <div class="auth-tabs"><button class="auth-tab active" data-auth-tab="login">Sign in</button><button class="auth-tab" data-auth-tab="register">Create an account</button></div>
            <form id="login-form" class="auth-form"><label>Email address<input type="email" name="email" placeholder="you@example.com" required autocomplete="email"></label><label>Password<input type="password" name="password" placeholder="At least 8 characters" required autocomplete="current-password"></label><button type="button" class="forgot-link" id="forgot-link">Forgot your password?</button><button class="button button-dark button-full" type="submit">Sign in <span>→</span></button></form>
            <form id="register-form" class="auth-form hidden"><label>Your name<input type="text" name="name" placeholder="What should we call you?" required minlength="2" autocomplete="name"></label><label>Email address<input type="email" name="email" placeholder="you@example.com" required autocomplete="email"></label><label>Password<input type="password" name="password" placeholder="At least 8 characters" required minlength="8" autocomplete="new-password"></label><button class="button button-dark button-full" type="submit">Create my account <span>→</span></button></form>
            <div class="auth-footer">By continuing, you’re agreeing to our little shop’s <a href="#home">terms & privacy</a>.</div>
        </section>
    </div>

    <div class="modal-backdrop hidden" id="reset-modal" aria-hidden="true">
        <section class="auth-modal reset-modal" role="dialog" aria-modal="true" aria-labelledby="reset-title">
            <button class="modal-close" data-close-reset aria-label="Close">×</button><div class="modal-flower">❀</div><span class="eyebrow eyebrow-light">A fresh start</span><h2 id="reset-title">Let’s get you <em>back in.</em></h2>
            <form id="reset-request-form" class="auth-form"><p class="auth-subtitle">Share your account email and we’ll make a one-time demo code for you.</p><label>Email address<input type="email" name="email" placeholder="you@example.com" required></label><button class="button button-dark button-full" type="submit">Get a reset code <span>→</span></button></form>
            <div id="reset-code-note" class="reset-code-note hidden" aria-live="polite"></div>
            <form id="reset-complete-form" class="auth-form hidden"><label>Six-digit demo code<input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required></label><label>New password<input type="password" name="password" minlength="8" required autocomplete="new-password"></label><button class="button button-dark button-full" type="submit">Set a new password <span>→</span></button></form>
            <button class="forgot-link reset-back-link" id="reset-back">Back to sign in</button>
        </section>
    </div>

    <div class="modal-backdrop hidden" id="checkout-modal" aria-hidden="true">
        <section class="auth-modal checkout-modal" role="dialog" aria-modal="true" aria-labelledby="checkout-title"><button class="modal-close" data-close-checkout aria-label="Close">×</button><div class="modal-flower">✽</div><span class="eyebrow eyebrow-light">The lovely last step</span><h2 id="checkout-title">Where shall we <em>send them?</em></h2><p class="auth-subtitle">Your flowers will be tied by hand and delivered with care.</p><form id="checkout-form" class="auth-form"><label>Recipient’s name<input type="text" name="recipient" required minlength="2"></label><label>Delivery address<textarea name="address" rows="3" placeholder="Street, barangay, city" required minlength="8"></textarea></label><label>Contact number<input type="tel" name="phone" required minlength="7" placeholder="+63"></label><button class="button button-dark button-full" type="submit">Place my flower order <span>→</span></button></form><small class="checkout-demo-note">Demo checkout · no payment is collected</small></section>
    </div>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
