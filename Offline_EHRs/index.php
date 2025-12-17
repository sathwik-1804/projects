<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Secure Electronic Health Records</title>

  <!-- Bootstrap CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />

  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fbff;
      color: #003366;
    }

    .navbar {
      background-color: #007bff;
    }

    .navbar-brand, .nav-link {
      color: #fff !important;
      font-weight: 600;
    }

    .navbar-brand:hover, .nav-link:hover {
      color: #cce5ff !important;
    }

    .hero-section {
      padding: 4rem 1rem;
      background-color: #ffffff;
      box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
      border-radius: 12px;
      margin-top: 2rem;
    }

    .hero-img {
      max-width: 100%;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 123, 255, 0.2);
    }

    .btn-primary-custom {
      background-color: #007bff;
      border: none;
    }

    .btn-primary-custom:hover {
      background-color: #0056b3;
    }

    .features-section {
      margin-top: 4rem;
    }

    .feature-card {
      background-color: #ffffff;
      border: 1px solid #dee2e6;
      border-radius: 12px;
      padding: 2rem;
      transition: transform 0.3s ease;
      box-shadow: 0 2px 10px rgba(0, 123, 255, 0.1);
    }

    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 20px rgba(0, 123, 255, 0.2);
    }

    footer {
      background-color: #003366;
      color: #ffffff;
      padding: 1.5rem 0;
      text-align: center;
    }

    footer a {
      color: #cce5ff;
      text-decoration: none;
    }

    footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <img src="images/logo.jpg" alt="EHR Logo" style="height: 40px; border-radius: 8px; margin-right: 10px;">
        Secure EHR
      </a>
      <div class="collapse navbar-collapse justify-content-end">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <div class="container hero-section">
    <div class="row align-items-center">
      <div class="col-md-6">
        <img src="images/do2.jpg" alt="Medical Illustration" class="hero-img mb-4 mb-md-0">
      </div>
      <div class="col-md-6">
        <h2 class="mb-3">Electronic Health Records</h2>
        <p>The Secure Electronic Health Records App offers a streamlined solution for managing patient information efficiently and securely. Designed with a focus on privacy, accessibility, and ease of use, it empowers healthcare providers to manage records effortlessly—even without internet connectivity.</p>
        <p>With smart features like advanced search, quick access, and complete record control, the system ensures healthcare professionals can focus more on patients and less on paperwork.</p>
        <div class="mt-4">
          <a href="register.php" class="btn btn-primary-custom me-3">Get Started</a>
          <a href="login.php" class="btn btn-outline-primary">Login</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Features Section -->
  <div class="container features-section">
    <div class="row text-center mb-4">
      <h3>Key Features</h3>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-card h-100 text-center">
          <h5>🗂️ Easy Record Management</h5>
          <p>Quickly create, edit, delete, and search patient records through a clean and user-friendly interface.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card h-100 text-center">
          <h5>⚡ Works Without Internet</h5>
          <p>Optimized for offline environments—ensures you have access to records even when connectivity is limited.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card h-100 text-center">
          <h5>🔐 Strong Data Privacy</h5>
          <p>Built with privacy in mind, ensuring sensitive patient information stays safe and secure at all times.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="mt-5">
    <div class="container">
      <p>&copy; 2025 Secure EHR. All rights reserved. | <a href="about.php">About Us</a></p>
    </div>
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
