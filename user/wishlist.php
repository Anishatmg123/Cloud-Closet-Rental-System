<?php
/**
 * Customer Saved Wishlist
 */

$page_title = "My Wishlist";
$path_prefix = '../';
require_once __DIR__ . '/../includes/user_auth.php';
require_once __DIR__ . '/../includes/header.php';

$userId = (int)$_SESSION['user_id'];

// Fetch saved dresses
$stmt = $pdo->prepare("
    SELECT d.*, c.category_name, f.created_at as saved_at
    FROM favorites f
    JOIN dresses d ON f.dress_id = d.dress_id
    LEFT JOIN categories c ON d.category_id = c.category_id
    WHERE f.user_id = :user_id
    ORDER BY f.created_at DESC
");
$stmt->execute([':user_id' => $userId]);
$wishlistDresses = $stmt->fetchAll();
?>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/../includes/user_sidebar.php'; ?>

    <div class="dashboard-main-wrapper">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <main class="dashboard-content-area">
            <?php echo render_flash(); ?>

            <div class="page-header-row">
                <div>
                    <h1 class="page-title-text">My Saved Outfits</h1>
                    <p class="page-subtitle-text">Your favorite designer gowns and silhouettes saved for upcoming galas.</p>
                </div>
                <div>
                    <a href="dresses.php" class="btn btn-magenta">
                        <i class="fa-solid fa-plus"></i> Explore More Dresses
                    </a>
                </div>
            </div>

            <?php if (!empty($wishlistDresses)): ?>
                <div class="dress-grid">
                    <?php foreach ($wishlistDresses as $dress): 
                        $imgSrc = get_dress_image_url($dress['image'], $dress['dress_name'], '../');
                    ?>
                        <div class="dress-card">
                            <div class="dress-image-box">
                                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($dress['dress_name']); ?>" loading="lazy">
                                <button type="button" class="dress-fav-btn active" onclick="toggleFavorite(<?php echo $dress['dress_id']; ?>, this)" aria-label="Remove from wishlist">
                                    <i class="fa-solid fa-heart text-magenta"></i>
                                </button>
                                <span class="dress-size-pill">Size <?php echo htmlspecialchars($dress['size'] ?? 'M'); ?></span>
                            </div>

                            <div class="dress-card-body">
                                <span class="dress-card-category"><?php echo htmlspecialchars($dress['category_name'] ?? 'Luxury'); ?></span>
                                <h3 class="dress-card-title"><?php echo htmlspecialchars($dress['dress_name']); ?></h3>

                                <div class="dress-card-footer">
                                    <div class="dress-price-wrap">
                                        <span class="dress-rent-price"><?php echo format_currency($dress['rental_price']); ?></span>
                                        <span class="dress-rent-period">per rental period</span>
                                    </div>
                                    <a href="dress-details.php?id=<?php echo $dress['dress_id']; ?>" class="btn btn-sm btn-magenta">
                                        Rent Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="dashboard-card text-center" style="padding: 60px 20px;">
                    <i class="fa-regular fa-heart text-muted" style="font-size: 3rem; margin-bottom: 16px;"></i>
                    <h3 style="color: var(--text-primary); margin-bottom: 8px;">Your Wishlist is Empty</h3>
                    <p class="text-muted" style="max-width: 440px; margin: 0 auto 24px;">Explore our editorial collection and click the heart icon on any outfit to save it here for later.</p>
                    <a href="dresses.php" class="btn btn-magenta">Browse Dress Collection</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
