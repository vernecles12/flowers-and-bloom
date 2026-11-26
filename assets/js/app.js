(function () {
    "use strict";

    var state = { csrf: "", user: null, products: [], cart: { items: [], total: 0, count: 0 }, orders: [] };
    var page = "home";
    var category = "all";
    var currentPage = 1;
    var pageSize = 8;
    var pendingProduct = null;
    var resetEmail = "";
    var selectedProfileImage = null;
    var removeProfileImage = false;
    var toastTimer;
    var homeSections = ["home", "shop", "our-story", "journal"];
    var productGrid = document.getElementById("product-grid");
    var money = new Intl.NumberFormat("en-PH", { style: "currency", currency: "PHP", maximumFractionDigits: 0 });
    var scriptBasePath = document.currentScript ? new URL("../../", document.currentScript.src).pathname : "/";
    var apiEndpoint = scriptBasePath === "/" ? "/api/shop.php" : scriptBasePath + "api.php";

    function setTheme(theme) {
        var dark = theme === "dark";
        document.documentElement.dataset.theme = dark ? "dark" : "light";
        var toggle = document.getElementById("theme-toggle");
        toggle.setAttribute("aria-label", dark ? "Switch to light mode" : "Switch to dark mode");
        toggle.title = dark ? "Switch to light mode" : "Switch to dark mode";
        toggle.querySelector(".theme-icon").textContent = dark ? "☀" : "☾";
        toggle.querySelector(".theme-label").textContent = dark ? "Light" : "Dark";
        try { localStorage.setItem("flowers-and-bloom-theme", dark ? "dark" : "light"); } catch (error) { /* Storage can be disabled; theme still works for this visit. */ }
    }

    var initialTheme = "light";
    try { initialTheme = localStorage.getItem("flowers-and-bloom-theme") || "light"; } catch (error) { /* Keep the light default. */ }
    setTheme(initialTheme);

    function escapeHtml(value) {
        return String(value == null ? "" : value).replace(/[&<>"']/g, function (char) {
            return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[char];
        });
    }

    async function api(action, data) {
        var options = { method: data ? "POST" : "GET", headers: {} };
        if (data) {
            options.headers["Content-Type"] = "application/json";
            options.body = JSON.stringify(Object.assign({ csrf: state.csrf }, data));
        }
        var response;
        try {
            response = await fetch(apiEndpoint + "?action=" + encodeURIComponent(action), options);
        } catch (error) {
            throw new Error("We couldn’t reach the shop. Check your connection and try again.");
        }
        var result;
        try { result = await response.json(); } catch (error) { throw new Error("The shop couldn’t load that response. Refresh and try again."); }
        if (!response.ok || result.ok === false) {
            var requestError = new Error(result.message || "That didn’t work. Please try again.");
            requestError.status = response.status;
            throw requestError;
        }
        return result;
    }

    function notify(message) {
        var toast = document.getElementById("toast");
        toast.textContent = message;
        toast.classList.add("visible");
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toast.classList.remove("visible"); }, 2800);
    }

    function floralNotice(title, message, confirmText) {
        if (window.Swal && typeof window.Swal.fire === "function") {
            return window.Swal.fire({
                title: title,
                html: message,
                icon: "success",
                iconColor: "#ad7468",
                confirmButtonText: confirmText || "Lovely",
                customClass: { popup: "floral-alert" },
                buttonsStyling: false
            });
        }
        return new Promise(function (resolve) {
            var layer = document.createElement("div");
            layer.className = "modal-backdrop floral-fallback-backdrop";
            layer.innerHTML = '<section class="floral-fallback-card" role="dialog" aria-modal="true"><div>✿</div><h2></h2><p></p><button type="button"></button></section>';
            layer.querySelector("h2").textContent = title;
            layer.querySelector("p").textContent = message.replace(/<[^>]+>/g, "");
            layer.querySelector("button").textContent = confirmText || "Lovely";
            layer.addEventListener("click", function (event) {
                if (event.target === layer || event.target.tagName === "BUTTON") { layer.remove(); resolve(); }
            });
            document.body.appendChild(layer);
        });
    }

    function showWelcome(user, type) {
        var first = type !== "back";
        var title = first ? "Welcome" : "Welcome back";
        var name = user && user.name ? user.name.split(/\s+/)[0] : "flower friend";
        var message = first
            ? "Glad you found us, " + escapeHtml(name) + ". Have a look around."
            : "Good to see you again, " + escapeHtml(name) + ". What can we help you find today?";
        floralNotice(title, message, "Have a look");
    }

    function setHeader() {
        var login = document.getElementById("header-login");
        login.textContent = state.user ? "My account" : "Sign in";
        document.getElementById("cart-count").textContent = state.cart.count || 0;
    }

    function renderProducts() {
        var filtered = state.products.filter(function (product) {
            return category === "all" || product.category === category;
        });
        var pages = Math.max(1, Math.ceil(filtered.length / pageSize));
        currentPage = Math.min(currentPage, pages);
        var start = (currentPage - 1) * pageSize;
        var items = filtered.slice(start, start + pageSize);
        document.getElementById("shop-count").textContent = filtered.length + (filtered.length === 1 ? " bouquet" : " bouquets");
        document.getElementById("page-label").textContent = (filtered.length ? String(start + 1).padStart(2, "0") : "00") + " — " + String(Math.min(start + pageSize, filtered.length)).padStart(2, "0");
        document.getElementById("page-number").textContent = String(currentPage).padStart(2, "0") + " / " + String(pages).padStart(2, "0");
        document.getElementById("page-prev").disabled = currentPage <= 1;
        document.getElementById("page-next").disabled = currentPage >= pages;
        if (!items.length) {
            productGrid.innerHTML = '<div class="empty-products">There aren’t any flowers in this little corner just yet.</div>';
            return;
        }
        productGrid.innerHTML = items.map(function (product, index) {
            return '<article class="product-card" style="animation-delay:' + (index * 45) + 'ms">' +
                '<div class="product-photo"><span class="product-tag">' + escapeHtml(product.tag) + '</span>' +
                '<img src="assets/images/' + encodeURIComponent(product.image) + '" alt="' + escapeHtml(product.name) + ' bouquet" loading="lazy" onerror="this.style.opacity=0.15;this.alt=\'Flower photo loading soon\'"></div>' +
                '<div class="product-card-top"><h3>' + escapeHtml(product.name) + '</h3><span class="product-price">' + money.format(product.price) + '</span></div>' +
                '<p class="product-description">' + escapeHtml(product.description) + '</p>' +
                '<button class="add-button" data-add="' + escapeHtml(product.id) + '">Add to bag <span>+</span></button></article>';
        }).join("");
    }

    function renderCart() {
        document.getElementById("cart-count").textContent = state.cart.count || 0;
        document.getElementById("summary-subtotal").textContent = money.format(state.cart.total || 0);
        document.getElementById("summary-total").textContent = money.format(state.cart.total || 0);
        var root = document.getElementById("cart-items");
        if (!state.cart.items || state.cart.items.length === 0) {
            root.innerHTML = '<div class="cart-empty"><span>✿</span><h3>Your cart is empty.</h3><p>Browse the shop to find a bunch you like.</p><button class="button button-dark" data-go-shop>Browse flowers <span>→</span></button></div>';
            return;
        }
        root.innerHTML = state.cart.items.map(function (item) {
            return '<article class="cart-item"><img src="assets/images/' + encodeURIComponent(item.image) + '" alt="' + escapeHtml(item.name) + '">' +
                '<div><h3>' + escapeHtml(item.name) + '</h3><p>' + escapeHtml(item.description) + '</p>' +
                '<div class="quantity-control"><button data-quantity="' + escapeHtml(item.id) + '" data-value="' + (item.quantity - 1) + '" aria-label="Remove one">−</button><span>' + item.quantity + '</span><button data-quantity="' + escapeHtml(item.id) + '" data-value="' + (item.quantity + 1) + '" aria-label="Add one">+</button></div></div>' +
                '<div class="cart-item-price"><b>' + money.format(item.lineTotal) + '</b><button class="remove-item" data-remove="' + escapeHtml(item.id) + '">Remove</button></div></article>';
        }).join("");
    }

    function renderAccount() {
        if (!state.user) return;
        document.getElementById("account-welcome").textContent = "Hello, " + state.user.name.split(/\s+/)[0];
        document.getElementById("account-avatar-initial").textContent = (state.user.name.trim()[0] || "✿").toUpperCase();
        var avatarImage = document.getElementById("account-avatar-image");
        avatarImage.classList.toggle("hidden", !state.user.profileImage);
        document.getElementById("account-avatar-initial").classList.toggle("hidden", !!state.user.profileImage);
        if (state.user.profileImage) avatarImage.src = state.user.profileImage;
        document.getElementById("profile-name").value = state.user.name;
        document.getElementById("profile-email").value = state.user.email;
        var orders = state.orders || [];
        document.getElementById("account-order-count").textContent = orders.length
            ? "You have " + orders.length + (orders.length === 1 ? " order in your history." : " orders in your history.")
            : "No orders yet. Orders you place will appear here.";
        document.getElementById("order-history").innerHTML = orders.length
            ? '<h3>Your orders</h3>' + orders.map(function (order) {
                var date = new Date(order.createdAt);
                return '<div class="order-row"><div><b>' + escapeHtml(order.id) + '</b><small>' + date.toLocaleDateString() + ' · ' + order.items.length + (order.items.length === 1 ? ' bouquet' : ' bouquets') + ' · ' + escapeHtml(order.paymentMethod || 'Payment method not recorded') + '</small></div><div class="order-status">' + escapeHtml(order.status) + (order.paymentStatus ? ' · ' + escapeHtml(order.paymentStatus) : '') + ' · ' + money.format(order.total) + '</div></div>';
            }).join("")
            : "";
    }

    function showPage(nextPage) {
        page = nextPage;
        homeSections.forEach(function (id) { document.getElementById(id).classList.toggle("hidden", nextPage !== "home"); });
        document.getElementById("cart-view").classList.toggle("hidden", nextPage !== "cart");
        document.getElementById("account-view").classList.toggle("hidden", nextPage !== "account");
        document.querySelectorAll(".nav-link").forEach(function (link) {
            link.classList.toggle("active", (nextPage === "home" && link.getAttribute("href") === "#home") || (nextPage === "shop" && link.getAttribute("href") === "#shop") || (nextPage === "account" && link.getAttribute("href") === "#account-view"));
        });
        if (nextPage === "cart") renderCart();
        if (nextPage === "account") renderAccount();
        document.getElementById("main-nav").classList.remove("open");
        document.getElementById("mobile-menu-button").setAttribute("aria-expanded", "false");
        if (nextPage === "cart" || nextPage === "account") window.scrollTo({ top: 0, behavior: "smooth" });
    }

    function openModal(id) {
        var modal = document.getElementById(id);
        modal.classList.remove("hidden");
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
        var input = modal.querySelector("input:not([readonly])");
        if (input) setTimeout(function () { input.focus(); }, 80);
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        modal.classList.add("hidden");
        modal.setAttribute("aria-hidden", "true");
        if (document.querySelectorAll(".modal-backdrop:not(.hidden)").length === 0) document.body.style.overflow = "";
    }

    function openAuth(tab) {
        switchAuthTab(tab || "login");
        openModal("auth-modal");
    }

    function switchAuthTab(tab) {
        document.querySelectorAll(".auth-tab").forEach(function (button) { button.classList.toggle("active", button.dataset.authTab === tab); });
        document.getElementById("login-form").classList.toggle("hidden", tab !== "login");
        document.getElementById("register-form").classList.toggle("hidden", tab !== "register");
        var isRegister = tab === "register";
        document.getElementById("auth-title").innerHTML = isRegister ? "Create your <em>account.</em>" : "Sign in to <em>continue.</em>";
        document.getElementById("auth-eyebrow").textContent = isRegister ? "A few details to get started" : "Your account";
        document.getElementById("auth-subtitle").textContent = isRegister ? "Create an account to check out and keep track of your orders." : "Sign in to see your orders and manage your details.";
    }

    function formData(form) {
        return Object.fromEntries(new FormData(form).entries());
    }

    async function handleSignIn(action, form) {
        var button = form.querySelector("button[type=submit]");
        button.disabled = true;
        try {
            var result = await api(action, formData(form));
            if (action === "register") {
                var registeredEmail = form.elements.email.value;
                form.reset();
                closeModal("auth-modal");
                switchAuthTab("login");
                document.querySelector('#login-form [name="email"]').value = registeredEmail;
                openModal("auth-modal");
                await floralNotice("Account created", "Your account is ready. Sign in with your email and password to continue.", "Sign in");
                return;
            }
            state.user = result.user;
            state.cart = result.cart || { items: [], total: 0, count: 0 };
            state.orders = result.orders || [];
            setHeader();
            closeModal("auth-modal");
            form.reset();
            showWelcome(result.user, result.welcomeType);
            if (pendingProduct) {
                var product = pendingProduct;
                pendingProduct = null;
                await addProduct(product);
            } else if (page === "account") {
                renderAccount();
            }
        } catch (error) {
            notify(error.message);
        } finally {
            button.disabled = false;
        }
    }

    async function addProduct(productId) {
        if (!state.user) {
            pendingProduct = productId;
            openAuth("login");
            return;
        }
        try {
            var result = await api("add_to_cart", { productId: productId });
            state.cart = result.cart;
            setHeader();
            notify("Added to your cart.");
        } catch (error) {
            if (error.status === 401) {
                state.user = null;
                pendingProduct = productId;
                openAuth("login");
            } else { notify(error.message); }
        }
    }

    async function refreshState() {
        var result = await api("state");
        state.csrf = result.csrf;
        state.user = result.user;
        state.products = result.products;
        state.cart = result.cart;
        state.orders = result.orders;
        setHeader();
        renderProducts();
    }

    document.getElementById("year").textContent = new Date().getFullYear();
    document.getElementById("page-prev").addEventListener("click", function () { currentPage--; renderProducts(); document.getElementById("shop").scrollIntoView({ behavior: "smooth" }); });
    document.getElementById("page-next").addEventListener("click", function () { currentPage++; renderProducts(); document.getElementById("shop").scrollIntoView({ behavior: "smooth" }); });
    document.querySelectorAll(".filter-chip").forEach(function (button) {
        button.addEventListener("click", function () {
            category = button.dataset.category;
            currentPage = 1;
            document.querySelectorAll(".filter-chip").forEach(function (chip) { chip.classList.toggle("active", chip === button); });
            renderProducts();
        });
    });
    document.addEventListener("click", function (event) {
        var add = event.target.closest("[data-add]");
        if (add) { addProduct(add.dataset.add); return; }
        var quantity = event.target.closest("[data-quantity]");
        if (quantity) {
            var itemId = quantity.dataset.quantity;
            var nextQuantity = Number(quantity.dataset.value);
            api(nextQuantity < 1 ? "remove_from_cart" : "update_cart", { productId: itemId, quantity: nextQuantity }).then(function (result) { state.cart = result.cart; renderCart(); setHeader(); }).catch(function (error) { notify(error.message); });
            return;
        }
        var remove = event.target.closest("[data-remove]");
        if (remove) {
            api("remove_from_cart", { productId: remove.dataset.remove }).then(function (result) { state.cart = result.cart; renderCart(); setHeader(); }).catch(function (error) { notify(error.message); });
            return;
        }
        if (event.target.closest("[data-go-shop]")) {
            showPage("home");
            document.getElementById("shop").scrollIntoView({ behavior: "smooth" });
        }
    });

    document.getElementById("cart-shortcut").addEventListener("click", function () {
        if (!state.user) { openAuth("login"); notify("Sign in or create an account to add flowers to your cart."); return; }
        showPage("cart");
    });
    document.getElementById("account-shortcut").addEventListener("click", function () {
        if (!state.user) { openAuth("login"); return; }
        showPage("account");
    });
    document.getElementById("header-login").addEventListener("click", function () {
        if (state.user) showPage("account");
        else openAuth("login");
    });
    document.getElementById("footer-account").addEventListener("click", function () {
        if (state.user) showPage("account");
        else openAuth("login");
    });
    document.getElementById("logout-button").addEventListener("click", async function () {
        try {
            var result = await api("logout", {});
            state.csrf = result.csrf;
            state.user = null;
            state.cart = { items: [], total: 0, count: 0 };
            state.orders = [];
            setHeader();
            showPage("home");
            notify("You’ve signed out. Come back any time.");
        } catch (error) { notify(error.message); }
    });
    document.querySelectorAll(".nav-link").forEach(function (link) {
        link.addEventListener("click", function () {
            var href = link.getAttribute("href");
            if (href === "#account-view") {
                if (state.user) showPage("account");
                else openAuth("login");
            } else {
                showPage("home");
                if (href === "#home") window.scrollTo({ top: 0, behavior: "smooth" });
                else if (href === "#shop" || href === "#our-story" || href === "#journal") document.querySelector(href).scrollIntoView({ behavior: "smooth" });
            }
            document.getElementById("main-nav").classList.remove("open");
        });
    });

    document.getElementById("theme-toggle").addEventListener("click", function () {
        setTheme(document.documentElement.dataset.theme === "dark" ? "light" : "dark");
    });

    document.getElementById("mobile-menu-button").addEventListener("click", function () {
        var nav = document.getElementById("main-nav");
        nav.classList.toggle("open");
        this.setAttribute("aria-expanded", nav.classList.contains("open") ? "true" : "false");
    });
    document.querySelectorAll("[data-auth-tab]").forEach(function (button) { button.addEventListener("click", function () { switchAuthTab(button.dataset.authTab); }); });
    document.getElementById("login-form").addEventListener("submit", function (event) { event.preventDefault(); handleSignIn("login", this); });
    document.getElementById("register-form").addEventListener("submit", function (event) { event.preventDefault(); handleSignIn("register", this); });
    document.getElementById("forgot-link").addEventListener("click", function () { closeModal("auth-modal"); resetEmail = ""; document.getElementById("reset-request-form").reset(); document.getElementById("reset-code-note").classList.add("hidden"); document.getElementById("reset-complete-form").classList.add("hidden"); document.getElementById("reset-request-form").classList.remove("hidden"); openModal("reset-modal"); });
    document.getElementById("reset-back").addEventListener("click", function () { closeModal("reset-modal"); openAuth("login"); });
    document.getElementById("reset-request-form").addEventListener("submit", async function (event) {
        event.preventDefault();
        var data = formData(this);
        try {
            var result = await api("request_reset", data);
            resetEmail = data.email;
            var note = document.getElementById("reset-code-note");
            var deliveryNote = result.emailSent
                ? "We’ve emailed a reset code to " + escapeHtml(resetEmail) + ". For this demo, it’s also shown here."
                : "Your reset code for " + escapeHtml(resetEmail) + " is ready. Local email isn’t configured, so use the demo code shown here.";
            note.innerHTML = deliveryNote + "<strong>" + escapeHtml(result.demoCode) + "</strong>This code expires in 10 minutes.";
            note.classList.remove("hidden");
            document.getElementById("reset-complete-form").classList.remove("hidden");
        } catch (error) { notify(error.message); }
    });
    document.getElementById("reset-complete-form").addEventListener("submit", async function (event) {
        event.preventDefault();
        var data = formData(this);
        try {
            await api("reset_password", { email: resetEmail, code: data.code, password: data.password });
            closeModal("reset-modal");
            openAuth("login");
            floralNotice("Password updated", "Your new password is ready. Come on in whenever you’re ready.", "Back to sign in");
        } catch (error) { notify(error.message); }
    });

    document.querySelectorAll("[data-close-modal]").forEach(function (button) { button.addEventListener("click", function () { pendingProduct = null; closeModal("auth-modal"); }); });
    document.querySelectorAll("[data-close-reset]").forEach(function (button) { button.addEventListener("click", function () { closeModal("reset-modal"); }); });
    document.querySelectorAll("[data-close-checkout]").forEach(function (button) { button.addEventListener("click", function () { closeModal("checkout-modal"); }); });
    document.querySelectorAll(".modal-backdrop").forEach(function (modal) {
        modal.addEventListener("click", function (event) { if (event.target === modal) closeModal(modal.id); });
    });
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") ["auth-modal", "reset-modal", "checkout-modal"].forEach(function (id) { closeModal(id); });
    });

    document.getElementById("checkout-button").addEventListener("click", function () {
        if (!state.user) { openAuth("login"); return; }
        if (!state.cart.count) { notify("Your cart is empty."); return; }
        openModal("checkout-modal");
    });
    document.getElementById("checkout-form").addEventListener("submit", async function (event) {
        event.preventDefault();
        var button = this.querySelector("button[type=submit]");
        button.disabled = true;
        try {
            var result = await api("checkout", formData(this));
            state.cart = result.cart;
            state.orders.unshift(result.order);
            this.reset();
            closeModal("checkout-modal");
            renderCart();
            setHeader();
            await floralNotice("Order saved", "We’ve saved order " + escapeHtml(result.order.id) + " with " + escapeHtml(result.order.paymentMethod) + ". No payment was taken through this demo site.", "View account");
            showPage("account");
        } catch (error) {
            notify(error.message);
        } finally { button.disabled = false; }
    });
    document.getElementById("profile-form").addEventListener("submit", async function (event) {
        event.preventDefault();
        var values = formData(this);
        try {
            var result = await api("update_profile", { name: values.name, currentPassword: values.currentPassword, newPassword: values.newPassword, profileImage: selectedProfileImage, removeProfileImage: removeProfileImage });
            state.user = result.user;
            this.reset();
            selectedProfileImage = null;
            removeProfileImage = false;
            renderAccount();
            notify(result.message);
        } catch (error) { notify(error.message); }
    });
    document.getElementById("profile-photo-input").addEventListener("change", function () {
        var file = this.files && this.files[0];
        if (!file) return;
        if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size > 5 * 1024 * 1024) {
            this.value = "";
            notify("Choose a JPG, PNG, or WebP photo under 5 MB.");
            return;
        }
        var reader = new FileReader();
        reader.onload = function () {
            var image = new Image();
            image.onload = function () {
                var canvas = document.createElement("canvas");
                var scale = Math.min(1, 480 / Math.max(image.width, image.height));
                canvas.width = Math.max(1, Math.round(image.width * scale));
                canvas.height = Math.max(1, Math.round(image.height * scale));
                canvas.getContext("2d").drawImage(image, 0, 0, canvas.width, canvas.height);
                selectedProfileImage = canvas.toDataURL("image/jpeg", .78);
                removeProfileImage = false;
                var preview = document.getElementById("account-avatar-image");
                preview.src = selectedProfileImage;
                preview.classList.remove("hidden");
                document.getElementById("account-avatar-initial").classList.add("hidden");
                notify("Photo ready. Save your details to keep it on your account.");
            };
            image.onerror = function () { notify("That photo could not be opened. Try another image."); };
            image.src = String(reader.result);
        };
        reader.readAsDataURL(file);
    });
    document.getElementById("remove-profile-photo").addEventListener("click", function () {
        selectedProfileImage = null;
        removeProfileImage = true;
        document.getElementById("profile-photo-input").value = "";
        document.getElementById("account-avatar-image").classList.add("hidden");
        document.getElementById("account-avatar-initial").classList.remove("hidden");
        notify("Save your details to remove the current profile photo.");
    });
    document.getElementById("newsletter-form").addEventListener("submit", async function (event) {
        event.preventDefault();
        var form = this;
        var button = form.querySelector("button[type=submit]");
        button.disabled = true;
        try {
            var result = await api("newsletter_subscribe", { email: form.elements.email.value });
            form.reset();
            document.getElementById("newsletter-result").textContent = result.message;
            document.getElementById("newsletter-result").classList.remove("hidden");
            document.getElementById("newsletter-change").classList.remove("hidden");
            form.classList.add("hidden");
        } catch (error) {
            notify(error.message);
        } finally {
            button.disabled = false;
        }
    });
    document.getElementById("newsletter-change").addEventListener("click", function () {
        document.getElementById("newsletter-result").classList.add("hidden");
        this.classList.add("hidden");
        document.getElementById("newsletter-form").classList.remove("hidden");
        document.getElementById("newsletter-email").focus();
    });

    refreshState().catch(function (error) {
        notify(error.message);
        productGrid.innerHTML = '<div class="empty-products">We’re arranging the flower table. Please refresh in a moment.</div>';
    });
}());
