<?php
require_once 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authenticity | Cherry Charms</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once 'includes/header.php'; ?>
<main class="authenticity-main">
  <section class="auth-hero">
    <h1>Authenticity & Quality Assurance</h1>
    <p>Your trust is our most valued asset. Every Cherry Charms piece is guaranteed authentic and crafted to the highest standards.</p>
  </section>
  <section class="auth-section">
    <h2>Our Promise</h2>
    <p>We guarantee that every product you purchase from Cherry Charms is genuine, ethically sourced, and meticulously crafted. Our commitment to authenticity is unwavering, and we stand behind every piece we offer.</p>
  </section>
  <section class="auth-section">
    <h2>Certificates & Hallmarks</h2>
    <ul class="auth-list">
      <li>All gold, silver, and platinum jewelry is hallmarked and certified by government-approved agencies.</li>
      <li>Diamond and gemstone pieces come with third-party authenticity certificates.</li>
      <li>Each product is accompanied by a Cherry Charms authenticity card.</li>
    </ul>
  </section>
  <section class="auth-section">
    <h2>Sourcing & Quality</h2>
    <ul class="auth-list">
      <li>We source our materials from trusted, ethical suppliers.</li>
      <li>Every piece undergoes rigorous quality checks by our in-house experts.</li>
      <li>We comply with all national and international standards for jewelry quality and safety.</li>
    </ul>
  </section>
  <section class="auth-section">
    <h2>Frequently Asked Questions</h2>
    <div class="auth-faq">
      <div class="faq-item"><strong>Q:</strong> How can I verify the authenticity of my jewelry?<br><strong>A:</strong> Each piece comes with a certificate and hallmark. You can also contact us for verification.</div>
      <div class="faq-item"><strong>Q:</strong> Are your diamonds conflict-free?<br><strong>A:</strong> Yes, we only use conflict-free, ethically sourced diamonds.</div>
      <div class="faq-item"><strong>Q:</strong> Can I request additional documentation?<br><strong>A:</strong> Absolutely! Contact us and we’ll provide any supporting documents you need.</div>
    </div>
  </section>
  <section class="auth-section contact-creator-section" id="contact-creator">
    <h2>Contact the Creator</h2>
    <div class="creator-contact-card">
      <div class="creator-info">
        <img src="images/dhwani.png" alt="Creator" class="creator-img">
        <div>
          <h3>Dhwani Chavda</h3>
          <p>Founder & Designer, Cherry Charms</p>
        </div>
      </div>
      <div class="creator-contact-details">
        <p><strong>Email:</strong> <a href="mailto:dhwani@cherrycharms.com">dhwani@cherrycharms.com</a></p>
        <p><strong>Instagram:</strong> <a href="https://instagram.com/cherrycharms" target="_blank">@cherrycharms</a></p>
      </div>
    </div>
  </section>
</main>
<style>
.authenticity-main { max-width: 900px; margin: 0 auto; padding: 2.5rem 1rem; background: #fff; border-radius: 24px; box-shadow: 0 4px 32px rgba(44,62,80,0.10); }
.auth-hero { text-align: center; margin-bottom: 2.5rem; }
.auth-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.5rem; color: #003152; margin-bottom: 0.7rem; }
.auth-hero p { color: #5a7ca7; font-size: 1.15rem; }
.auth-section { margin-bottom: 2.2rem; }
.auth-section h2 { color: #003152; font-size: 1.3rem; font-family: 'Montserrat', sans-serif; margin-bottom: 0.7rem; }
.auth-list { list-style: disc inside; color: #003152; margin-left: 1.2rem; }
.auth-faq { margin-top: 1rem; }
.faq-item { background: #f8fafd; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; color: #003152; box-shadow: 0 2px 8px #5a7ca722; }
.contact-creator-section { border-top: 2px solid #e6ecf5; padding-top: 2rem; }
.creator-contact-card { display: flex; flex-wrap: wrap; align-items: center; gap: 2rem; background: #f8fafd; border-radius: 16px; box-shadow: 0 2px 12px #5a7ca722; padding: 2rem; }
.creator-info { display: flex; align-items: center; gap: 1.2rem; }
.creator-img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 8px #5a7ca744; }
.creator-contact-details { margin-top: 1rem; }
.creator-contact-details a { color: #003152; text-decoration: underline; }
@media (max-width: 700px) { .creator-contact-card { flex-direction: column; align-items: flex-start; padding: 1rem; } }
</style>
<?php include 'includes/footer.php'; ?>
</body>
</html> 