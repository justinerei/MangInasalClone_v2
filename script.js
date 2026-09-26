document.addEventListener("DOMContentLoaded", function () {

    const CART_KEY = "mangInasalCart";
    const SHIPPING_FEE = 50;
    const SENIOR_DISCOUNT_RATE = 0.20;
    const SENIOR_MIN_AGE = 60;


    /* ---------------------------------------------------------------- */
    /* Cart storage (localStorage) — shared by every page                */
    /* ---------------------------------------------------------------- */

    function getCart() {
        try {
            const raw = localStorage.getItem(CART_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
        updateCartBadge();
    }

    function updateCartBadge() {
        const cart = getCart();
        const totalItems = cart.reduce(function (sum, item) {
            return sum + item.qty;
        }, 0);

        const badge = document.getElementById("cart-badge");
        if (badge) {
            badge.textContent = totalItems;
        }
    }

    function formatPeso(amount) {
        return "₱" + amount.toLocaleString("en-PH", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }


    /* ---------------------------------------------------------------- */
    /* Add to cart — menu pages (mainCourse.php, drinks.php, dessert.php) */
    /* ---------------------------------------------------------------- */

    function initAddToCartButtons() {
        const buttons = document.querySelectorAll(".add-to-cart-btn[data-product-id]");

        buttons.forEach(function (button) {
            button.addEventListener("click", function () {

                const card = button.closest(".menu-card");
                if (!card) return;

                const id = button.dataset.productId;
                const name = card.querySelector(".card-title").textContent.trim();
                const priceText = card.querySelector(".card-price").textContent;
                const price = parseFloat(priceText.replace(/[^\d.]/g, ""));
                const imageEl = card.querySelector("img");
                const image = imageEl ? imageEl.getAttribute("src") : "";

                addToCart({ id: id, name: name, price: price, image: image });

                const originalHTML = button.innerHTML;
                button.classList.add("btn-added");
                button.innerHTML = '<i class="fa-solid fa-check"></i> Added';

                setTimeout(function () {
                    button.classList.remove("btn-added");
                    button.innerHTML = originalHTML;
                }, 1000);
            });
        });
    }

    function addToCart(product) {
        const cart = getCart();
        const existing = cart.find(function (item) {
            return item.id === product.id;
        });

        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                qty: 1
            });
        }

        saveCart(cart);
    }


    /* ---------------------------------------------------------------- */
    /* Cart page (cart.php)                                              */
    /* ---------------------------------------------------------------- */

    const cartItemsContainer = document.getElementById("cart-items");

    if (cartItemsContainer) {
        initCartPage();
    }

    function initCartPage() {

        renderCart();

        document.getElementById("age-input").addEventListener("input", function () {
            const age = parseInt(this.value, 10);
            const checkbox = document.getElementById("senior-checkbox");

            if (age >= SENIOR_MIN_AGE) {
                checkbox.disabled = false;
            } else {
                checkbox.disabled = true;
                checkbox.checked = false;
            }

            updateSummary();
        });

        document.getElementById("senior-checkbox").addEventListener("change", updateSummary);
        document.getElementById("apply-discount-btn").addEventListener("click", updateSummary);

        document.getElementById("checkout-btn").addEventListener("click", openReceipt);
        document.getElementById("close-receipt").addEventListener("click", closeReceipt);
        document.getElementById("finalize-order").addEventListener("click", finalizeOrder);
    }

    function renderCart() {
        const cart = getCart();
        const container = document.getElementById("cart-items");
        const emptyMessage = document.getElementById("empty-cart-message");

        container.innerHTML = "";

        if (cart.length === 0) {
            emptyMessage.style.display = "block";
        } else {
            emptyMessage.style.display = "none";

            cart.forEach(function (item) {
                container.appendChild(buildCartRow(item));
            });

            attachRowEvents();
        }

        updateSummary();
    }

    function buildCartRow(item) {
        const row = document.createElement("div");
        row.className = "cart-item";
        row.dataset.productId = item.id;

        const subtotal = item.price * item.qty;

        row.innerHTML =
            '<button class="remove-btn" type="button" title="Remove item">' +
                '<i class="fa-solid fa-xmark"></i>' +
            '</button>' +

            '<img src="' + item.image + '" alt="' + item.name + '">' +

            '<div class="item-name">' + item.name + '</div>' +

            '<div class="item-price">' + formatPeso(item.price) + '</div>' +

            '<div class="qty-selector">' +
                '<button class="qty-btn qty-decrease" type="button">-</button>' +
                '<input class="qty-input" type="number" min="1" value="' + item.qty + '">' +
                '<button class="qty-btn qty-increase" type="button">+</button>' +
            '</div>' +

            '<div class="item-subtotal">' + formatPeso(subtotal) + '</div>';

        return row;
    }

    function attachRowEvents() {
        document.querySelectorAll(".cart-item").forEach(function (row) {
            const id = row.dataset.productId;

            row.querySelector(".remove-btn").addEventListener("click", function () {
                removeFromCart(id);
            });

            row.querySelector(".qty-decrease").addEventListener("click", function () {
                changeQty(id, -1);
            });

            row.querySelector(".qty-increase").addEventListener("click", function () {
                changeQty(id, 1);
            });

            row.querySelector(".qty-input").addEventListener("change", function () {
                const value = parseInt(this.value, 10);
                setQty(id, value > 0 ? value : 1);
            });
        });
    }

    function removeFromCart(id) {
        let cart = getCart();
        cart = cart.filter(function (item) {
            return item.id !== id;
        });
        saveCart(cart);
        renderCart();
    }

    function changeQty(id, delta) {
        const cart = getCart();
        const item = cart.find(function (i) { return i.id === id; });
        if (!item) return;

        item.qty = Math.max(1, item.qty + delta);
        saveCart(cart);
        renderCart();
    }

    function setQty(id, qty) {
        const cart = getCart();
        const item = cart.find(function (i) { return i.id === id; });
        if (!item) return;

        item.qty = qty;
        saveCart(cart);
        renderCart();
    }

    function updateSummary() {
        const cart = getCart();

        const subtotal = cart.reduce(function (sum, item) {
            return sum + (item.price * item.qty);
        }, 0);

        const itemCount = cart.reduce(function (sum, item) {
            return sum + item.qty;
        }, 0);

        const checkbox = document.getElementById("senior-checkbox");
        const discountActive = checkbox && checkbox.checked && !checkbox.disabled;
        const discount = discountActive ? subtotal * SENIOR_DISCOUNT_RATE : 0;

        const shipping = cart.length > 0 ? SHIPPING_FEE : 0;
        const total = subtotal - discount + shipping;

        setText("subtotal", formatPeso(subtotal));
        setText("item-count", itemCount);
        setText("senior-discount", "-" + formatPeso(discount));
        setText("shipping", formatPeso(shipping));
        setText("total", formatPeso(total));
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }


    /* ---------------------------------------------------------------- */
    /* Receipt modal                                                     */
    /* ---------------------------------------------------------------- */

    function openReceipt() {
        const cart = getCart();
        if (cart.length === 0) return;

        const list = document.getElementById("receipt-items");
        list.innerHTML = "";

        cart.forEach(function (item) {
            const li = document.createElement("li");
            li.className = "receipt-item";
            li.innerHTML =
                '<span><span class="receipt-item-qty">' + item.qty + 'x</span>' + item.name + '</span>' +
                '<span>' + formatPeso(item.price * item.qty) + '</span>';
            list.appendChild(li);
        });

        const subtotal = cart.reduce(function (sum, item) {
            return sum + (item.price * item.qty);
        }, 0);

        const checkbox = document.getElementById("senior-checkbox");
        const discountActive = checkbox && checkbox.checked && !checkbox.disabled;
        const discount = discountActive ? subtotal * SENIOR_DISCOUNT_RATE : 0;
        const shipping = SHIPPING_FEE;
        const total = subtotal - discount + shipping;

        setText("receipt-subtotal", formatPeso(subtotal));
        setText("receipt-discount", "-" + formatPeso(discount));
        setText("receipt-shipping", formatPeso(shipping));
        setText("receipt-total", formatPeso(total));

        const dateEl = document.getElementById("receipt-date");
        if (dateEl) {
            dateEl.textContent = new Date().toLocaleString("en-PH");
        }

        document.getElementById("receipt-modal").classList.remove("hidden");
    }

    function closeReceipt() {
        document.getElementById("receipt-modal").classList.add("hidden");
    }

    function finalizeOrder() {
        saveCart([]);
        closeReceipt();
        renderCart();
    }


    /* ---------------------------------------------------------------- */
    /* Init — runs on every page                                        */
    /* ---------------------------------------------------------------- */

    updateCartBadge();
    initAddToCartButtons();

});