<?php
/**
 * Database Setup and Seeder Script
 * Cloud Closet Rental System
 *
 * This script initializes missing tables (notifications, reviews),
 * verifies table schemas, and populates initial sample data (categories,
 * sample luxury dresses, approved vendors, demo bookings & payments).
 */

require_once __DIR__ . '/database.php';

try {
    echo "Starting Cloud Closet Database Setup...\n";

    // 0. Remove any legacy admin tables
    $pdo->exec("DROP TABLE IF EXISTS `admins`");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `notifications` (
            `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_type` ENUM('user', 'vendor') NOT NULL,
            `user_id` INT NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `message` TEXT NOT NULL,
            `type` VARCHAR(50) DEFAULT 'info',
            `is_read` TINYINT(1) DEFAULT 0,
            `link` VARCHAR(255) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✔ Notifications table ready.\n";

    // 2. Create Reviews Table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `reviews` (
            `review_id` INT AUTO_INCREMENT PRIMARY KEY,
            `rental_id` INT NOT NULL,
            `user_id` INT NOT NULL,
            `dress_id` INT NOT NULL,
            `rating` INT NOT NULL DEFAULT 5,
            `comment` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "✔ Reviews table ready.\n";

    // 3. Seed Categories if empty
    $catCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($catCount == 0) {
        $categories = [
            ['Evening Gowns', 'Floor-length luxury gowns for galas, red carpet events, and formal black-tie evenings.'],
            ['Cocktail Dresses', 'Chic knee-length and midi designer dresses perfect for semi-formal parties and dinners.'],
            ['Bridal & Wedding', 'Exquisite bridal wear, bridesmaid gowns, and wedding guest statement outfits.'],
            ['Prom & Gala', 'Vibrant, show-stopping silhouettes and sequin pieces for memorable celebratory nights.'],
            ['Traditional & Cultural', 'Handcrafted traditional lehengas, sarees, and cultural heritage garments.'],
            ['Designer Suits & Sets', 'Sharp tailored blazers, power suits, and coordinated luxury two-piece ensembles.']
        ];
        $catStmt = $pdo->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
        foreach ($categories as $cat) {
            $catStmt->execute($cat);
        }
        echo "✔ Seeded " . count($categories) . " luxury categories.\n";
    }

    // 4. Update vendor 1 to 'approved' so we have active vendor items
    $pdo->exec("UPDATE vendors SET status = 'approved' WHERE vendor_id = 1");
    echo "✔ Verified approved vendor exists.\n";

    // 5. Seed sample dresses if empty
    $dressCount = $pdo->query("SELECT COUNT(*) FROM dresses")->fetchColumn();
    if ($dressCount == 0) {
        // Fetch category IDs
        $cats = $pdo->query("SELECT category_id, category_name FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
        $catMap = array_flip($cats);

        $sampleDresses = [
            [
                'vendor_id' => 1,
                'category_id' => $catMap['Evening Gowns'] ?? 1,
                'dress_name' => 'Emerald Radiance Gown',
                'description' => 'A breathtaking emerald green satin floor-length gown featuring an asymmetrical shoulder strap, subtle side slit, and corseted bodice. Ideal for black-tie galas and luxury banquets.',
                'size' => 'M',
                'color' => 'Emerald Green',
                'rental_price' => 45.00,
                'security_deposit' => 20.00,
                'image' => 'assets/images/emerald_radiance.jpg',
                'availability' => 'available'
            ],
            [
                'vendor_id' => 1,
                'category_id' => $catMap['Cocktail Dresses'] ?? 2,
                'dress_name' => 'Floral Blossom Mini',
                'description' => 'Delicate organza cocktail dress embroidered with 3D floral petals and finished with puff sleeves. A playful yet sophisticated silhouette for rooftop parties and fashion launches.',
                'size' => 'S',
                'color' => 'Blush Pink',
                'rental_price' => 38.00,
                'security_deposit' => 15.00,
                'image' => 'assets/images/floral_blossom.jpg',
                'availability' => 'available'
            ],
            [
                'vendor_id' => 1,
                'category_id' => $catMap['Evening Gowns'] ?? 1,
                'dress_name' => 'Midnight Satin Slip Gown',
                'description' => 'A timeless bias-cut midnight navy satin slip gown with a delicate cowl neckline and low-back crisscross straps. Drapes effortlessly and shimmers in candlelight.',
                'size' => 'L',
                'color' => 'Midnight Navy',
                'rental_price' => 52.00,
                'security_deposit' => 25.00,
                'image' => 'assets/images/midnight_satin.jpg',
                'availability' => 'available'
            ],
            [
                'vendor_id' => 1,
                'category_id' => $catMap['Prom & Gala'] ?? 4,
                'dress_name' => 'Liquid Gold Sequin Maxi',
                'description' => 'Turn heads in this liquid-gold cascading sequin dress with long sleeves and an open back. Fully lined with ultra-soft stretch modal for maximum red-carpet comfort.',
                'size' => 'S',
                'color' => 'Champagne Gold',
                'rental_price' => 60.00,
                'security_deposit' => 30.00,
                'image' => 'assets/images/liquid_gold.jpg',
                'availability' => 'available'
            ],
            [
                'vendor_id' => 1,
                'category_id' => $catMap['Bridal & Wedding'] ?? 3,
                'dress_name' => 'Opulent Ivory Lace Gown',
                'description' => 'Hand-stitched French Chantilly lace over a structured sweetheart corset with a cascading chapel train. A masterpiece for wedding receptions and rehearsal dinners.',
                'size' => 'M',
                'color' => 'Ivory White',
                'rental_price' => 85.00,
                'security_deposit' => 40.00,
                'image' => 'assets/images/ivory_lace.jpg',
                'availability' => 'available'
            ],
            [
                'vendor_id' => 1,
                'category_id' => $catMap['Designer Suits & Sets'] ?? 6,
                'dress_name' => 'Magenta Velvet Power Suit',
                'description' => 'A tailored two-piece double-breasted velvet power suit in rich magenta. Tailored sharp lapels, satin covered buttons, and flattering wide-leg trousers.',
                'size' => 'M',
                'color' => 'Deep Magenta',
                'rental_price' => 48.00,
                'security_deposit' => 20.00,
                'image' => 'assets/images/magenta_velvet.jpg',
                'availability' => 'available'
            ],
            [
                'vendor_id' => 1,
                'category_id' => $catMap['Traditional & Cultural'] ?? 5,
                'dress_name' => 'Royal Crimson Zari Lehenga',
                'description' => 'Lavish crimson raw silk lehenga with intricate golden zari hand embroidery and embellished net dupatta. Perfect for luxury wedding festivities.',
                'size' => 'L',
                'color' => 'Crimson Red',
                'rental_price' => 95.00,
                'security_deposit' => 50.00,
                'image' => 'assets/images/royal_crimson.jpg',
                'availability' => 'available'
            ],
            [
                'vendor_id' => 1,
                'category_id' => $catMap['Cocktail Dresses'] ?? 2,
                'dress_name' => 'Crystal Embellished Black Mini',
                'description' => 'Structured crepe mini dress with hand-applied rhinestone trim around the sweetheart neckline and hemline. The ultimate statement cocktail look.',
                'size' => 'XS',
                'color' => 'Onyx Black',
                'rental_price' => 42.00,
                'security_deposit' => 20.00,
                'image' => 'assets/images/crystal_mini.jpg',
                'availability' => 'available'
            ]
        ];

        $dressStmt = $pdo->prepare("
            INSERT INTO dresses (vendor_id, category_id, dress_name, description, size, color, rental_price, security_deposit, image, availability) 
            VALUES (:vendor_id, :category_id, :dress_name, :description, :size, :color, :rental_price, :security_deposit, :image, :availability)
        ");

        foreach ($sampleDresses as $d) {
            $dressStmt->execute($d);
        }
        echo "✔ Seeded " . count($sampleDresses) . " luxury sample dresses.\n";
    }

    // 6. Seed sample rentals & payments if empty
    $rentalCount = $pdo->query("SELECT COUNT(*) FROM rentals")->fetchColumn();
    if ($rentalCount == 0) {
        $userId = $pdo->query("SELECT user_id FROM users LIMIT 1")->fetchColumn() ?: 1;
        $dresses = $pdo->query("SELECT dress_id, rental_price, security_deposit FROM dresses LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($dresses)) {
            // Rental 1: Returned
            $d1 = $dresses[0];
            $days1 = 4;
            $rentalAmt1 = $d1['rental_price'];
            $secDep1 = $d1['security_deposit'];
            $total1 = $rentalAmt1 + $secDep1;
            
            $pdo->exec("
                INSERT INTO rentals (user_id, dress_id, rental_start_date, rental_end_date, total_days, rental_amount, security_deposit, total_amount, rental_status, request_date, approved_at, returned_at)
                VALUES ($userId, {$d1['dress_id']}, DATE_SUB(CURRENT_DATE, INTERVAL 15 DAY), DATE_SUB(CURRENT_DATE, INTERVAL 11 DAY), $days1, $rentalAmt1, $secDep1, $total1, 'returned', DATE_SUB(NOW(), INTERVAL 16 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 11 DAY))
            ");
            $rId1 = $pdo->lastInsertId();
            $pdo->exec("
                INSERT INTO payments (rental_id, payment_method, amount, payment_status, transaction_id, payment_date)
                VALUES ($rId1, 'esewa', $total1, 'paid', 'TXN_ESEWA_".rand(100000, 999999)."', DATE_SUB(NOW(), INTERVAL 15 DAY))
            ");

            // Rental 2: Active
            if (isset($dresses[1])) {
                $d2 = $dresses[1];
                $days2 = 4;
                $rentalAmt2 = $d2['rental_price'];
                $secDep2 = $d2['security_deposit'];
                $total2 = $rentalAmt2 + $secDep2;

                $pdo->exec("
                    INSERT INTO rentals (user_id, dress_id, rental_start_date, rental_end_date, total_days, rental_amount, security_deposit, total_amount, rental_status, request_date, approved_at)
                    VALUES ($userId, {$d2['dress_id']}, DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY), $days2, $rentalAmt2, $secDep2, $total2, 'active', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY))
                ");
                $rId2 = $pdo->lastInsertId();
                $pdo->exec("
                    INSERT INTO payments (rental_id, payment_method, amount, payment_status, transaction_id, payment_date)
                    VALUES ($rId2, 'esewa', $total2, 'paid', 'TXN_ESEWA_".rand(100000, 999999)."', DATE_SUB(NOW(), INTERVAL 2 DAY))
                ");
            }

            // Rental 3: Approved / Upcoming
            if (isset($dresses[2])) {
                $d3 = $dresses[2];
                $days3 = 4;
                $rentalAmt3 = $d3['rental_price'];
                $secDep3 = $d3['security_deposit'];
                $total3 = $rentalAmt3 + $secDep3;

                $pdo->exec("
                    INSERT INTO rentals (user_id, dress_id, rental_start_date, rental_end_date, total_days, rental_amount, security_deposit, total_amount, rental_status, request_date, approved_at)
                    VALUES ($userId, {$d3['dress_id']}, DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 9 DAY), $days3, $rentalAmt3, $secDep3, $total3, 'approved', DATE_SUB(NOW(), INTERVAL 1 DAY), NOW())
                ");
                $rId3 = $pdo->lastInsertId();
                $pdo->exec("
                    INSERT INTO payments (rental_id, payment_method, amount, payment_status, transaction_id, payment_date)
                    VALUES ($rId3, 'cash_on_delivery', $total3, 'pending', 'TXN_COD_".rand(100000, 999999)."', NOW())
                ");
            }

            echo "✔ Seeded sample rentals & transaction payments.\n";
        }
    }

    // 7. Seed Notifications
    $notifCount = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    if ($notifCount == 0) {
        $userId = $pdo->query("SELECT user_id FROM users LIMIT 1")->fetchColumn() ?: 1;
        $vendorId = 1;

        $pdo->exec("
            INSERT INTO notifications (user_type, user_id, title, message, type, is_read, link) VALUES
            ('user', $userId, 'Booking Confirmed!', 'Your booking for Emerald Radiance Gown has been approved by the vendor.', 'success', 0, 'user/bookings.php'),
            ('user', $userId, 'Welcome to Cloud Closet', 'Enjoy 10% off your first sustainable designer dress rental with code SUSTAIN10.', 'info', 0, 'user/dresses.php'),
            ('vendor', $vendorId, 'New Booking Request', 'You have a new pending rental request for Liquid Gold Sequin Maxi.', 'info', 0, 'vendor/bookings.php'),
            ('vendor', $vendorId, 'Vendor Application Approved', 'Welcome to Cloud Closet! Your store account is fully approved.', 'success', 1, 'vendor/dashboard.php')
        ");
        echo "✔ Seeded sample notifications.\n";
    }

    echo "\n=== Cloud Closet Database Setup Successfully Completed! ===\n";

} catch (Exception $e) {
    echo "Setup Error: " . $e->getMessage() . "\n";
}
