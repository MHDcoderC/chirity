<?php
$donation = donation_info();
if (!donation_is_configured()) {
    return;
}
$cardDisplay = format_card_number($donation['card_number']);
$cardRaw = normalize_card_number($donation['card_number']);
?>
<div class="modal fade donation-modal" id="donationModal" tabindex="-1" aria-labelledby="donationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered donation-modal-dialog">
        <div class="modal-content donation-modal-shell border-0 bg-transparent shadow-none">
            <div class="donation-glass-panel">
                <div class="donation-glass-border" aria-hidden="true"></div>
                <div class="donation-glass-glow" aria-hidden="true"></div>

                <header class="donation-glass-header">
                    <button type="button" class="donation-modal-close btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                    <div class="donation-glass-heading">
                        <p class="donation-glass-eyebrow">کمک مالی</p>
                        <h5 class="donation-glass-title" id="donationModalLabel">حمایت از خیریه</h5>
                        <p class="donation-glass-sub">شماره کارت را کپی کرده و واریز کنید</p>
                    </div>
                    <span class="donation-glass-header-spacer" aria-hidden="true"></span>
                </header>

                <div class="donation-glass-body">
                    <article class="donation-bank-card" aria-label="اطلاعات کارت بانکی">
                        <div class="donation-bank-card-shine" aria-hidden="true"></div>
                        <div class="donation-bank-card-noise" aria-hidden="true"></div>
                        <div class="donation-bank-card-top">
                            <span class="donation-chip" aria-hidden="true"></span>
                            <span class="donation-card-brand" aria-hidden="true">
                                <i class="bi bi-credit-card-2-fill"></i>
                            </span>
                        </div>
                        <p class="donation-bank-card-number" id="donationCardNumber" dir="ltr"><?= e($cardDisplay) ?></p>
                        <div class="donation-bank-card-footer">
                            <div class="donation-bank-card-field">
                                <span class="donation-bank-card-label">دارنده کارت</span>
                                <span class="donation-bank-card-value"><?= e($donation['card_holder'] !== '' ? $donation['card_holder'] : site_name()) ?></span>
                            </div>
                            <div class="donation-bank-card-field">
                                <span class="donation-bank-card-label">شبکه</span>
                                <span class="donation-bank-card-value">شتاب</span>
                            </div>
                        </div>
                    </article>

                    <button type="button" class="btn-donation-copy" id="donationCopyBtn" data-card="<?= e($cardRaw) ?>">
                        <span class="btn-donation-copy-icon"><i class="bi bi-copy"></i></span>
                        <span>کپی شماره کارت</span>
                    </button>
                    <p class="donation-copy-feedback d-none" id="donationCopyFeedback">
                        <i class="bi bi-check2-circle"></i> شماره کارت کپی شد
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
