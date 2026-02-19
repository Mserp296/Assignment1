<?php
require_once "db.php";
$pdo = getPDO();

$userId = isset($_GET["userId"]) ? (int)$_GET["userId"] : 0;

$uStmt = $pdo->prepare("SELECT firstname, lastname FROM users WHERE id = :id");
$uStmt->execute([":id" => $userId]);
$user = $uStmt->fetch(PDO::FETCH_ASSOC);

function money($n) { return "$" . number_format((float)$n, 2); }

$totals = ["total_shares" => 0, "num_companies" => 0, "total_value" => 0];
$holdings = [];

if ($user) {
  // totals
  $tStmt = $pdo->prepare("
    SELECT
      COALESCE(SUM(p.amount),0) AS total_shares,
      COUNT(*) AS num_companies,
      COALESCE(SUM(p.amount * h.close),0) AS total_value
    FROM portfolio p
    JOIN history h
      ON h.symbol = p.symbol
     AND h.date = (SELECT MAX(date) FROM history h2 WHERE h2.symbol = p.symbol)
    WHERE p.userId = :uid
  ");
  $tStmt->execute([":uid" => $userId]);
  $totals = $tStmt->fetch(PDO::FETCH_ASSOC);

  // holdings list
  $hStmt = $pdo->prepare("
    SELECT
      p.symbol,
      c.name AS company_name,
      p.amount,
      h.close,
      (p.amount * h.close) AS value
    FROM portfolio p
    JOIN companies c ON c.symbol = p.symbol
    JOIN history h
      ON h.symbol = p.symbol
     AND h.date = (SELECT MAX(date) FROM history h2 WHERE h2.symbol = p.symbol)
    WHERE p.userId = :uid
    ORDER BY p.symbol
  ");
  $hStmt->execute([":uid" => $userId]);
  $holdings = $hStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Portfolio</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php $active = "home"; require "header.php"; ?>


<div class="wrap">
  <div class="grid">
    <aside class="sidebar">
      <h2>Home</h2>
      <p class="muted"><a href="index.php">← Back to customers</a></p>
    </aside>

    <section class="panel">
      <?php if (!$user): ?>
        <p class="muted">Invalid customer. Go back to <a href="index.php">Home</a>.</p>
      <?php else: ?>
        <h2><?= htmlspecialchars($user["lastname"] . ", " . $user["firstname"]) ?></h2>

        <div class="stats">
          <div class="card">
            <div class="label"># shares</div>
            <div class="big"><?= (int)$totals["total_shares"] ?></div>
          </div>
          <div class="card">
            <div class="label"># companies</div>
            <div class="big"><?= (int)$totals["num_companies"] ?></div>
          </div>
          <div class="card">
            <div class="label">Total Value</div>
            <div class="big"><?= money($totals["total_value"]) ?></div>
          </div>
        </div>

        <h3>Holdings</h3>
        <table>
          <thead>
            <tr>
              <th>Symbol</th>
              <th>Name</th>
              <th>Share Amount</th>
              <th>Value</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($holdings as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row["symbol"]) ?></td>
                <td><?= htmlspecialchars($row["company_name"]) ?></td>
                <td><?= (int)$row["amount"] ?></td>
                <td><?= money($row["value"]) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </div>
</div>
</body>
</html>
