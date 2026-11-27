(function () {
    "use strict";

    var state = { csrf: "", user: null, products: [], cart: { items: [], total: 0, count: 0 }, orders: [], favorites: [], adminData: null };
    var page = "home";
    var category = "all";
    var currentPage = 1;
    var pageSize = 8;
    var pendingProduct = null;
    var resetEmail = "";
    var selectedProfileImage = null;
    var removeProfileImage = false;
    var toastTimer;
    var homeSections = ["home", "our-story", "journal"];
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

    async function sendApiRequest(action, data) {
        var options = {
            method: data ? "POST" : "GET",
            headers: { "Accept": "application/json" },
            credentials: "same-origin",
            cache: "no-store"
        };
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
        try {
            result = await response.json();
        } catch (error) {
            throw new Error("The shop couldn’t load that response. Refresh and try again.");
        }
        return { response: response, result: result };
    }

    async function api(action, data) {
        var request = await sendApiRequest(action, data);

        // A stale CSRF token can be left in an open tab after the PHP session
        // is renewed. Fetch a fresh state once, then retry the original POST once.
        if (data && action !== "state" && request.response.status === 419) {
            var refreshed = await sendApiRequest("state", null);
            if (!refreshed.response.ok || refreshed.result.ok === false || !refreshed.result.csrf) {
                var refreshError = new Error(refreshed.result.message || "Your session could not be refreshed. Please reload the page.");
                refreshError.status = refreshed.response.status;
                throw refreshError;
            }
            state.csrf = refreshed.result.csrf;
            state.user = refreshed.result.user || null;
            state.cart = refreshed.result.cart || { items: [], total: 0, count: 0 };
            state.orders = refreshed.result.orders || [];
            state.favorites = refreshed.result.favorites || [];
            setHeader();
            request = await sendApiRequest(action, data);
        }

        if (!request.response.ok || request.result.ok === false) {
            var requestError = new Error(request.result.message || "That didn’t work. Please try again.");
            requestError.status = request.response.status;
            throw requestError;
        }
        return request.result;
    }

    function notify(message) {
        var toast = document.getElementById("toast");
        toast.textContent = message;
        toast.classList.add("visible");
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toast.classList.remove("visible"); }, 2800);
    }

    function floralAlert(options) {
        var config = Object.assign({
            title: "",
            text: "",
            icon: "success",
            confirmButtonText: "Lovely",
            showCancelButton: false,
            cancelButtonText: "Not now",
            iconColor: null
        }, options || {});

        if (window.Swal && typeof window.Swal.fire === "function") {
            return window.Swal.fire({
                title: config.title,
                text: config.text,
                icon: config.icon,
                iconColor: config.iconColor || (
                    config.icon === "error" ? "#c43d45" :
                    config.icon === "warning" ? "#a87524" : "#2f8a4c"
                ),
                confirmButtonText: config.confirmButtonText,
                showCancelButton: Boolean(config.showCancelButton),
                cancelButtonText: config.cancelButtonText,
                customClass: {
                    popup: "floral-alert floral-alert-" + config.icon,
                    confirmButton: "floral-alert-confirm",
                    cancelButton: "floral-alert-cancel"
                },
                buttonsStyling: false,
                reverseButtons: true,
                focusConfirm: true,
                showClass: { popup: "floral-alert-show" },
                hideClass: { popup: "floral-alert-hide" }
            });
        }

        return new Promise(function (resolve) {
            var layer = document.createElement("div");
            layer.className = "modal-backdrop floral-fallback-backdrop floral-fallback-alert-" + config.icon;
            var symbol = config.icon === "error" ? "×" : (config.icon === "success" ? "✓" : "!");
            var iconClass = config.icon === "error"
                ? "floral-fallback-icon floral-fallback-error"
                : (config.icon === "warning" ? "floral-fallback-icon floral-fallback-warning" : "floral-fallback-icon floral-fallback-success");
            layer.innerHTML = '<section class="floral-fallback-card" role="dialog" aria-modal="true"><div class="' + iconClass + '" aria-hidden="true"></div><h2></h2><p></p><div class="floral-fallback-actions"><button type="button" data-confirm></button></div></section>';
            layer.querySelector(".floral-fallback-icon").textContent = symbol;
            layer.querySelector("h2").textContent = config.title;
            layer.querySelector("p").textContent = config.text;
            var confirmButton = layer.querySelector("[data-confirm]");
            confirmButton.textContent = config.confirmButtonText;
            confirmButton.classList.add("floral-fallback-confirm");
            if (config.showCancelButton) {
                var cancel = document.createElement("button");
                cancel.type = "button";
                cancel.className = "floral-fallback-cancel";
                cancel.textContent = config.cancelButtonText;
                cancel.setAttribute("data-cancel", "");
                layer.querySelector(".floral-fallback-actions").appendChild(cancel);
            }
            function finish(confirmed) {
                layer.remove();
                resolve({ isConfirmed: confirmed, isDismissed: !confirmed });
            }
            layer.querySelector("[data-confirm]").addEventListener("click", function () { finish(true); });
            var cancelButton = layer.querySelector("[data-cancel]");
            if (cancelButton) cancelButton.addEventListener("click", function () { finish(false); });
            layer.addEventListener("click", function (event) { if (event.target === layer) finish(false); });
            document.body.appendChild(layer);
            layer.querySelector("[data-confirm]").focus();
        });
    }

    function floralNotice(title, message, confirmText) {
        return floralAlert({
            title: title,
            text: message,
            icon: "success",
            iconColor: "#2f8a4c",
            confirmButtonText: confirmText || "Lovely"
        });
    }

    async function askToSignIn(message, productId) {
        pendingProduct = productId || null;
        var result = await floralAlert({
            title: "Please sign in first",
            text: message || "Sign in to continue with your flowers.",
            icon: "error",
            iconColor: "#c43d45",
            confirmButtonText: "Sign in",
            showCancelButton: true,
            cancelButtonText: "Not now"
        });
        if (result && result.isConfirmed) {
            openAuth("login");
        } else {
            pendingProduct = null;
        }
    }

    function showWelcome(user, type) {
        var first = type !== "back";
        var title = first ? "Welcome" : "Welcome back";
        var name = user && user.name ? user.name.split(/\s+/)[0] : "flower friend";
        var message = first
            ? "Glad you found us, " + name + ". Have a look around."
            : "Good to see you again, " + name + ". What can we help you find today?";
        floralNotice(title, message, "Have a look");
    }

    function updateHomeAccess() {
        var onHomePage = page === "home";
        homeSections.forEach(function (id) {
            document.getElementById(id).classList.toggle("hidden", !onHomePage);
        });

        var canViewMemberSections = Boolean(state.user) && onHomePage;
        document.getElementById("shop").classList.toggle("hidden", !canViewMemberSections);
        document.getElementById("studio-letters").classList.toggle("hidden", !canViewMemberSections);

        // Disable every link to the protected Shop section while signed out.
        document.querySelectorAll('a[href="#shop"]').forEach(function (link) {
            var disabled = !state.user;
            link.classList.toggle("shop-link-disabled", disabled);
            if (disabled) {
                link.setAttribute("aria-disabled", "true");
                link.tabIndex = -1;
            } else {
                link.removeAttribute("aria-disabled");
                link.removeAttribute("tabindex");
            }
        });
    }

    function setHeader() {
        var login = document.getElementById("header-login");
        login.textContent = state.user ? "My account" : "Sign in";
        var cartCount = Number(state.cart.count || 0);
        document.getElementById("cart-count").textContent = cartCount;
        document.getElementById("cart-shortcut").setAttribute("aria-label", "My Cart, " + cartCount + (cartCount === 1 ? " item" : " items"));
        var savedCount = state.favorites.length;
        document.getElementById("wishlist-count").textContent = savedCount;
        document.getElementById("wishlist-shortcut").setAttribute("aria-label", "Saved flowers, " + savedCount + (savedCount === 1 ? " item" : " items"));
        document.getElementById("admin-nav-link").classList.toggle("hidden", !(state.user && state.user.isAdmin));
        updateHomeAccess();
    }

    function productCardMarkup(product, index) {
        var detailsReviews = product.reviews || [];
        var stock = Number(product.stock == null ? 12 : product.stock);
        var saved = state.favorites.indexOf(product.id) !== -1;
        var outOfStock = stock <= 0;
        var stockText = outOfStock ? "Sold out" : (stock <= 3 ? "Only " + stock + " left" : "In stock");
        return '<article class="product-card" style="animation-delay:' + (index * 35) + 'ms">' +
            '<div class="product-photo"><span class="product-tag">' + escapeHtml(product.tag) + '</span>' +
            '<img src="assets/images/' + encodeURIComponent(product.image) + '" alt="' + escapeHtml(product.name) + ' bouquet" loading="lazy" onerror="this.style.opacity=0.15;this.alt=\'Flower photo loading soon\'">' +
            '<button class="product-photo-link" data-product-details="' + escapeHtml(product.id) + '" aria-label="See ' + escapeHtml(product.name) + ' ratings and customer notes"></button>' +
            '<button class="save-flower-button ' + (saved ? "saved" : "") + '" type="button" data-favorite="' + escapeHtml(product.id) + '" aria-pressed="' + (saved ? "true" : "false") + '" aria-label="' + (saved ? "Remove " : "Save ") + escapeHtml(product.name) + (saved ? " from saved flowers" : " to saved flowers") + '">' + (saved ? "♥" : "♡") + '</button></div>' +
            '<div class="product-card-top"><h3>' + escapeHtml(product.name) + '</h3><span class="product-price">' + money.format(product.price) + '</span></div>' +
            '<button class="rating-summary" data-product-details="' + escapeHtml(product.id) + '" aria-label="Rated ' + Number(product.rating || 0).toFixed(1) + ' out of 5. Read sample customer notes">' +
            '<span class="rating-stars" aria-hidden="true">★★★★★</span><b>' + Number(product.rating || 0).toFixed(1) + '</b><span>· ' + detailsReviews.length + ' sample notes</span></button>' +
            '<p class="product-description">' + escapeHtml(product.description) + '</p>' +
            '<div class="product-card-footer"><small class="product-stock ' + (outOfStock ? "sold-out" : (stock <= 3 ? "low-stock" : "")) + '">' + stockText + '</small>' +
            '<button class="add-button" data-add="' + escapeHtml(product.id) + '"' + (outOfStock ? " disabled" : "") + '>' + (outOfStock ? "Sold out" : "Add to bag") + ' <span>+</span></button></div></article>';
    }

    function filteredProducts() {
        var search = (document.getElementById("product-search").value || "").trim().toLowerCase();
        var sort = document.getElementById("product-sort").value;
        var filtered = state.products.filter(function (product) {
            var categoryMatch = category === "all" || product.category === category;
            var searchable = [product.name, product.category, product.description, product.tag].join(" ").toLowerCase();
            return categoryMatch && (!search || searchable.indexOf(search) !== -1);
        });
        filtered.sort(function (a, b) {
            if (sort === "price-low") return Number(a.price) - Number(b.price);
            if (sort === "price-high") return Number(b.price) - Number(a.price);
            if (sort === "name") return a.name.localeCompare(b.name);
            if (sort === "rating") return Number(b.rating || 0) - Number(a.rating || 0);
            return 0;
        });
        return filtered;
    }

    function renderProducts() {
        var filtered = filteredProducts();
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
            productGrid.innerHTML = '<div class="empty-products">No flowers match that search. Try a different name or category.</div>';
            return;
        }
        productGrid.innerHTML = items.map(productCardMarkup).join("");
    }

    function renderWishlist() {
        var root = document.getElementById("wishlist-grid");
        var saved = state.products.filter(function (product) { return state.favorites.indexOf(product.id) !== -1; });
        document.getElementById("wishlist-count-label").textContent = saved.length + (saved.length === 1 ? " saved bouquet" : " saved bouquets");
        if (!saved.length) {
            root.innerHTML = '<div class="empty-products wishlist-empty"><span>♡</span><h3>No saved flowers yet.</h3><p>Tap the heart on a bouquet to keep it here for later.</p><button class="button button-dark" data-go-shop>Browse flowers <span>→</span></button></div>';
            return;
        }
        root.innerHTML = saved.map(productCardMarkup).join("");
    }

    async function toggleFavorite(productId) {
        if (!state.user) {
            await askToSignIn("Please sign in first to save your favourite flowers.", productId);
            return;
        }
        try {
            var result = await api("toggle_favorite", { productId: productId });
            state.favorites = result.favorites || [];
            setHeader();
            renderProducts();
            if (page === "wishlist") renderWishlist();
            notify(state.favorites.indexOf(productId) !== -1 ? "Added to your saved flowers." : "Removed from saved flowers.");
        } catch (error) {
            notify(error.message);
        }
    }

    function openProductDetails(productId) {
        var product = state.products.find(function (item) { return item.id === productId; });
        if (!product) return;
        var reviews = product.reviews || [];
        document.getElementById("reviews-title").textContent = product.name;
        document.getElementById("reviews-overview").innerHTML =
            '<strong>' + Number(product.rating || 0).toFixed(1) + '</strong><span class="rating-stars" aria-label="out of 5 stars">★★★★★</span>' +
            '<small>Based on ' + reviews.length + ' sample customer notes</small>';
        document.getElementById("product-review-list").innerHTML = reviews.map(function (review) {
            var rating = Math.max(0, Math.min(5, Number(review.rating) || 0));
            return '<article class="customer-review"><div class="customer-review-heading"><b>' + escapeHtml(review.name) + '</b><span class="rating-stars" aria-label="' + rating + ' out of 5 stars">' + '★'.repeat(rating) + '<span class="empty-stars">' + '☆'.repeat(5 - rating) + '</span></span></div><p>' + escapeHtml(review.comment) + '</p></article>';
        }).join("");
        openModal("reviews-modal");
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

    function orderTimelineMarkup(order) {
        var status = String(order.status || "Received");
        if (status === "Cancelled") {
            return '<div class="order-tracking cancelled"><b>Order cancelled</b><span>This order will not be prepared for delivery.</span></div>';
        }
        if (status === "Awaiting manual payment") {
            return '<div class="order-tracking waiting"><b>Awaiting manual payment</b><span>The shop will confirm your GCash transfer before preparing the flowers.</span></div>';
        }
        var steps = ["Received", "Confirmed", "Preparing", "Out for delivery", "Delivered"];
        var current = Math.max(0, steps.indexOf(status));
        return '<div class="order-progress" aria-label="Order progress">' + steps.map(function (step, index) {
            return '<div class="order-progress-step ' + (index < current ? "complete" : (index === current ? "current" : "")) + '"><span>' + (index < current ? "✓" : (index + 1)) + '</span><small>' + step + '</small></div>';
        }).join("") + '</div>';
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
                var history = order.statusHistory || [];
                var lineItems = (order.items || []).map(function (item) {
                    return '<li>' + escapeHtml(item.name) + ' × ' + Number(item.quantity || 1) + '</li>';
                }).join("");
                var updates = history.slice(-5).map(function (entry) {
                    return '<li><b>' + escapeHtml(entry.status) + '</b><small>' + new Date(entry.at).toLocaleString() + '</small></li>';
                }).join("");
                return '<article class="customer-order-card"><div class="customer-order-top"><div><b>' + escapeHtml(order.id) + '</b><small>' + date.toLocaleDateString() + ' · ' + (order.items || []).length + ((order.items || []).length === 1 ? ' bouquet' : ' bouquets') + '</small></div><div class="customer-order-total">' + money.format(order.total) + '</div></div>' +
                    '<div class="customer-order-status"><span>Fulfilment</span><b>' + escapeHtml(order.status || "Received") + '</b></div>' + orderTimelineMarkup(order) +
                    '<div class="customer-order-payment">' + escapeHtml(order.paymentMethod || "Payment method not recorded") + ' · ' + escapeHtml(order.paymentStatus || "Payment status pending") + '</div>' +
                    '<details class="customer-order-details"><summary>Delivery details and updates</summary><div class="customer-order-detail-grid"><div><small>Recipient</small><b>' + escapeHtml(order.recipient || "—") + '</b></div><div><small>Contact</small><b>' + escapeHtml(order.phone || "—") + '</b></div><div class="wide"><small>Delivery address</small><b>' + escapeHtml(order.address || "—") + '</b></div><div class="wide"><small>Items</small><ul>' + lineItems + '</ul></div><div class="wide"><small>Order updates</small><ol>' + (updates || '<li>Order placed</li>') + '</ol></div></div></details></article>';
            }).join("")
            : '<div class="order-empty"><span>✿</span><b>Your next lovely delivery starts here.</b><p>Orders you place will appear here with their fulfilment progress.</p></div>';
    }

    function allowedNextStatuses(status) {
        var transitions = {
            "Awaiting manual payment": ["Confirmed", "Cancelled"],
            "Received": ["Confirmed", "Preparing", "Cancelled"],
            "Confirmed": ["Preparing", "Cancelled"],
            "Preparing": ["Out for delivery", "Cancelled"],
            "Out for delivery": ["Delivered"]
        };
        return transitions[status] || [];
    }

    function renderAdmin() {
        if (!state.adminData) return;
        var data = state.adminData;
        var summary = data.summary || {};
        var root = document.getElementById("admin-content");
        var stats = [
            ["Orders", summary.orders || 0],
            ["Open orders", summary.openOrders || 0],
            ["Delivered", summary.deliveredOrders || 0],
            ["Order value", money.format(summary.orderValueTotal || 0)],
            ["Low-stock flowers", summary.lowStock || 0],
            ["Customers", summary.customers || 0]
        ];
        var statsHtml = '<div class="admin-stat-grid">' + stats.map(function (entry) {
            return '<article class="admin-stat"><small>' + entry[0] + '</small><b>' + entry[1] + '</b></article>';
        }).join("") + '</div>';
        var orders = data.orders || [];
        var orderHtml = orders.length ? orders.map(function (order) {
            var options = [order.status].concat(allowedNextStatuses(order.status));
            return '<article class="admin-order-card"><div class="admin-order-info"><b>' + escapeHtml(order.id) + '</b><span>' + escapeHtml(order.customerName) + ' · ' + escapeHtml(order.customerEmail) + '</span><small>' + new Date(order.createdAt).toLocaleString() + ' · ' + money.format(order.total) + '</small><small>' + escapeHtml(order.recipient || "") + ' · ' + escapeHtml(order.address || "") + '</small></div><div class="admin-order-controls"><label>Order status<select data-admin-order-status="' + escapeHtml(order.id) + '">' + options.map(function (status) { return '<option value="' + escapeHtml(status) + '"' + (status === order.status ? " selected" : "") + '>' + escapeHtml(status) + '</option>'; }).join("") + '</select></label><button class="button button-dark admin-save-button" data-save-order="' + escapeHtml(order.id) + '">Save status</button></div></article>';
        }).join("") : '<p class="admin-empty">No orders have been placed yet.</p>';
        var products = data.products || [];
        var productHtml = products.map(function (product) {
            return '<article class="admin-stock-row"><div><b>' + escapeHtml(product.name) + '</b><small>' + escapeHtml(product.category) + ' · ' + money.format(product.price) + '</small></div><label>Units in stock<input type="number" min="0" max="5000" step="1" value="' + Number(product.stock) + '" data-admin-stock="' + escapeHtml(product.id) + '"></label><button class="button button-outline admin-save-button" data-save-stock="' + escapeHtml(product.id) + '">Update</button></article>';
        }).join("");
        root.innerHTML = statsHtml + '<div class="admin-panel-grid"><section class="admin-panel"><div class="admin-panel-heading"><div><span class="eyebrow eyebrow-light">Order fulfilment</span><h3>Recent orders</h3></div><span>' + orders.length + ' shown</span></div>' + orderHtml + '</section><section class="admin-panel"><div class="admin-panel-heading"><div><span class="eyebrow eyebrow-light">Stock control</span><h3>Flower inventory</h3></div><span>Updates apply at checkout</span></div>' + productHtml + '</section></div>';
    }

    async function loadAdmin() {
        if (!state.user || !state.user.isAdmin) return;
        try {
            state.adminData = await api("admin_dashboard", null);
            renderAdmin();
        } catch (error) {
            notify(error.message);
        }
    }

    function showPage(nextPage) {
        if (nextPage === "admin" && !(state.user && state.user.isAdmin)) {
            notify("The studio dashboard is for authorised staff only.");
            return;
        }
        if (nextPage === "wishlist" && !state.user) {
            askToSignIn("Please sign in to see your saved flowers.");
            return;
        }
        page = nextPage;
        updateHomeAccess();
        document.getElementById("cart-view").classList.toggle("hidden", nextPage !== "cart");
        document.getElementById("account-view").classList.toggle("hidden", nextPage !== "account");
        document.getElementById("wishlist-view").classList.toggle("hidden", nextPage !== "wishlist");
        document.getElementById("admin-view").classList.toggle("hidden", nextPage !== "admin");
        document.querySelectorAll(".nav-link").forEach(function (link) {
            link.classList.toggle("active", (nextPage === "home" && link.getAttribute("href") === "#home") || (nextPage === "shop" && link.getAttribute("href") === "#shop") || (nextPage === "account" && link.getAttribute("href") === "#account-view") || (nextPage === "admin" && link.getAttribute("href") === "#admin-view"));
        });
        if (nextPage === "cart") renderCart();
        if (nextPage === "account") renderAccount();
        if (nextPage === "wishlist") renderWishlist();
        if (nextPage === "admin") loadAdmin();
        document.getElementById("main-nav").classList.remove("open");
        document.getElementById("mobile-menu-button").setAttribute("aria-expanded", "false");
        if (nextPage !== "home") window.scrollTo({ top: 0, behavior: "smooth" });
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
            state.user = result.user;
            state.cart = result.cart || { items: [], total: 0, count: 0 };
            state.orders = result.orders || [];
            state.favorites = result.favorites || [];
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
            if (action === "register" && error.status === 409) {
                var existingEmail = form.elements.email.value;
                switchAuthTab("login");
                document.querySelector('#login-form [name="email"]').value = existingEmail;
                notify("An account with this email already exists. Sign in or use Forgot your password?");
            } else {
                notify(error.message);
            }
        } finally {
            button.disabled = false;
        }
    }

    async function addProduct(productId) {
        if (!state.user) {
            await askToSignIn("Please sign in first to add this flower to your cart.", productId);
            return;
        }
        try {
            var result = await api("add_to_cart", { productId: productId });
            state.cart = result.cart;
            setHeader();
            await floralNotice("Added to your cart", "Your flower has been added. You can keep browsing or view your cart.", "Lovely");
        } catch (error) {
            if (error.status === 401) {
                state.user = null;
                setHeader();
                await askToSignIn("Your session has expired. Please sign in first to add this flower to your cart.", productId);
            } else {
                await floralAlert({
                    title: "We couldn't add that flower",
                    text: error.message,
                    icon: "error",
                    iconColor: "#c43d45",
                    confirmButtonText: "Close"
                });
            }
        }
    }

    async function refreshState() {
        var catalogue = await api("catalog");
        state.products = catalogue.products || [];
        renderProducts();
        var result = await api("state");
        state.csrf = result.csrf;
        state.user = result.user;
        state.products = result.products;
        state.cart = result.cart;
        state.orders = result.orders;
        state.favorites = result.favorites || [];
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
    document.getElementById("product-search").addEventListener("input", function () {
        currentPage = 1;
        renderProducts();
    });
    document.getElementById("product-sort").addEventListener("change", function () {
        currentPage = 1;
        renderProducts();
    });
    document.addEventListener("click", function (event) {
        var favorite = event.target.closest("[data-favorite]");
        if (favorite) { toggleFavorite(favorite.dataset.favorite); return; }
        var saveOrder = event.target.closest("[data-save-order]");
        if (saveOrder) {
            var orderStatus = Array.from(document.querySelectorAll("[data-admin-order-status]")).find(function (control) { return control.dataset.adminOrderStatus === saveOrder.dataset.saveOrder; });
            if (orderStatus) {
                api("admin_update_order_status", { orderId: saveOrder.dataset.saveOrder, status: orderStatus.value }).then(function () {
                    return loadAdmin();
                }).then(function () {
                    floralNotice("Order updated", "The fulfilment status has been saved.", "Lovely");
                }).catch(function (error) { notify(error.message); });
            }
            return;
        }
        var saveStock = event.target.closest("[data-save-stock]");
        if (saveStock) {
            var stockInput = Array.from(document.querySelectorAll("[data-admin-stock]")).find(function (control) { return control.dataset.adminStock === saveStock.dataset.saveStock; });
            if (stockInput) {
                api("admin_update_inventory", { productId: saveStock.dataset.saveStock, stock: stockInput.value }).then(function () {
                    return refreshState().then(loadAdmin);
                }).then(function () {
                    floralNotice("Inventory saved", "The available quantity has been updated.", "Lovely");
                }).catch(function (error) { notify(error.message); });
            }
            return;
        }
        var details = event.target.closest("[data-product-details]");
        if (details) { openProductDetails(details.dataset.productDetails); return; }
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

    document.getElementById("wishlist-shortcut").addEventListener("click", function () {
        if (!state.user) {
            askToSignIn("Please sign in to see your saved flowers.");
            return;
        }
        showPage("wishlist");
    });
    document.getElementById("cart-shortcut").addEventListener("click", function () {
        if (!state.user) {
            askToSignIn("Please sign in first to view your cart.");
            return;
        }
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
            state.favorites = [];
            state.adminData = null;
            setHeader();
            showPage("home");
            notify("You’ve signed out. Come back any time.");
        } catch (error) { notify(error.message); }
    });
    document.querySelectorAll(".nav-link").forEach(function (link) {
        link.addEventListener("click", function (event) {
            var href = link.getAttribute("href");
            if (href === "#shop" && !state.user) {
                event.preventDefault();
                return;
            }
            if (href === "#account-view") {
                if (state.user) showPage("account");
                else openAuth("login");
            } else if (href === "#admin-view") {
                showPage("admin");
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
    document.querySelectorAll("[data-close-reviews]").forEach(function (button) { button.addEventListener("click", function () { closeModal("reviews-modal"); }); });
    document.querySelectorAll(".modal-backdrop").forEach(function (modal) {
        modal.addEventListener("click", function (event) { if (event.target === modal) closeModal(modal.id); });
    });
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") ["auth-modal", "reset-modal", "checkout-modal", "reviews-modal"].forEach(function (id) { closeModal(id); });
    });

    document.getElementById("checkout-button").addEventListener("click", function () {
        if (!state.user) {
            askToSignIn("Please sign in first to proceed to checkout.");
            return;
        }
        if (!state.cart.count) {
            floralAlert({
                title: "Your cart is empty",
                text: "Add a bouquet before proceeding to checkout.",
                icon: "warning",
                iconColor: "#b78037",
                confirmButtonText: "Keep browsing"
            });
            return;
        }
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
                var scale = Math.min(1, 768 / Math.max(image.width, image.height));
                canvas.width = Math.max(1, Math.round(image.width * scale));
                canvas.height = Math.max(1, Math.round(image.height * scale));
                var context = canvas.getContext("2d");
                context.imageSmoothingEnabled = true;
                context.imageSmoothingQuality = "high";
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                selectedProfileImage = canvas.toDataURL("image/jpeg", .9);
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
        if (!state.products.length) {
            productGrid.innerHTML = '<div class="empty-products">We’re arranging the flower table. Please refresh in a moment.</div>';
        }
    });
}());

