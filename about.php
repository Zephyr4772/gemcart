<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
// about.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | Cherry Charms</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>
<main>
    <section class="about-section about-row">
        <div class="about-img-col">
            <img src="images/dhwani.png" alt="Creator" class="about-img">
            <h2 class="about-img-title">Dhwani Chavda</h2>
        </div>
        <div class="about-text-col">
            <h1 class="about-title">Meet the Creator</h1>
            <p class="about-desc">
                Jasmine is the creative force behind Cherry Charms. With a passion for elegant jewelry and a background in design, she crafts each collection to celebrate individuality and timeless beauty. Her vision is to make every customer feel special, confident, and radiant.
            </p>
        </div>  
    </section>
    <section class="about-section about-row about-row-reverse">
        <div class="about-text-col">
            <h1 class="about-title">Co Creator </h1>
            <p class="about-desc">
                At Cherry Charms, we believe jewelry is more than an accessory—it's a story, a memory, a statement. We blend modern trends with classic elegance, ensuring every piece is as unique as the person who wears it.
            </p>
        </div>
        <div class="about-img-col">
            <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80" alt="Philosophy" class="about-img">
            <h2 class="about-img-title">Krisha</h2>
        </div>
    </section>
    <section class="about-section about-social">
        <h2 class="about-social-title">Connect with Jasmine</h2>
        <div class="about-social-links">
            <a href="#" class="about-social-link" title="LinkedIn"><i class="fa fa-linkedin"></i></a>
            <a href="#" class="about-social-link" title="GitHub"><i class="fa fa-github"></i></a>
            <a href="#" class="about-social-link" title="Instagram"><i class="fa fa-instagram"></i></a>
            <a href="#" class="about-social-link" title="Twitter"><i class="fa fa-twitter"></i></a>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html> 