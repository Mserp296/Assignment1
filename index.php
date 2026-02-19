<?php
require_once "db.php";
$pdo = getPDO();

$stmt = $pdo->prepare("SELECT id, firstname, lastname FROM users ORDER BY lastname, firstname");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Home</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php $active = "home"; require "header.php"; ?>


<div class="wrap">
  <div class="grid">
    <aside class="sidebar">
      <h2>Customers</h2>
      <ul class="list">
        <?php foreach ($users as $u): ?>
          <li>
            <span><?= htmlspecialchars($u["lastname"] . ", " . $u["firstname"]) ?></span>
            <a class="btn" href="portfolio.php?userId=<?= urlencode($u["id"]) ?>">portfolio</a>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <section class="panel">
      <p class="muted">Select a customer’s portfolio.</p>
    </section>
  </div>
</div>
</body>
</html>
