<?php require __DIR__ . '/members-slider.php'; ?>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <h5 class="site-footer-title">
                    <i class="bi bi-heart-fill text-danger"></i> <?= e(site_name()) ?>
                </h5>
                <?php if (site_tagline() !== ''): ?>
                <p class="site-footer-tagline"><?= e(site_tagline()) ?></p>
                <?php endif; ?>
                <?php if (site_footer_about() !== ''): ?>
                <p class="site-footer-about mb-0"><?= e(site_footer_about()) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-lg-4">
                <h6 class="site-footer-heading">تماس با ما</h6>
                <ul class="site-footer-contact list-unstyled mb-0">
                    <?php if (site_footer_phone() !== ''): ?>
                    <li>
                        <a href="tel:<?= e(preg_replace('/\D/', '', site_footer_phone())) ?>">
                            <i class="bi bi-phone"></i>
                            <?= e(site_footer_phone_display()) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (site_footer_email() !== ''): ?>
                    <li>
                        <a href="mailto:<?= e(site_footer_email()) ?>">
                            <i class="bi bi-envelope"></i>
                            <?= e(site_footer_email()) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-lg-3 text-lg-end">
                <p class="site-footer-copy mb-2">
                    &copy; <?= persian_digits((string) date('Y')) ?> <?= e(site_name()) ?>
                    <span class="d-block small opacity-75">تمامی حقوق محفوظ است</span>
                </p>
                <p class="site-footer-dev mb-0"><?= kh_dev_footer_credit() ?></p>
            </div>
        </div>
    </div>
</footer>
<?php include __DIR__ . '/donation-modal.php'; ?>
<script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<?php
$extraJs = ($extraJs ?? '') . '<script src="' . asset('js/donation.js') . '"></script>';
echo $extraJs;
?>
