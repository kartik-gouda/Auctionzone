<?php
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

$auctionId = isset($_POST['auction_id']) ? (int)$_POST['auction_id'] : 0;
$bidAmount = trim($_POST['bid_amount'] ?? '');
$redirect = '/auction.php?id=' . $auctionId;

if ($auctionId <= 0 || $bidAmount === '') {
    redirect($redirect . '&error=amount');
}

$stmt = $pdo->prepare('SELECT * FROM auctions WHERE id = ?');
$stmt->execute([$auctionId]);
$auction = $stmt->fetch();
if (!$auction) {
    redirect('/index.php');
}

if (auctionHasEnded($auction)) {
    redirect($redirect . '&error=ended');
}

$currentPrice = auctionCurrentPrice($auction);
if (!is_numeric($bidAmount) || (float)$bidAmount <= (float)$currentPrice) {
    redirect($redirect . '&error=amount');
}

$pdo->beginTransaction();
try {
    $insert = $pdo->prepare('INSERT INTO bids (auction_id, user_id, amount) VALUES (?, ?, ?)');
    $insert->execute([$auctionId, $_SESSION['user']['id'], (float)$bidAmount]);

    $update = $pdo->prepare('UPDATE auctions SET current_price = ?, winner_id = ? WHERE id = ?');
    $update->execute([(float)$bidAmount, $_SESSION['user']['id'], $auctionId]);

    $pdo->commit();
    redirect($redirect . '&success=bid');
} catch (Exception $e) {
    $pdo->rollBack();
    redirect($redirect . '&error=general');
}
