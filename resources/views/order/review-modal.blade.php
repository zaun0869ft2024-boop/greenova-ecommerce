{{-- GREENOVA RATING & REVIEW MODAL --}}
<div id="greenovaReviewModal" class="gn-modal" aria-hidden="true" role="dialog" aria-labelledby="gnReviewModalTitle">
    <div class="gn-modal-backdrop" id="greenovaReviewBackdrop"></div>

    <div class="gn-modal-dialog">
        <div class="gn-modal-content">

            <!-- HEADER -->
            <div class="gn-modal-header">
                <div>
                    <span class="gn-modal-subtitle">PENILAIAN PESANAN</span>
                    <h3 id="gnReviewModalTitle" class="gn-modal-title">Beri Penilaian Produk</h3>
                </div>
                <button type="button" class="gn-modal-close" id="gnReviewModalClose" aria-label="Tutup modal">
                    &times;
                </button>
            </div>

            <!-- FORM -->
            <form id="gnReviewForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="gnReviewMethod" value="">
                <input type="hidden" name="order_id" id="gnReviewOrderId" value="">

                <div class="gn-modal-body">

                    <!-- PRODUK RINGKAS -->
                    <div class="gn-review-product">
                        <div class="gn-review-product-img">
                            <img id="gnReviewProductImg" src="" alt="Produk" onerror="this.onerror=null; this.src='https://placehold.co/120x120/eaf1e7/244b35?text=Greenova';">
                        </div>
                        <div class="gn-review-product-info">
                            <span class="gn-review-order-badge" id="gnReviewOrderBadge">Pesanan #</span>
                            <h4 id="gnReviewProductTitle" class="gn-review-product-name">Nama Produk</h4>
                            <p class="gn-review-product-caption">Beri penilaian berdasarkan pengalaman kamu menggunakan produk ini.</p>
                        </div>
                    </div>

                    <!-- STAR RATING -->
                    <div class="gn-rating-picker-group">
                        <label class="gn-rating-label">
                            Rating Produk <span class="gn-text-required">*</span>
                        </label>

                        <div class="gn-stars-wrapper" id="gnStarsWrapper" role="radiogroup" aria-label="Rating bintang 1 sampai 5">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button"
                                        class="gn-star-btn"
                                        data-value="{{ $i }}"
                                        role="radio"
                                        aria-checked="false"
                                        aria-label="{{ $i }} Bintang">
                                    <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor">
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                    </svg>
                                </button>
                            @endfor
                        </div>

                        <input type="hidden" name="rating" id="gnRatingInput" value="5" required>
                        <div class="gn-rating-hint" id="gnRatingHint">Pilih rating bintang</div>
                    </div>

                    <!-- KOMENTAR -->
                    <div class="gn-form-group">
                        <label for="gnCommentInput" class="gn-form-label">
                            Ulasan Anda <span class="gn-text-optional">(Opsional)</span>
                        </label>
                        <textarea
                            id="gnCommentInput"
                            name="comment"
                            rows="4"
                            maxlength="1000"
                            class="gn-form-textarea"
                            placeholder="Ceritakan pengalamanmu menggunakan produk ini, kondisi barang saat diterima, dan kesesuaian deskripsi..."></textarea>
                        <div class="gn-form-counter">
                            <span id="gnCharCount">0</span> / 1000 karakter
                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="gn-modal-footer">
                    <button type="button" class="gn-btn-secondary" id="gnReviewModalCancel">
                        Batal
                    </button>
                    <button type="submit" class="gn-btn-primary" id="gnReviewSubmitBtn">
                        Kirim Penilaian
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal          = document.getElementById('greenovaReviewModal');
    const backdrop       = document.getElementById('greenovaReviewBackdrop');
    const btnClose       = document.getElementById('gnReviewModalClose');
    const btnCancel      = document.getElementById('gnReviewModalCancel');
    const form           = document.getElementById('gnReviewForm');
    const methodInput    = document.getElementById('gnReviewMethod');
    const orderIdInput   = document.getElementById('gnReviewOrderId');
    const modalTitle     = document.getElementById('gnReviewModalTitle');
    const submitBtn      = document.getElementById('gnReviewSubmitBtn');
    const productImg     = document.getElementById('gnReviewProductImg');
    const productTitle   = document.getElementById('gnReviewProductTitle');
    const orderBadge     = document.getElementById('gnReviewOrderBadge');
    const ratingInput    = document.getElementById('gnRatingInput');
    const ratingHint     = document.getElementById('gnRatingHint');
    const starsWrapper   = document.getElementById('gnStarsWrapper');
    const commentInput   = document.getElementById('gnCommentInput');
    const charCount      = document.getElementById('gnCharCount');
    const starButtons    = starsWrapper ? starsWrapper.querySelectorAll('.gn-star-btn') : [];

    const ratingDescriptions = {
        1: '1 Bintang • Sangat Mengecewakan',
        2: '2 Bintang • Kurang Memuaskan',
        3: '3 Bintang • Cukup Baik',
        4: '4 Bintang • Memuaskan',
        5: '5 Bintang • Sangat Memuaskan'
    };

    function updateStars(val) {
        const rating = parseInt(val) || 0;
        starButtons.forEach(btn => {
            const btnVal = parseInt(btn.dataset.value);
            if (btnVal <= rating) {
                btn.classList.add('is-active');
                btn.setAttribute('aria-checked', 'true');
            } else {
                btn.classList.remove('is-active');
                btn.setAttribute('aria-checked', 'false');
            }
        });
        if (ratingHint) {
            ratingHint.textContent = ratingDescriptions[rating] || 'Pilih rating bintang';
        }
    }

    // Star click
    starButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const val = parseInt(this.dataset.value);
            ratingInput.value = val;
            updateStars(val);
        });

        // Hover effect
        btn.addEventListener('mouseenter', function () {
            const val = parseInt(this.dataset.value);
            updateStars(val);
        });
    });

    if (starsWrapper) {
        starsWrapper.addEventListener('mouseleave', function () {
            updateStars(ratingInput.value || 0);
        });
    }

    // Live character counter
    if (commentInput && charCount) {
        commentInput.addEventListener('input', function () {
            charCount.textContent = this.value.length;
        });
    }

    // Open modal
    function openReviewModal(btn) {
        const orderId      = btn.dataset.orderId || '';
        const isEdit       = btn.dataset.isEdit === '1';
        const rating       = parseInt(btn.dataset.rating) || 5;
        const comment      = btn.dataset.comment || '';
        const storeUrl     = btn.dataset.storeUrl || '';
        const updateUrl    = btn.dataset.updateUrl || '';
        const titleText    = btn.dataset.productTitle || 'Produk Greenova';
        const imageSrc     = btn.dataset.productImage || '';

        // Fill product info
        if (productTitle) productTitle.textContent = titleText;
        if (orderBadge)   orderBadge.textContent = 'Pesanan #' + orderId;
        if (productImg) {
            productImg.src = imageSrc || 'https://placehold.co/120x120/eaf1e7/244b35?text=Greenova';
            productImg.alt = titleText;
        }

        // Configure Form for Add or Edit
        if (isEdit) {
            form.action            = updateUrl;
            methodInput.value      = 'PATCH';
            modalTitle.textContent = 'Ubah Penilaian Produk';
            submitBtn.textContent  = 'Simpan Perubahan';
        } else {
            form.action            = storeUrl;
            methodInput.value      = '';
            modalTitle.textContent = 'Beri Penilaian Produk';
            submitBtn.textContent  = 'Kirim Penilaian';
        }

        orderIdInput.value = orderId;
        ratingInput.value  = rating;
        if (commentInput) {
            commentInput.value = comment;
            if (charCount) charCount.textContent = comment.length;
        }

        updateStars(rating);

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('gn-modal-locked');
    }

    // Close modal
    function closeReviewModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('gn-modal-locked');
    }

    if (btnClose)  btnClose.addEventListener('click', closeReviewModal);
    if (btnCancel) btnCancel.addEventListener('click', closeReviewModal);
    if (backdrop)  backdrop.addEventListener('click', closeReviewModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeReviewModal();
        }
    });

    // Delegate click for trigger buttons
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.btn-review-trigger');
        if (trigger) {
            e.preventDefault();
            openReviewModal(trigger);
        }
    });
});
</script>
