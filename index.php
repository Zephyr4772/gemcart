<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Fetch categories for the dynamic shortcuts
$categories = [];
$cat_result = mysqli_query($conn, 'SELECT * FROM categories ORDER BY name');
while ($row = mysqli_fetch_assoc($cat_result)) {
    $categories[$row['id']] = $row['name'];
}

// Function to get a random image from a category
function getRandomCategoryImage($category_id, $category_name, $connection) {
    // First, try to get a random product image from this category
    $query = "SELECT image FROM products WHERE category_id = ? AND image != 'default.jpg' ORDER BY RAND() LIMIT 1";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['image'])) {
            // Check if it's a full URL or relative path
            if (filter_var($row['image'], FILTER_VALIDATE_URL)) {
                return $row['image'];
            } else {
                // It's a relative path, check if file exists
                $fullPath = 'assets/' . $row['image'];
                $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/gemcart/' . $fullPath;
                if (file_exists($absolutePath)) {
                    return $fullPath;
                }
            }
        }
    }
    
    // If no product image found, try to find any image in the category folder
    $category_folder = strtolower($category_name);
    $category_folder = str_replace(' ', '', $category_folder);
    $category_images = glob('assets/' . $category_folder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    
    if (!empty($category_images)) {
        // Return a random image from the category folder
        $random_image = $category_images[array_rand($category_images)];
        return $random_image;
    }
    
    // Fallback to placeholder if no images found
    return 'https://via.placeholder.com/350x260/ffffff/000000?text=' . urlencode($category_name);
}

// Define the rotation schedule for the shortcuts
// This will rotate every minute (for testing purposes)
$rotation_schedule = [
    [1, 2, 3], // Rings, Necklaces, Earrings
    [4, 5, 1], // Bracelets, Watches, Rings
    [2, 3, 4], // Necklaces, Earrings, Bracelets
    [5, 1, 2], // Watches, Rings, Necklaces
    [3, 4, 5], // Earrings, Bracelets, Watches
];

// Determine which set of categories to show based on the minute (changes every minute)
$minute = date('i'); // 0-59
$rotation_index = $minute % count($rotation_schedule);
$current_categories = $rotation_schedule[$rotation_index];

// Prepare carousel data with random category images
$carousel_data = [
    [
        "link" => "#",
        "img" => "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80",
        "label" => "EXTRA LEFT"
    ],
    [
        "link" => "products.php?category=1",
        "img" => getRandomCategoryImage(1, "Rings", $conn),
        "label" => "RINGS"
    ],
    [
        "link" => "products.php?category=2",
        "img" => getRandomCategoryImage(2, "Necklaces", $conn),
        "label" => "NECKLACES"
    ],
    [
        "link" => "products.php?category=3",
        "img" => getRandomCategoryImage(3, "Earrings", $conn),
        "label" => "EARRINGS"
    ],
    [
        "link" => "#",
        "img" => "https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80",
        "label" => "EXTRA RIGHT"
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cherry Charms - Premium Jewelry Store</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <script src="js/cart.js"></script>
</head>
<body class="has-hero" data-user-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>"
      data-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Section: Modern Carousel -->
        <section class="hero-static-slider">
            <div class="hero-slider-track-wrapper">
                <div class="hero-slider-track">
                    <div class="hero-slide active">
                        <img src="images/main-girl.jpg" alt="Golden Hour" class="hero-img">
                        <div class="hero-overlay">
                            <div class="hero-text-top">IT'S ALWAYS</div>
                            <div class="hero-text-main">GOLDEN HOUR</div>
                            <a href="products.php" class="hero-btn">Shop All</a>
                        </div>
                    </div>
                    <div class="hero-slide">
                        <img src="images/bride.jpg" alt="Bridal Collection" class="hero-img">
                        <div class="hero-overlay">
                            <div class="hero-text-top">BRIDAL COLLECTION</div>
                            <div class="hero-text-main">for the most auspicious occasion</div>
                            <a href="products.php" class="hero-btn">Shop Bridal</a>
                        </div>
                    </div>
                    <div class="hero-slide">
                        <img src="images/plat.png" alt="Everything Platinum" class="hero-img">
                        <div class="hero-overlay">
                            <div class="hero-text-top">EVERYTHING PLATINUM</div>
                            <div class="hero-text-main">not every needs a golden glow</div>
                            <a href="products.php" class="hero-btn">Shop Platinum</a>
                        </div>
                    </div>
                    <div class="hero-slide">
                        <img src="images/all.jpg" alt="Everyday Wear" class="hero-img">
                        <div class="hero-overlay">
                            <div class="hero-text-top">EVERYDAY WEAR</div>
                            <div class="hero-text-main">for that on the go shine</div>
                            <a href="products.php" class="hero-btn">Shop Everyday</a>
                        </div>
                    </div>
                </div>
            </div>
            <button class="hero-arrow left">&#10094;</button>
            <button class="hero-arrow right">&#10095;</button>
            <div class="hero-slider-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </section>

       
        <!-- Dynamic Category Shortcuts -->
        <section class="featured-categories-section">
            <div class="container">
                <h2 class="section-title">Featured Categories</h2>
                <div class="jewelry-collections-row">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <?php 
                        $cat_id = $current_categories[$i];
                        $cat_name = $categories[$cat_id];
                        // Create a URL-friendly version of the category name
                        $cat_url_name = strtolower(str_replace(' ', '-', $cat_name));
                        
                        // Get a random image for this category
                        $category_image = getRandomCategoryImage($cat_id, $cat_name, $conn);
                        
                        // Define category-specific colors and icons
                        $category_styles = [
                            1 => ['color' => '#e6c200', 'icon' => '💍'], // Rings
                            2 => ['color' => '#bfc9d1', 'icon' => '📿'], // Necklaces
                            3 => ['color' => '#b3b3b3', 'icon' => '👂'], // Earrings
                            4 => ['color' => '#5a7ca7', 'icon' => '🔗'], // Bracelets
                            5 => ['color' => '#003152', 'icon' => '⏱️']  // Watches
                        ];
                        $style = $category_styles[$cat_id];
                        ?>
                        <a href="products.php?category=<?php echo $cat_id; ?>" class="jewelry-collection-card category-<?php echo $cat_url_name; ?>" style="--category-color: <?php echo $style['color']; ?>;">
                            <div class="card-image-wrapper">
                                <div class="category-icon"><?php echo $style['icon']; ?></div>
                                <img src="<?php echo htmlspecialchars($category_image); ?>" alt="<?php echo htmlspecialchars($cat_name); ?>">
                            </div>
                            <div class="jewelry-collection-label">
                                <span class="category-name"><?php echo htmlspecialchars($cat_name); ?></span>
                            </div>
                            <div class="card-overlay">
                                <div class="overlay-text">Explore Collection</div>
                            </div>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </section>
        <style>
        .featured-categories-section {
            padding: 4rem 1rem;
            background: linear-gradient(135deg, #fff9f0 0%, #f8f4f0 100%);
            position: relative;
            overflow: hidden;
        }
        .featured-categories-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #e6c200, #bfc9d1, #b3b3b3, #5a7ca7, #003152);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        .section-title {
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #003152;
            margin-bottom: 3rem;
            position: relative;
        }
        .section-title:after {
            content: '✦';
            display: block;
            font-size: 1.8rem;
            color: #e6c200;
            margin: 0.8rem auto;
        }
        .jewelry-collections-row {
            display: flex;
            gap: 2.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .jewelry-collection-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            width: 100%;
            max-width: 360px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 3px solid #f0f0f0;
            position: relative;
            transform-style: preserve-3d;
        }
        .jewelry-collection-card:hover {
            transform: translateY(-15px) scale(1.03);
            box-shadow: 0 15px 40px rgba(90, 124, 167, 0.25);
            border-color: var(--category-color, #003152);
            z-index: 10;
        }
        .card-image-wrapper {
            width: 100%;
            height: 260px;
            overflow: hidden;
            position: relative;
        }
        .category-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            z-index: 3;
            background: rgba(255, 255, 255, 0.8);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }
        .jewelry-collection-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .jewelry-collection-card:hover img {
            transform: scale(1.1);
        }
        .jewelry-collection-label {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #003152;
            letter-spacing: 1px;
            margin: 1.8rem 0;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        .category-name {
            background: linear-gradient(90deg, #003152, var(--category-color, #003152));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            padding: 0 1rem;
        }
        .category-name:after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--category-color, #003152);
            border-radius: 3px;
        }
        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 49, 82, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .jewelry-collection-card:hover .card-overlay {
            opacity: 1;
        }
        .overlay-text {
            color: white;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transform: translateY(20px);
            transition: transform 0.4s ease;
        }
        .jewelry-collection-card:hover .overlay-text {
            transform: translateY(0);
        }
        /* Category-specific styling */
        .jewelry-collection-card.category-rings { border-color: #fff0b3; }
        .jewelry-collection-card.category-necklaces { border-color: #e0e0e0; }
        .jewelry-collection-card.category-earrings { border-color: #d0d0d0; }
        .jewelry-collection-card.category-bracelets { border-color: #c0d0e0; }
        .jewelry-collection-card.category-watches { border-color: #a0b0c0; }
        @media (max-width: 1100px) {
            .jewelry-collections-row {
                gap: 2rem;
            }
        }
        @media (max-width: 768px) {
            .jewelry-collections-row {
                flex-direction: column;
                align-items: center;
            }
            .jewelry-collection-card {
                max-width: 100%;
            }
            .card-image-wrapper {
                height: 220px;
            }
            .section-title {
                font-size: 2.2rem;
            }
        }
        
        /* Category Carousel Styles */
        .home-categories-carousel-section {
            padding: 4rem 1rem;
            background: linear-gradient(135deg, #f9f3f0 0%, #f0f0f8 100%);
            position: relative;
        }
        .home-categories-carousel-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #003152, #5a7ca7);
        }
        .carousel-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            padding: 0 3rem;
        }
        .carousel-viewport {
            overflow: hidden;
            width: 100%;
            height: 320px;
            position: relative;
        }
        .carousel-track {
            display: flex;
            gap: 1.5rem;
            position: absolute;
            top: 0;
            left: 0;
            transition: transform 0.5s ease;
        }
        .carousel-card {
            min-width: 300px;
            height: 300px;
            border-radius: 18px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        .carousel-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        .carousel-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .home-category-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 49, 82, 0.85);
            color: white;
            padding: 1.2rem;
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 700;
            text-align: center;
            letter-spacing: 1px;
        }
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            z-index: 10;
        }
        .carousel-arrow:hover {
            background: #e6c200;
            color: #003152;
            transform: translateY(-50%) scale(1.1);
        }
        .carousel-arrow.left {
            left: 0;
        }
        .carousel-arrow.right {
            right: 0;
        }
        </style>
        <!-- END Dynamic Category Shortcuts -->

        <!-- Enhanced Gender Section -->
        <section class="elegant-gender-section">
            <div class="container">
                <h2 class="section-title">Shop by Gender</h2>
                <div class="gender-cards-container">
                    <a href="watches.php" class="gender-card men">
                        <div class="gender-card-content">
                            <div class="gender-icon"></div>
                            <h3>For Him</h3>
                            <p>Bold & Timeless</p>
                            <span class="explore-link">Explore Collection</span>
                        </div>
                        <div class="card-glare"></div>
                    </a>
                    <a href="products.php?gender=women" class="gender-card women">
                        <div class="gender-card-content">
                            <div class="gender-icon"></div>
                            <h3>For Her</h3>
                            <p>Elegant & Iconic</p>
                            <span class="explore-link">Explore Collection</span>
                        </div>
                        <div class="card-glare"></div>
                    </a>
                </div>
            </div>
        </section>

        <style>
        .elegant-gender-section {
            padding: 4rem 1rem;
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f0fa 100%);
            position: relative;
            overflow: hidden;
        }
        .elegant-gender-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #003152, #5a7ca7);
        }
        .gender-cards-container {
            display: flex;
            gap: 3rem;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
        }
        .gender-card {
            flex: 1;
            min-width: 300px;
            max-width: 520px;
            height: 360px;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background-size: cover;
            background-position: center;
            border: 3px solid transparent;
        }
        .gender-card.men {
            background-image: url('https://cdn.pixabay.com/photo/2024/11/08/05/28/man-9182458_1280.jpg');
        }
        .gender-card.women {
            background-image:  url('https://cdn.pixabay.com/photo/2017/11/19/07/30/girl-2961959_1280.jpg');
        }
        .gender-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            border-color: #e6c200;
        }
        .gender-card-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            padding: 2.5rem;
            width: 100%;
        }
        .gender-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }
        .gender-card-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: 1.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        .gender-card-content p {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.3rem;
            margin-bottom: 2rem;
            font-weight: 500;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        .explore-link {
            display: inline-block;
            padding: 1rem 2.2rem;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid white;
            border-radius: 30px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        .gender-card:hover .explore-link {
            background: rgba(230, 194, 0, 0.9);
            color: #003152;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .card-glare {
            position: absolute;
            top: -100%;
            left: -100%;
            width: 50%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
            transition: all 0.8s;
        }
        .gender-card:hover .card-glare {
            top: 100%;
            left: 100%;
        }
        @media (max-width: 768px) {
            .gender-cards-container {
                flex-direction: column;
                align-items: center;
                gap: 2.5rem;
            }
            .gender-card {
                width: 100%;
                max-width: 100%;
                height: 320px;
            }
            .gender-card-content h3 {
                font-size: 2.2rem;
            }
        }
        </style>
        <!-- END Enhanced Gender Section -->

        <section class="home-gender-banners" style="display: none;">
    <div class="gender-banner gender-banner-men">
        <img src="https://cdn.pixabay.com/photo/2024/11/08/05/28/man-9182458_1280.jpg" alt="Men's Collection">
        <div class="gender-banner-overlay">
            <div class="gender-banner-sub">FOR HIM</div>
            <div class="gender-banner-title">Bold &amp; Timeless</div>
            <a href="#" class="gender-banner-btn" id="shopMenBtn">Shop Men</a>
        </div>
    </div>
    <div class="gender-banner gender-banner-women">
        <img src="https://cdn.pixabay.com/photo/2017/11/19/07/30/girl-2961959_1280.jpg" alt="Women's Collection">
        <div class="gender-banner-overlay">
            <div class="gender-banner-sub">FOR HER</div>
            <div class="gender-banner-title">Elegant &amp; Iconic</div>
            <a href="#" class="gender-banner-btn" id="shopWomenBtn">Shop Women</a>
        </div>
    </div>
</section>

<script>
// Static hero slider (auto-advance, no arrows)
const heroTrack = document.querySelector('.hero-slider-track');
const heroSlides = Array.from(document.querySelectorAll('.hero-slide'));
const heroDots = document.querySelectorAll('.hero-slider-dots .dot');
let heroCurrent = 0;

function updateHeroSlider() {
    const slideWidth = heroSlides[0].offsetWidth;
    heroTrack.style.transform = `translateX(-${heroCurrent * slideWidth}px)`;
    heroSlides.forEach((slide, i) => slide.classList.toggle('active', i === heroCurrent));
    heroDots.forEach((dot, i) => dot.classList.toggle('active', i === heroCurrent));
}
function goToHeroSlide(idx) {
    heroCurrent = (idx + heroSlides.length) % heroSlides.length;
    updateHeroSlider();
}
let heroAuto = setInterval(() => goToHeroSlide(heroCurrent + 1), 4000);
window.addEventListener('resize', updateHeroSlider);
updateHeroSlider();

// Category carousel functionality
const cardsData = <?php echo json_encode($carousel_data); ?>;
let currentIndex = 1; // Start with the first real card in the center

function renderCarousel() {
    const track = document.getElementById('carouselTrack');
    track.innerHTML = '';
    // Show 5 cards: [left-off, left, center, right, right-off]
    for (let i = -2; i <= 2; i++) {
        let idx = (currentIndex + i + cardsData.length) % cardsData.length;
        const card = document.createElement('a');
        card.href = cardsData[idx].link;
        card.className = 'carousel-card';
        card.innerHTML = `
            <img src="${cardsData[idx].img}" alt="${cardsData[idx].label}">
            <div class="home-category-overlay">${cardsData[idx].label}</div>
        `;
        track.appendChild(card);
    }
    // Center the track
    track.style.transform = `translateX(-${380 + 1.2*2*16}px)`; // 380px + 2*1.2rem gap
}

function moveCarousel(dir) {
    if (dir === 'right') {
        currentIndex = (currentIndex + 1) % cardsData.length;
    } else {
        currentIndex = (currentIndex - 1 + cardsData.length) % cardsData.length;
    }
    renderCarousel();
}

document.getElementById('carouselLeftBtn').onclick = () => moveCarousel('left');
document.getElementById('carouselRightBtn').onclick = () => moveCarousel('right');

window.addEventListener('DOMContentLoaded', renderCarousel);
</script>
    </main>

    <?php include 'includes/footer.php'; ?>