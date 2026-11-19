
<!-- =====================================
     GREENOVA ASSISTANT CHATBOT
===================================== -->

<div class="greenova-chatbot" id="greenovaChatbot">

    <!-- Tombol chatbot -->
    <button type="button"
            class="chatbot-toggle"
            id="chatbotToggle"
            aria-label="Buka chatbot"
            aria-expanded="false">
        <span class="chatbot-toggle-icon">✦</span>
    </button>

    <!-- Jendela chatbot -->
    <div class="chatbot-window"
         id="chatbotWindow"
         role="dialog"
         aria-label="Greenova Assistant"
         hidden>

        <!-- Header -->
        <div class="chatbot-header">
            <div class="chatbot-avatar">✦</div>

            <div class="chatbot-header-info">
                <strong>Greenova Assistant</strong>
                <span>
                    <span class="chatbot-online-dot"></span>
                    Asisten belanja
                </span>
            </div>

            <button type="button"
                    class="chatbot-close"
                    id="chatbotClose"
                    aria-label="Tutup chatbot">
                &times;
            </button>
        </div>

        <!-- Percakapan -->
        <div class="chatbot-messages"
             id="chatbotMessages"
             role="log"
             aria-live="polite">

            <div class="chatbot-message bot">
                Halo! Selamat datang di Greenova.
                Ada yang bisa kami bantu hari ini?
            </div>

        </div>

        <!-- Pilihan pertanyaan -->
        <div class="chatbot-suggestions"
             id="chatbotSuggestions">

            <button type="button" data-question="Cari produk">
                Cari Produk
            </button>

            <button type="button" data-question="Produk terlaris">
                Produk Terlaris
            </button>

            <button type="button" data-question="Rekomendasi termurah">
                Produk Termurah
            </button>

            <button type="button" data-question="Cara checkout">
                Cara Checkout
            </button>

            <button type="button" data-question="Status pesanan">
                Status Pesanan
            </button>

        </div>

        <!-- Input -->
        <form class="chatbot-input-area"
              id="chatbotForm">

            <input type="text"
                   id="chatbotInput"
                   placeholder="Tulis pertanyaan..."
                   aria-label="Pesan untuk chatbot"
                   maxlength="300"
                   autocomplete="off"
                   required>

            <button type="submit"
                    aria-label="Kirim pesan">
                ➤
            </button>

        </form>

        <div class="chatbot-footer">
            Greenova • Sustainable Lifestyle
        </div>
    </div>
</div>

<style>
/* =====================================
   GREENOVA CHATBOT
===================================== */

.greenova-chatbot {
    position: fixed;
    right: 25px;
    bottom: 25px;
    z-index: 9999;
    font-family: var(--gn-font-sans, 'Inter', sans-serif);
}

/* Tombol mengambang */

.chatbot-toggle {
    width: 58px;
    height: 58px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 50%;
    background: #166534;
    color: white;
    cursor: pointer;
    box-shadow: 0 6px 22px rgba(22, 101, 52, 0.25);
    transition: 0.2s ease;
}

.chatbot-toggle:hover {
    background: #14532d;
    transform: translateY(-3px);
}

.chatbot-toggle-icon {
    font-size: 27px;
}

/* Jendela chat */

.chatbot-window {
    position: absolute;
    bottom: 75px;
    right: 0;
    width: 355px;
    height: 490px;
    max-height: calc(100dvh - 110px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #ffffff;
    border: 1px solid #e5eee5;
    border-radius: 18px;
    box-shadow: 0 12px 45px rgba(0, 0, 0, 0.15);
}

.chatbot-window[hidden] {
    display: none;
}

/* Header */

.chatbot-header {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 17px;
    background: #166534;
    color: white;
}

.chatbot-avatar {
    width: 39px;
    height: 39px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.17);
    border-radius: 50%;
    font-size: 22px;
}

.chatbot-header-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
}

.chatbot-header-info strong {
    font-size: 13px;
    font-weight: 600;
}

.chatbot-header-info > span {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #dcfce7;
    font-size: 10px;
}

.chatbot-online-dot {
    width: 7px;
    height: 7px;
    background: #bbf7d0;
    border-radius: 50%;
}

.chatbot-close {
    padding: 0 5px;
    border: none;
    background: transparent;
    color: white;
    font-size: 25px;
    cursor: pointer;
}

/* Area percakapan */

.chatbot-messages {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
    padding: 18px 15px;
    background: #fafcf9;
}

.chatbot-message {
    max-width: 87%;
    padding: 11px 13px;
    font-size: 11px;
    line-height: 1.8;
    overflow-wrap: anywhere;
}

.chatbot-message.bot {
    align-self: flex-start;
    background: #edf5ed;
    color: #25482d;
    border-radius: 13px 13px 13px 3px;
}

.chatbot-message.user {
    align-self: flex-end;
    background: #166534;
    color: #ffffff;
    border-radius: 13px 13px 3px 13px;
}

/* Link rekomendasi */

.chatbot-message-link {
    display: inline-block;
    margin-top: 8px;
    color: #166534;
    font-weight: 600;
    text-decoration: underline;
}

.chatbot-message-link:hover {
    color: #14532d;
}

/* Pilihan cepat */

.chatbot-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    padding: 10px 14px;
    border-top: 1px solid #edf0ed;
    background: #ffffff;
}

.chatbot-suggestions button {
    padding: 7px 10px;
    border: 1px solid #c7e0ca;
    border-radius: 20px;
    background: #ffffff;
    color: #166534;
    font-size: 10px;
    font-family: inherit;
    cursor: pointer;
    transition: 0.2s;
}

.chatbot-suggestions button:hover {
    background: #edf5ed;
}

/* Form pesan */

.chatbot-input-area {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 11px 13px;
    background: #ffffff;
    border-top: 1px solid #edf0ed;
}

.chatbot-input-area input {
    flex: 1;
    min-width: 0;
    height: 39px;
    padding: 0 13px;
    border: 1px solid #e0e9e0;
    border-radius: 22px;
    outline: none;
    background: #fafcf9;
    color: #17233b;
    font-family: inherit;
    font-size: 11px;
}

.chatbot-input-area input:focus {
    border-color: #166534;
}

.chatbot-input-area button {
    width: 39px;
    height: 39px;
    flex-shrink: 0;
    border: none;
    border-radius: 50%;
    background: #166534;
    color: white;
    cursor: pointer;
    font-size: 16px;
}
.chatbot-message {
    white-space: pre-line;
}

.chatbot-input-area button:hover {
    background: #14532d;
}

.chatbot-footer {
    padding: 7px;
    text-align: center;
    color: #a1a9a1;
    background: #ffffff;
    font-size: 9px;
}

/* Responsive */

@media (max-width: 480px) {
    .greenova-chatbot {
        right: 15px;
        bottom: 15px;
    }

    .chatbot-window {
        width: min(355px, calc(100vw - 30px));
        height: min(490px, calc(100dvh - 110px));
        right: 0;
    }

    .chatbot-toggle {
        width: 53px;
        height: 53px;
    }
}

/* =====================================
   GREENOVA CHATBOT - PRODUCT CARDS
===================================== */

.chatbot-product-card {
    display: flex;
    gap: 10px;
    width: 100%;
    padding: 10px;
    background: #ffffff;
    border: 1px solid #e2eae1;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03);
}

.chatbot-product-image {
    width: 72px;
    height: 76px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 9px;
    background: #f3f6f0;
}

.chatbot-product-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.chatbot-product-placeholder {
    color: #66806b;
    font-size: 24px;
}

.chatbot-product-info {
    display: flex;
    flex: 1;
    min-width: 0;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
}

.chatbot-product-name {
    margin: 0;
    color: #21452c;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.5;
}

.chatbot-product-price {
    color: #166534;
    font-size: 12px;
    font-weight: 700;
}

.chatbot-product-stock {
    color: #64756a;
    font-size: 10px;
}

.chatbot-product-stock.empty {
    color: #b42318;
}

.chatbot-product-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-top: 5px;
    padding: 6px 11px;
    border-radius: 7px;
    background: #166534;
    color: #ffffff !important;
    font-size: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s ease;
}

.chatbot-product-link:hover {
    background: #14532d;
}

.chatbot-products-list {
    display: flex;
    flex-direction: column;
    align-self: stretch;
    gap: 9px;
}

.chatbot-messages {
    scrollbar-width: thin;
    scrollbar-color: #c5d6c7 transparent;
}

</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggle = document.getElementById('chatbotToggle');
    const windowChat = document.getElementById('chatbotWindow');
    const close = document.getElementById('chatbotClose');
    const form = document.getElementById('chatbotForm');
    const input = document.getElementById('chatbotInput');
    const messages = document.getElementById('chatbotMessages');
    const suggestions = document.getElementById('chatbotSuggestions');

    const routes = {
        products: @json(route('products')),
        cart: @json(route('cart')),
        orders: @json(route('orders')),
        profile: @json(route('profile.edit')),
        chatbot: @json(route('chatbot.products'))
    };

    // =====================================
    // BUKA DAN TUTUP CHATBOT
    // =====================================

    function setChatOpen(open) {
        windowChat.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));

        if (open) {
            input.focus();
        }
    }

    toggle.addEventListener('click', function () {
        setChatOpen(windowChat.hidden);
    });

    close.addEventListener('click', function () {
        setChatOpen(false);
        toggle.focus();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !windowChat.hidden) {
            setChatOpen(false);
            toggle.focus();
        }
    });

    // =====================================
    // TAMPILKAN PESAN
    // =====================================

    function scrollBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function addMessage(text, sender, link = null) {
        const bubble = document.createElement('div');

        bubble.className = 'chatbot-message ' + sender;
        bubble.textContent = text;

        if (link) {
            const anchor = document.createElement('a');

            anchor.href = link.url;
            anchor.textContent = link.label;
            anchor.className = 'chatbot-message-link';

            bubble.appendChild(document.createElement('br'));
            bubble.appendChild(anchor);
        }

        messages.appendChild(bubble);
        scrollBottom();

        return bubble;
    }

    // =====================================
    // FORMAT DAN DETEKSI BUDGET
    // =====================================

    function formatRupiah(number) {
        return 'Rp ' + Number(number).toLocaleString('id-ID');
    }

    function getBudget(question) {
        const text = question.toLowerCase();

        const match = text.match(
            /(?:rp\s*)?(\d[\d.,]*)(?:\s*(ribu|rb|k|juta|jt))?/i
        );

        if (!match) return null;

        const raw = match[1];
        const unit = (match[2] || '').toLowerCase();

        let amount;

        if (unit) {
            amount = Number(raw.replace(',', '.'));

            if (unit === 'ribu' || unit === 'rb' || unit === 'k') {
                amount *= 1000;
            }

            if (unit === 'juta' || unit === 'jt') {
                amount *= 1000000;
            }
        } else {
            amount = Number(raw.replace(/[.,]/g, ''));
        }

        if (!Number.isFinite(amount) || amount < 0) {
            return null;
        }

        return Math.round(amount);
    }

    // =====================================
    // DETEKSI NAMA PRODUK
    // =====================================

    
function extractProductName(question) {

    let search = question.toLowerCase();

    // Hapus nominal harga beserta Rp dan satuannya
    search = search.replace(
        /\b(?:rp\s*)?\d[\d.,]*(?:\s*(?:ribu|rb|k|juta|jt))?\b/gi,
        ' '
    );

    // Hapus kata-kata instruksi yang bukan nama produk
    search = search.replace(
        /\b(rekomendasikan|rekomendasi|tolong|carikan|cari|tampilkan|lihat|produk|barang|katalog|harga|harganya|stok|stoknya|stock|berapa|apakah|masih|ada|tersedia|yang|di|bawah|dibawah|kurang|dari|maksimal|budget|dengan|sekitar|paling|murah|terjangkau|untuk|aku|saya|dong|nih|ya|greenova|dan|punya|saja|aja)\b/gi,
        ' '
    );

    search = search.replace(/[?!.,]/g, ' ');
    search = search.replace(/\s+/g, ' ').trim();

    return search;
}

    // =====================================
    // MEMBUAT KARTU PRODUK
    // =====================================

    function createProductCard(product) {

        const card = document.createElement('div');
        card.className = 'chatbot-product-card';

        const imageBox = document.createElement('div');
        imageBox.className = 'chatbot-product-image';

        if (product.image) {
            const img = document.createElement('img');

            img.src = product.image;
            img.alt = product.name;
            img.loading = 'lazy';

            imageBox.appendChild(img);
        } else {
            const placeholder = document.createElement('span');

            placeholder.className = 'chatbot-product-placeholder';
            placeholder.textContent = 'G';

            imageBox.appendChild(placeholder);
        }

        const info = document.createElement('div');
        info.className = 'chatbot-product-info';

        const name = document.createElement('h4');
        name.className = 'chatbot-product-name';
        name.textContent = product.name;

        const price = document.createElement('div');
        price.className = 'chatbot-product-price';
        if (product.has_discount && product.formatted_base_price) {
            price.innerHTML = `<span>${product.formatted_price}</span> <small style="text-decoration: line-through; color: #9CA3AF; font-size: 10px; margin-left: 4px;">${product.formatted_base_price}</small>`;
        } else {
            price.textContent = product.formatted_price;
        }

        const stock = document.createElement('div');
        stock.className = 'chatbot-product-stock';

        let extraMeta = '';
        if (product.rating && product.rating > 0) {
            extraMeta += ` • ★ ${product.rating}`;
        }
        if (product.sold_count && product.sold_count > 0) {
            extraMeta += ` • Terjual ${product.sold_count}`;
        }

        if (product.stock > 0) {
            stock.textContent = 'Stok: ' + product.stock + ' unit' + extraMeta;
        } else {
            stock.textContent = 'Stok habis' + extraMeta;
            stock.classList.add('empty');
        }

        const link = document.createElement('a');
        link.className = 'chatbot-product-link';
        link.href = product.url;
        link.textContent = 'Lihat Detail';

        info.appendChild(name);
        info.appendChild(price);
        info.appendChild(stock);
        info.appendChild(link);

        card.appendChild(imageBox);
        card.appendChild(info);

        return card;
    }

    // =====================================
    // AMBIL PRODUK DARI DATABASE
    // =====================================

    async function searchProducts(question) {

        const lower = question.toLowerCase();

        const hasBudgetWords =
            /harga|budget|di bawah|dibawah|maksimal|kurang dari|rp\s*\d|murah|terjangkau/i
                .test(lower);

        const budget = hasBudgetWords ? getBudget(question) : null;

        const availableOnly =
            /tersedia|ready|stok ada|masih ada|stoknya ada|belum habis/i
                .test(lower);

        const search = extractProductName(question);

        console.log('Pencarian produk:', {
            search: search,
            budget: budget,
            availableOnly: availableOnly
        });
        const loading = addMessage(
            'Aku sedang mencari produk di katalog Greenova...',
            'bot'
        );

        try {
            const url = new URL(
                routes.chatbot,
                window.location.origin
            );

            if (search) {
                url.searchParams.set('search', search);
            }

            if (budget !== null) {
                url.searchParams.set('max_price', budget);
            }

            if (availableOnly) {
                url.searchParams.set('available_only', '1');
            }

            if (/terlaris|laku|populer|best seller/i.test(lower)) {
                url.searchParams.set('sort', 'best_seller');
            } else if (/paling murah|termurah|terjangkau/i.test(lower)) {
                url.searchParams.set('sort', 'cheapest');
            } else if (/rating|terbaik|bintang/i.test(lower)) {
                url.searchParams.set('sort', 'top_rated');
            }

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Gagal mengambil katalog');
            }

            const data = await response.json();

            loading.remove();

            if (!data.products || data.products.length === 0) {
                addMessage(
                    'Maaf, belum ada produk yang cocok dengan kriteria tersebut. Coba gunakan nama produk lain atau ubah batas harganya.',
                    'bot',
                    {
                        label: 'Lihat Semua Produk',
                        url: routes.products
                    }
                );
                return;
            }

            let introduction = 'Berikut produk yang ditemukan untuk Anda:';

            if (budget !== null) {
                introduction =
                    'Berikut produk dengan harga maksimal ' +
                    formatRupiah(budget) + ':';
            }

            addMessage(introduction, 'bot');

            const list = document.createElement('div');
            list.className = 'chatbot-products-list';

            data.products.forEach(function (product) {
                list.appendChild(createProductCard(product));
            });

            messages.appendChild(list);
            scrollBottom();

        } catch (error) {
            loading.remove();

            addMessage(
                'Maaf, aku belum bisa mengambil informasi produk saat ini.',
                'bot',
                {
                    label: 'Buka Katalog',
                    url: routes.products
                }
            );
        }
    }

    // =====================================
    // PROSES PERTANYAAN
    // =====================================

    async function sendMessage(question) {

        const text = question.trim();

        if (!text) return;

        addMessage(text, 'user');

        const lower = text.toLowerCase();

        if (/halo|hai|hello|selamat pagi|selamat siang|selamat malam/i.test(lower)) {
            addMessage(
                'Halo! Saya Greenova Assistant. Saya dapat membantu mencari produk ramah lingkungan, rekomendasi harga, dan panduan belanja.',
                'bot'
            );
        }

        else if (/terima kasih|makasih|thanks/i.test(lower)) {
            addMessage(
                'Sama-sama! Senang bisa membantu Anda belanja ramah lingkungan.',
                'bot'
            );
        }

        else if (/pesanan|status|pengiriman|dikirim|diproses|pending/i.test(lower)) {
            addMessage(
                'Kamu bisa melihat riwayat dan status transaksi melalui halaman Pesanan.',
                'bot',
                {
                    label: 'Lihat Pesanan',
                    url: routes.orders
                }
            );
        }

        else if (/checkout|pembayaran|cara bayar|cara beli/i.test(lower)) {
            addMessage(
                'Buka Keranjang, pilih produk, lalu klik Checkout Produk Dipilih. Setelah itu, lengkapi informasi checkout.',
                'bot',
                {
                    label: 'Buka Keranjang',
                    url: routes.cart
                }
            );
        }

        else if (/keranjang|hapus produk|ubah jumlah/i.test(lower)) {
            addMessage(
                'Di Keranjang kamu bisa mengubah jumlah, menghapus produk, dan memilih produk untuk checkout.',
                'bot',
                {
                    label: 'Buka Keranjang',
                    url: routes.cart
                }
            );
        }

        else if (/profil|profile|akun|ubah alamat|ubah email|ubah nama/i.test(lower)) {
            addMessage(
                'Kamu bisa mengubah informasi akun melalui halaman Profil.',
                'bot',
                {
                    label: 'Buka Profil',
                    url: routes.profile
                }
            );
        }

        else if (
            /produk|barang|harga|stok|stock|katalog|tersedia|rekomendasi|rekomendasikan|carikan|cari|tampilkan|budget|murah|terjangkau|di bawah|dibawah/i
                .test(lower)
        ) {
            await searchProducts(text);
        }

        else {
            addMessage(
                'Aku bisa membantu mencari produk berdasarkan nama, harga, atau stok. Contohnya: "Rekomendasikan produk di bawah Rp150.000 yang masih tersedia."',
                'bot'
            );
        }

        input.focus();
    }

    // =====================================
    // FORM PESAN
    // =====================================

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const question = input.value;
        input.value = '';

        sendMessage(question);
    });

    // =====================================
    // PERTANYAAN CEPAT
    // =====================================

    suggestions.querySelectorAll('[data-question]')
        .forEach(function (button) {

            button.addEventListener('click', function () {
                sendMessage(button.dataset.question);
            });

        });

});
</script>

