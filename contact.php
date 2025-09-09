<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - Jewelry</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f9f6f2;
      margin: 0;
      padding: 0;
      color: #333;
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    h1 {
      text-align: center;
      color: #b8860b;
      margin-bottom: 20px;
    }
    .contact-wrapper {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      background: #fff;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    }
    .contact-info {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .contact-info h2 {
      margin-bottom: 15px;
      font-size: 22px;
      color: #444;
    }
    .info-item {
      margin-bottom: 12px;
      font-size: 15px;
    }
    .info-item strong {
      color: #b8860b;
    }
    .email-btn {
      display: inline-block;
      margin-top: 20px;
      padding: 14px 20px;
      background: #b8860b;
      color: white;
      text-decoration: none;
      font-weight: bold;
      border-radius: 8px;
      transition: background 0.3s ease;
      text-align: center;
    }
    .email-btn:hover {
      background: #a07207;
    }
    .map {
      border-radius: 12px;
      overflow: hidden;
    }
    iframe {
      width: 100%;
      height: 100%;
      min-height: 300px;
      border: none;
    }
    @media(max-width: 768px) {
      .contact-wrapper {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Contact Us</h1>
    <div class="contact-wrapper">
      <div class="contact-info">
        <h2>We’d love to hear from you!</h2>
        <p class="info-item"><strong>Phone:</strong> +91 98765 43210</p>
        <p class="info-item"><strong>Email:</strong> your-email@example.com</p>
        <p class="info-item"><strong>Address:</strong> 123 Jewelry Lane, Mumbai, India</p>
        
        <!-- Mailto Button -->
        <a class="email-btn" href="mailto:your-email@example.com?subject=Inquiry from Jewelry Website&body=Hello, I’d like to know more about...">
          📧 Email Me
        </a>
      </div>
      
      <!-- Google Map Embed -->
      <div class="map">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d241317.11609909384!2d72.74109992335842!3d19.082197839390456!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7b63c35d3d8d7%3A0xdea1f9a1e2b9f4f!2sMumbai%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1691813456789!5m2!1sen!2sin" 
          allowfullscreen="" loading="lazy">
        </iframe>
      </div>
    </div>
  </div>

</body>
</html>
