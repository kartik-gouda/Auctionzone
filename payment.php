<?php
require_once 'includes/functions.php';
requireLogin();

$auctionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($auctionId <= 0) {
    redirect('/dashboard.php');
}

$stmt = $pdo->prepare('SELECT a.*, u.username FROM auctions a JOIN users u ON a.user_id = u.id WHERE a.id = ?');
$stmt->execute([$auctionId]);
$auction = $stmt->fetch();
if (!$auction || !auctionHasEnded($auction) || (int)$auction['winner_id'] !== (int)$_SESSION['user']['id']) {
    redirect('/dashboard.php');
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE auctions SET paid = 1 WHERE id = ?');
    $stmt->execute([$auctionId]);
    $message = 'Payment recorded. Thank you for using Auctionzone!';
}
?>
<?php include 'includes/header.php'; ?>
<main class="container">
    <section class="card">
        <h2>Payment for: <?php echo escape($auction['title']); ?></h2>
        <?php if ($message): ?>
            <div class="message success"><?php echo escape($message); ?></div>
        <?php endif; ?>
        <p><strong>Seller:</strong> <?php echo escape($auction['username']); ?></p>
        <p><strong>Final amount:</strong> <?php echo formatPrice(auctionCurrentPrice($auction)); ?></p>
        <?php if ($auction['paid']): ?>
            <div class="message">This auction has already been marked as paid.</div>
        <?php else: ?>
            <form method="post" action="<?php echo BASE_URL; ?>/payment.php?id=<?php echo $auctionId; ?>">
                <input type="submit" value="Confirm payment">
            </form>
        <?php endif; ?>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
