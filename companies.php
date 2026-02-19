<?php
require_once "db.php";
$pdo = getPDO();

$symbol = isset($_GET["symbol"]) ? $_GET["symbol"] : "";


$listStmt = $pdo->prepare("SELECT symbol, name FROM companies ORDER BY name");
$listStmt->execute();
$companies = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$company = null;
$stats = null;
$history = [];

if ($symbol !== "") {
  $cStmt = $pdo->prepare("SELECT * FROM companies WHERE symbol = :s");
  $cStmt->execute([":s" => $symbol]);
  $company = $cStmt->fetch(PDO::FETCH_ASSOC);

  if ($company) {
    // Note: PDF says volume columns are in companies table, but in your stocks.db they are in history.
    $sStmt = $pdo->prepare("
      SELECT
        COALESCE(SUM(volume),0) AS total_volume,
        COALESCE(AVG(volume),0) AS avg_volume,
        MAX(high) AS history_high,
        MIN(low) AS history_low
      FROM history
      WHERE symbol = :s
    ");
    $sStmt->execute([":s" => $symbol]);
    $stats = $sStmt->fetch(PDO::FETCH_ASSOC);

    $hStmt = $pdo->prepare("
      SELECT date, volume, open, close, high, low
      FROM history
      WHERE symbol = :s
      ORDER BY date ASC
    ");
    $hStmt->execute([":s" => $symbol]);
    $history = $hStmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

function money($n) { 
    return "$" . number_format((float)$n, 2); 
    }

function num2($n) { 
    return number_format((float)$n, 2); 
    }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Companies</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php $active = "companies"; require "header.php"; ?>


<div class="wrap">
  <div class="grid">
    <aside class="sidebar">
      <h2>Companies</h2>
      <ul class="list">
        <?php foreach ($companies as $c): ?>
          <li>
            <a class="company-link <?= ($symbol === $c["symbol"] ? "active" : "") ?>"
            href="companies.php?symbol=<?= urlencode($c["symbol"]) ?>">
            <?= htmlspecialchars($c["name"]) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <section class="panel">
      <?php if ($symbol === "" || !$company): ?>
        <p class="muted">Click a company to view details.</p>
      <?php else: ?>
        <h2><?= htmlspecialchars($company["name"]) ?> (<?= htmlspecialchars($company["symbol"]) ?>)</h2>

        <p><strong>Sector:</strong> <?= htmlspecialchars($company["sector"]) ?></p>
        <p><strong>Sub-industry:</strong> <?= htmlspecialchars($company["subindustry"]) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($company["address"]) ?></p>
        <p><strong>Exchange:</strong> <?= htmlspecialchars($company["exchange"]) ?></p>
        <p><strong>Website:</strong>
          <a href="<?= htmlspecialchars($company["website"]) ?>" target="_blank">
            <?= htmlspecialchars($company["website"]) ?>
          </a>
        </p>
        <p><?= htmlspecialchars($company["description"]) ?></p>

        <div class="stats">
          <div class="card">
            <div class="label">History High</div>
            <div class="big"><?= money($stats["history_high"]) ?></div>
          </div>
          <div class="card">
            <div class="label">History Low</div>
            <div class="big"><?= money($stats["history_low"]) ?></div>
          </div>
          <div class="card">
            <div class="label">Total Volume</div>
            <div class="big"><?= number_format((float)$stats["total_volume"], 0) ?></div>
          </div>
          <div class="card">
            <div class="label">Average Volume</div>
            <div class="big"><?= number_format((float)$stats["avg_volume"], 0) ?></div>
          </div>
        </div>

        <h3>History (Jan 2 – Mar 29, 2019)</h3>
        <table>
          <thead>
            <tr>
              <th>Date</th><th>Volume</th><th>Open</th><th>Close</th><th>High</th><th>Low</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($history as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r["date"]) ?></td>
                <td><?= number_format((float)$r["volume"], 0) ?></td>
                <td><?= num2($r["open"]) ?></td>
                <td><?= num2($r["close"]) ?></td>
                <td><?= num2($r["high"]) ?></td>
                <td><?= num2($r["low"]) ?></td>
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
