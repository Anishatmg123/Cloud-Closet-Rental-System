<?php
/**
 * Cloud Closet - Public Editorial Landing Page
 */

$page_title = "Rent Beautiful Dresses Anywhere";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch Trending / Featured Dresses from DB
$trendingDresses = [];
$totalDressesCount = 0;
$totalUsersCount = 0;

try {
    $stmt = $pdo->query("
        SELECT d.*, c.category_name 
        FROM dresses d 
        LEFT JOIN categories c ON d.category_id = c.category_id 
        WHERE d.availability = 'available' 
        ORDER BY d.created_at DESC 
        LIMIT 6
    ");
    $trendingDresses = $stmt->fetchAll();

    $totalDressesCount = (int)$pdo->query("SELECT COUNT(*) FROM dresses")->fetchColumn();
    $totalUsersCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
} catch (Exception $e) {
    // Graceful fallback
}

// User favorites for heart state
$userFavs = [];
if (isset($_SESSION['user_id'])) {
    $userFavs = get_user_favorites_ids($pdo, $_SESSION['user_id']);
}
?>

<!-- Luxury Hero Section with Editorial Backdrop -->
<section class="landing-hero" id="home">
    <div class="container">
        <div class="hero-editorial-card">
            <span class="hero-pill-badge">
                <i class="fa-solid fa-sparkles"></i> Sustainable High Fashion Couture
            </span>
            <h1 class="hero-headline">Cloud Closet Rental System</h1>
            <p class="hero-description">
                Rent Beautiful Dresses Anywhere. Experience the future of sustainable fashion through our curated, premium editorial collection.
            </p>

            <!-- 4 Role Cards / Login Pathways -->
            <div class="hero-role-grid">
                <!-- User Login Card -->
                <div class="hero-role-card">
                    <div class="role-icon-box">
                        <i class="fa-solid fa-user-heart"></i>
                    </div>
                    <h3 class="role-card-title">User Login</h3>
                    <p class="role-card-desc">Browse gowns, manage active rentals, and track deliveries.</p>
                    <a href="login.php" class="btn btn-sm btn-magenta role-card-btn" id="hero-user-login-btn">
                        Customer Login <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Vendor Login Card -->
                <div class="hero-role-card">
                    <div class="role-icon-box">
                        <i class="fa-solid fa-shop"></i>
                    </div>
                    <h3 class="role-card-title">Vendor Login</h3>
                    <p class="role-card-desc">List wardrobe dresses, manage stock, and review booking requests.</p>
                    <a href="vendor/login.php" class="btn btn-sm btn-outline-magenta role-card-btn" id="hero-vendor-login-btn">
                        Vendor Portal <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Admin Login Card -->
                <div class="hero-role-card">
                    <div class="role-icon-box">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="role-card-title">Administrator</h3>
                    <p class="role-card-desc">System dashboard, approve vendors, manage transactions.</p>
                    <a href="admin/login.php" class="btn btn-sm btn-outline-magenta role-card-btn" id="hero-admin-login-btn">
                        Admin Central <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Register User Card -->
                <div class="hero-role-card">
                    <div class="role-icon-box">
                        <i class="fa-solid fa-sparkles"></i>
                    </div>
                    <h3 class="role-card-title">Register Account</h3>
                    <p class="role-card-desc">Join our sustainable fashion movement as a customer or vendor.</p>
                    <a href="register-choice.php" class="btn btn-sm btn-magenta role-card-btn" id="hero-register-btn">
                        Join Free <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trending Collection Section -->
<section class="landing-section" id="browse">
    <div class="container">
        <div class="section-title-wrap">
            <span class="section-pretitle">CURATED EDITORIAL</span>
            <h2 class="section-maintitle">Trending Styles to Rent</h2>
            <p class="section-desc">Handpicked designer silhouettes ready for your unforgettable occasions.</p>
        </div>

        <?php if (!empty($trendingDresses)): ?>
            <div class="dress-grid">
                <?php foreach ($trendingDresses as $dress): 
                    $isFav = in_array($dress['dress_id'], $userFavs);
                    $imgSrc = !empty($dress['image']) && file_exists(__DIR__ . '/' . $dress['image']) ? $dress['image'] : 'assets/images/hero_closet_banner.jpg';
                ?>
                    <div class="dress-card">
                        <div class="dress-image-box">
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($dress['dress_name']); ?>" loading="lazy">
                            <button type="button" class="dress-fav-btn <?php echo $isFav ? 'active' : ''; ?>" onclick="toggleFavorite(<?php echo $dress['dress_id']; ?>, this)" aria-label="Add to wishlist">
                                <i class="<?php echo $isFav ? 'fa-solid text-magenta' : 'fa-regular'; ?> fa-heart"></i>
                            </button>
                            <span class="dress-size-pill">Size <?php echo htmlspecialchars($dress['size'] ?? 'M'); ?></span>
                        </div>

                        <div class="dress-card-body">
                            <span class="dress-card-category"><?php echo htmlspecialchars($dress['category_name'] ?? 'Luxury Couture'); ?></span>
                            <h3 class="dress-card-title"><?php echo htmlspecialchars($dress['dress_name']); ?></h3>
                            
                            <div class="dress-card-footer">
                                <div class="dress-price-wrap">
                                    <span class="dress-rent-price"><?php echo format_currency($dress['rental_price']); ?></span>
                                    <span class="dress-rent-period">per rental period</span>
                                </div>
                                <a href="user/dress-details.php?id=<?php echo $dress['dress_id']; ?>" class="btn btn-sm btn-magenta">
                                    Rent Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center" style="margin-top: 45px;">
                <a href="user/dresses.php" class="btn btn-lg btn-outline-magenta">
                    Explore Complete Collection <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="text-center" style="padding: 40px; background: #ffffff; border-radius: var(--radius-lg);">
                <p class="text-muted">No dresses currently featured. Explore our wardrobe catalog.</p>
                <a href="user/dresses.php" class="btn btn-magenta mt-3">Browse Catalog</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- How It Works Section -->
<section class="landing-section" id="how-it-works" style="background-color: #ffffff;">
    <div class="container">
        <div class="section-title-wrap">
            <span class="section-pretitle">SEAMLESS PROCESS</span>
            <h2 class="section-maintitle">Rent in 4 Simple Steps</h2>
            <p class="section-desc">Experience luxury designer fashion with zero wardrobe clutter.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-num-bubble">1</div>
                <h3 class="step-card-title">Find Your Look</h3>
                <p class="step-card-desc">Browse our curated collection of luxury gowns and designer outfits for any gala, wedding, or party.</p>
            </div>
            <div class="step-card">
                <div class="step-num-bubble">2</div>
                <h3 class="step-card-title">Choose Your Dates</h3>
                <p class="step-card-desc">Select your event rental window with guaranteed date availability and door-to-door delivery.</p>
            </div>
            <div class="step-card">
                <div class="step-num-bubble">3</div>
                <h3 class="step-card-title">Wear & Shine</h3>
                <p class="step-card-desc">Make a breathtaking entrance, turn heads, and feel your most radiant without the retail buyer's remorse.</p>
            </div>
            <div class="step-card">
                <div class="step-num-bubble">4</div>
                <h3 class="step-card-title">Return Free</h3>
                <p class="step-card-desc">Simply place the gown back in the return kit. We handle eco-friendly dry-cleaning and sanitation.</p>
            </div>
        </div>
    </div>
</section>

<!-- Sustainability & About Mission -->
<section class="landing-section" id="about">
    <div class="container">
        <div class="mission-box">
            <div class="mission-left">
                <span class="section-pretitle">OUR MISSION</span>
                <h2 class="section-maintitle" style="font-size: 2.3rem;">Redefining Fashion for a Circular Future</h2>
                <p style="margin-top: 16px; color: var(--text-muted); line-height: 1.7;">
                    At Cloud Closet, we believe timeless elegance shouldn't cost the Earth. The fashion industry accounts for 10% of global carbon emissions. By transitioning from single-use ownership to shared experiences, we empower you to look stunning while saving closet space and reducing waste.
                </p>
                <div class="stats-inline-grid">
                    <div class="stat-box">
                        <span class="stat-number">5K+</span>
                        <span class="stat-label">Happy Clients</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-number"><?php echo max(50, $totalDressesCount * 12); ?>+</span>
                        <span class="stat-label">Couture Outfits</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-number">12K+</span>
                        <span class="stat-label">kg CO2 Saved</span>
                    </div>
                </div>
            </div>
            <div class="mission-right">
                <div style="background: linear-gradient(135deg, #fdf2f8 0%, #ffe4e6 100%); border-radius: var(--radius-lg); padding: 44px; text-align: center; border: 1px solid rgba(225, 29, 72, 0.15);">
                    <i class="fa-solid fa-quote-left" style="font-size: 2.5rem; color: var(--color-magenta); opacity: 0.4; margin-bottom: 20px;"></i>
                    <p style="font-family: var(--font-heading); font-size: 1.45rem; font-style: italic; color: var(--text-primary); line-height: 1.4; margin-bottom: 20px;">
                        "Buy less, rent more, stand out always."
                    </p>
                    <span style="font-weight: 700; color: var(--color-magenta); font-size: 0.9rem; letter-spacing: 1px; text-transform: uppercase;">— The Cloud Closet Editorial Team</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Luxury Public Footer -->
<footer class="luxury-footer">
    <div class="container">
        <div class="footer-top-grid">
            <div>
                <h4 class="footer-logo-title">Cloud Closet</h4>
                <p style="font-size: 0.92rem; line-height: 1.6; max-width: 320px;">
                    Rent the runway, protect the planet. Premium luxury designer clothing rental for your special occasions.
                </p>
            </div>
            <div>
                <h5 class="footer-col-title">Navigation</h5>
                <ul class="footer-links-list">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#browse">Collection</a></li>
                    <li><a href="index.php#how-it-works">How It Works</a></li>
                    <li><a href="index.php#about">About Us</a></li>
                </ul>
            </div>
            <div>
                <h5 class="footer-col-title">Portals</h5>
                <ul class="footer-links-list">
                    <li><a href="login.php">Customer Login</a></li>
                    <li><a href="vendor/login.php">Vendor Boutique Portal</a></li>
                    <li><a href="admin/login.php">Administrator Central</a></li>
                    <li><a href="register-choice.php">Register New Account</a></li>
                </ul>
            </div>
            <div>
                <h5 class="footer-col-title">Assistance</h5>
                <ul class="footer-links-list">
                    <li><a href="javascript:void(0)" onclick="showToast('Customer support is active 24/7 at support@cloudcloset.com', 'info')">Support & FAQ</a></li>
                    <li><a href="javascript:void(0)">Rental Terms</a></li>
                    <li><a href="javascript:void(0)">Eco Dry-Cleaning</a></li>
                    <li><a href="javascript:void(0)">Privacy Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom-bar">
            <p>&copy; <?php echo date("Y"); ?> Cloud Closet Rental System. All rights reserved.</p>
            <p style="color: rgba(255,255,255,0.4);">Sustainable Fashion Technology</p>
        </div>
    </div>
</footer>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
