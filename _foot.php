
</main>

<footer class="footer">
    <div class="footer-container">
        <!-- Categories Section -->
        <div class="footer-section">
            <h3 class="footer-heading">CATEGORIES</h3>
            <ul class="footer-list">
                <li>
                    <a href="product.php?category=1">TRADING CARD GAME</a>
                </li>
                <li>
                    <a href="product.php?category=2">PLUSH</a>
                </li>
                <li>
                    <a href="product.php?category=3">BLIND BOX</a>
                </li>
            </ul>
        </div>

        <!-- Customer Service Section -->
        <div class="footer-section">
            <h3 class="footer-heading">SIGMA MART</h3>
            <ul class="footer-list">
                <li><a href="contact_us.php">CONTACT US</a></li>
                <li><a href="faq.php">FAQ</a></li>
            </ul>
        </div>

        <!-- Site Info Section -->
        <div class="footer-section">
            <h3 class="footer-heading">SITE INFO</h3>
            <ul class="footer-list">
                <li><a href="about_us.php">ABOUT SIGMA MART</a></li>
            </ul>
        </div>
    </div>

    <!-- Footer Bottom Section -->
    <div class="footer-bottom">
        <p class="footer-copyright">© Sigma Mart, 2025</p>
        <p class="footer-legal">Dekjie / WaiHang / LimKuan / KaiZhen / WheyLong </p>
        <ul class="footer-legal-links">
            <li><a href="#">Terms of Use</a></li>
            <li><a href="#">Cookies</a></li>
            <li><a href="#">Privacy Notice</a></li>
            <li><a href="#">Legal Info</a></li>
        </ul>
        <p class="footer-brand">Sigma Mart</p>
    </div>
</footer>

<script src="js/app.js"></script>
<?php if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false): ?>
<script src="../js/admin-sort.js"></script>

<?php endif; ?>

</body>
</html>