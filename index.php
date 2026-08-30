<?php
/**
 * Cloud Closet - Landing Page
 *
 * This is the main landing page of the Cloud Closet Rental System.
 * It imports the reusable header, navbar, and footer components.
 */

// Define page title for the header
$page_title = "Home | Premium Clothing Rental";

// Include header and navbar
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<!-- Hero Section -->
<header class="hero-section" id="home">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-subtitle">Sustainable Luxury Fashion</span>
        <h1 class="hero-title">Rent the Closet of Your Dreams</h1>
        <p class="hero-desc">
            Access hundreds of premium designer dresses and high-fashion outfits for a fraction of the retail price. Look stunning, save closet space, and protect the planet.
        </p>
        <div class="hero-cta">
            <a href="#browse" class="btn btn-lg btn-primary" id="hero-cta-btn">Browse Dresses</a>
            <a href="#about" class="btn btn-lg btn-secondary">Learn More</a>
        </div>
    </div>
</header>

<!-- How It Works Section -->
<section class="section how-it-works" id="how-it-works">
    <div class="section-container">
        <div class="section-header">
            <span class="section-subtitle">How It Works</span>
            <h2 class="section-title">Rent in 4 Simple Steps</h2>
            <div class="title-underline"></div>
        </div>
        
        <div class="steps-grid">
            <!-- Step 1 -->
            <div class="step-card">
                <div class="step-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <h3 class="step-title">1. Find Your Look</h3>
                <p class="step-desc">Explore our curated collection of premium designer dresses for every occasion.</p>
            </div>
            
            <!-- Step 2 -->
            <div class="step-card">
                <div class="step-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <h3 class="step-title">2. Choose Your Dates</h3>
                <p class="step-desc">Select a 4-day or 8-day rental window. We deliver right to your doorstep.</p>
            </div>
            
            <!-- Step 3 -->
            <div class="step-card">
                <div class="step-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                </div>
                <h3 class="step-title">3. Wear and Shine</h3>
                <p class="step-desc">Stand out at your event! Look beautiful and confident without the buyer's remorse.</p>
            </div>
            
            <!-- Step 4 -->
            <div class="step-card">
                <div class="step-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                </div>
                <h3 class="step-title">4. Return Free</h3>
                <p class="step-desc">Put the item in the pre-paid return envelope. We handle all dry-cleaning and shipping.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Outfits Section -->
<section class="section browse-section" id="browse">
    <div class="section-container">
        <div class="section-header">
            <span class="section-subtitle">Our Closet</span>
            <h2 class="section-title">Trending Styles to Rent</h2>
            <div class="title-underline"></div>
        </div>
        
        <div class="product-grid">
            <!-- Outfit Card 1 -->
            <div class="product-card">
                <div class="product-image-container">
                    <!-- Default luxury outfit display placeholder with css background gradient -->
                    <div class="product-image-placeholder outfit-1">
                        <div class="product-badge">Trending</div>
                        <div class="outfit-icon">👗</div>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-category">Evening Gown</span>
                    <h3 class="product-name">Midnight Velvet Gown</h3>
                    <div class="product-price">
                        <span class="price-rent">$45</span> <span class="price-duration">/ 4 days</span>
                        <span class="price-retail">Retail $380</span>
                    </div>
                    <button class="btn btn-sm btn-outline btn-block">View Details</button>
                </div>
            </div>
            
            <!-- Outfit Card 2 -->
            <div class="product-card">
                <div class="product-image-container">
                    <div class="product-image-placeholder outfit-2">
                        <div class="product-badge">New Arrival</div>
                        <div class="outfit-icon">👚</div>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-category">Cocktail Dress</span>
                    <h3 class="product-name">Emerald Pleated Midi</h3>
                    <div class="product-price">
                        <span class="price-rent">$39</span> <span class="price-duration">/ 4 days</span>
                        <span class="price-retail">Retail $290</span>
                    </div>
                    <button class="btn btn-sm btn-outline btn-block">View Details</button>
                </div>
            </div>
            
            <!-- Outfit Card 3 -->
            <div class="product-card">
                <div class="product-image-container">
                    <div class="product-image-placeholder outfit-3">
                        <div class="product-badge">Designer Choice</div>
                        <div class="outfit-icon">🧥</div>
                    </div>
                </div>
                <div class="product-info">
                    <span class="product-category">Formal Dress</span>
                    <h3 class="product-name">Rose Gold Sequin Slip</h3>
                    <div class="product-price">
                        <span class="price-rent">$55</span> <span class="price-duration">/ 4 days</span>
                        <span class="price-retail">Retail $450</span>
                    </div>
                    <button class="btn btn-sm btn-outline btn-block">View Details</button>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="#browse" class="btn btn-outline-primary btn-lg">Explore Full Collection</a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="section about-section" id="about">
    <div class="section-container">
        <div class="about-grid">
            <div class="about-content">
                <span class="section-subtitle">Our Mission</span>
                <h2 class="section-title">Redefining Fashion for a Better Tomorrow</h2>
                <div class="title-underline left"></div>
                <p class="about-text">
                    At Cloud Closet, we believe that style should not come at the cost of our planet. The fashion industry is one of the largest polluters in the world. By shifting from ownership to shared experiences, we can dramatically reduce waste, water use, and carbon footprints.
                </p>
                <p class="about-text">
                    Our platform gives you access to a premium, curated closet of top designer wear. Whether it's a gala, a wedding, a job interview, or a weekend brunch, Cloud Closet ensures you turn heads while making a responsible choice.
                </p>
                <div class="about-stats">
                    <div class="stat-item">
                        <span class="stat-number">5K+</span>
                        <span class="stat-label">Happy Clients</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">10K+</span>
                        <span class="stat-label">kg CO2 Saved</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Designer Outfits</span>
                    </div>
                </div>
            </div>
            <div class="about-visual">
                <!-- Decorative geometric patterns and premium borders -->
                <div class="visual-card">
                    <div class="visual-border"></div>
                    <div class="visual-bg">
                        <span class="visual-quote">"Buy less, rent more, stand out always."</span>
                        <span class="visual-author">— Cloud Closet Team</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once 'includes/footer.php';
?>
