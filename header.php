<?php
// header.php
if (!isset($active)) { $active = ""; }
?>
<header class="site-header">
  <a class="brand" href="index.php">Sage Portfolio</a>


  <nav class="nav">
    <a class="<?= ($active==="home" ? "active" : "") ?>" href="index.php">Home</a>
    <a class="<?= ($active==="companies" ? "active" : "") ?>" href="companies.php">Companies</a>
    <a class="<?= ($active==="about" ? "active" : "") ?>" href="about.php">About</a>
  </nav>
</header>
