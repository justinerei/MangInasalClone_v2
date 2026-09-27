<?php

require_once "includes/auth.php";

requireCustomerFacing();

loadSessionUser();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mang Inasal | Taste the Flavor</title>

  <link rel="stylesheet" href="style.css">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="icon" type="image/x-icon" href="images/favicon.ico">
</head>

<body>

<header>

    <div class="header-left">
      <a href="index.php">
        <img src="images/logo.png"
             alt="Mang Inasal Logo"
             class="logo">
      </a>
    </div>

    <nav class="header-center">
      <ul>

        <li>
          <a href="index.php" class="active">
            Home
          </a>
        </li>

        <li>
          <a href="mainCourse.php">
            Main Course
          </a>
        </li>

        <li>
          <a href="drinks.php">
            Drinks
          </a>
        </li>

        <li>
          <a href="dessert.php">
            Dessert
          </a>
        </li>

      </ul>
    </nav>

    <div class="header-right">

      <?php if ($isLoggedIn): ?>

        <button class="btn-primary" type="button">
          <i class="fa-solid fa-user"></i>
          <?php echo htmlspecialchars($username); ?>
        </button>

        <a href="logout.php" style="text-decoration: none;" title="Logout">
          <i class="fa-solid fa-right-from-bracket" style="color: var(--black); font-size: 1.1rem;"></i>
        </a>

      <?php else: ?>

        <a href="login.php" style="text-decoration: none;">
          <button class="btn-primary">
            Login
          </button>
        </a>

      <?php endif; ?>


      <a href="cart.php" style="text-decoration: none;">

        <div class="cart-container">

          <i class="fa-solid fa-cart-shopping cart-icon"
             style="color: var(--black);">
          </i>

          <span class="cart-badge" id="cart-badge">
            0
          </span>

        </div>

      </a>

    </div>

</header>


<main>

  <!-- HERO -->

  <section id="hero">

    <img
      src="images/heroBannerIMG.png"
      alt="Hero Banner"
      class="hero-img"
    >

  </section>


  <!-- INTRO -->

  <section id="intro">

    <div class="container text-center">

      <h2>
        Love the flavors at Mang Inasal
      </h2>

      <p>
        Mang Inasal is the Philippines' Grill Expert that delightfully
        serves Ihaw-Sarap food and Unli-Saya experience.
      </p>

    </div>

  </section>


  <!-- MENU -->

  <section id="menu">

    <div class="container">

      <h2 class="section-title">
        Menu
      </h2>

      <div class="menu-grid">


        <article class="menu-card">

          <img
            src="images/menuIMG1.png"
            alt="Paa Large - PM1"
          >

          <h3 class="card-title">
            Paa Large - PM1
          </h3>

          <button class="btn-primary add-to-cart-btn">
            Order Now
          </button>

        </article>


        <article class="menu-card">

          <img
            src="images/menuIMG2.png"
            alt="Extra Creamy Halo-Halo"
          >

          <h3 class="card-title">
            Extra Creamy Halo-Halo
          </h3>

          <button class="btn-primary add-to-cart-btn">
            Order Now
          </button>

        </article>


        <article class="menu-card">

          <img
            src="images/menuIMG3.png"
            alt="Palabok"
          >

          <h3 class="card-title">
            Palabok
          </h3>

          <button class="btn-primary add-to-cart-btn">
            Order Now
          </button>

        </article>


        <article class="menu-card">

          <img
            src="images/menuIMG4.png"
            alt="2 pc Pork BBQ with Spiced Vinegar"
          >

          <h3 class="card-title">
            2 pc Pork BBQ with Spiced Vinegar
          </h3>

          <button class="btn-primary add-to-cart-btn">
            Order Now
          </button>

        </article>


      </div>

    </div>

  </section>


  <!-- PROMOS -->

  <section id="promos">

    <div class="container">

      <h2 class="promos-title">
        Promos
      </h2>

      <p class="promos-subtitle">
        These deals and discounts are waiting for you!
      </p>

      <div class="promos-grid">

        <img
          src="images/promoIMG1.png"
          alt="Valentine Fiesta Treat"
          class="promo-banner"
        >

        <img
          src="images/promoIMG2.png"
          alt="Creamy Yess Halo-Halo"
          class="promo-banner"
        >

        <img
          src="images/promoIMG3.png"
          alt="Unli-Sayabration"
          class="promo-banner"
        >

      </div>

    </div>

  </section>

</main>


<script src="script.js"></script>

</body>
</html>