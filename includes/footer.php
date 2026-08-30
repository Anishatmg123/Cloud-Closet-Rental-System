<!-- Footer Section -->
<footer class="footer">
    <div class="footer-container">
        <!-- Brand Info -->
        <div class="footer-brand">
            <a href="index.php" class="footer-logo">
                <svg class="logo-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a3 3 0 0 0-3 3v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3V5a3 3 0 0 0-3-3z"></path>
                </svg>
                <span class="logo-text">Cloud Closet</span>
            </a>
            <p class="brand-tagline">Rent the runway, protect the planet. Premium designer clothing rental for your special occasions.</p>
        </div>

        <!-- Quick Links -->
        <div class="footer-links-group">
            <h3>Quick Links</h3>
            <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="#browse">Browse Dresses</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
            </ul>
        </div>

        <!-- Support Links -->
        <div class="footer-links-group">
            <h3>Support & Legal</h3>
            <ul class="footer-links">
                <li><a href="#contact">Contact Support</a></li>
                <li><a href="#faq">FAQs</a></li>
                <li><a href="#terms">Terms of Service</a></li>
                <li><a href="#privacy">Privacy Policy</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Copyright bar -->
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Cloud Closet. All rights reserved. Made for sustainable fashion.</p>
    </div>
</footer>

<!-- Scripts -->
<script src="<?php echo isset($path_prefix) ? $path_prefix : ''; ?>assets/js/script.js"></script>
</body>
</html>
