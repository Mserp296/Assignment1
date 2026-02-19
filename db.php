<?php
// db.php
function getPDO() {
    $dbPath = __DIR__ . "/data/stocks.db";
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
