<?php
require_once 'includes/functions.php';

$auctionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($auctionId <= 0) {
    redirect('/index.php');
}

$stmt = $pdo->prepare('SELECT a.*, u.username FROM auctions a JOIN users u ON a.user_id = u.id WHERE a.id = ?');
$stmt->execute([$auctionId]);
$auction = $stmt->fetch();
if (!$auction) {
    redirect('/index.php');
}

$bidsStmt = $pdo->prepare('SELECT b.*, u.username FROM bids b JOIN users u ON b.user_id = u.id WHERE b.auction_id = ? ORDER BY b.amount DESC');
$bidsStmt->execute([$auctionId]);
$bids = $bidsStmt->fetchAll();

$currentPrice = auctionCurrentPrice($auction);
$endsAt = new DateTime($auction['end_date']);
$now = new DateTime();
$timeLeft = $endsAt > $now ? $now->diff($endsAt)->format('%a days %h hrs %i min') : 'Closed';

$message = '';
$messageClass = 'success';
if (isset($_GET['success'])) {
    $message = 'Your bid has been placed successfully.';
}
if (isset($_GET['error'])) {
    $messageClass = 'error';
    if ($_GET['error'] === 'ended') {
        $message = 'This auction has already ended.';
    } elseif ($_GET['error'] === 'amount') {
        $message = 'The bid amount must be greater than the current price.';
    } else {
        $message = 'Unable to place your bid.';
    }
}

$user = getCurrentUser();
$canBid = isLoggedIn() && !auctionHasEnded($auction) && (!$user || $user['id'] !== $auction['user_id']);
$winnerMessage = '';
if (auctionHasEnded($auction) && $auction['winner_id']) {
    if ($user && (int)$user['id'] === (int)$auction['winner_id']) {
        $winnerMessage = 'You are the winner! Proceed to payment.';
    } else {
        $winnerMessage = 'This auction has closed.';
    }
}
?>
<?php include 'includes/header.php'; ?>
<main class="container">
    <section class="card">
        <h2><?php echo escape($auction['title']); ?></h2>
        <?php if ($message): ?>
            <div class="message <?php echo escape($messageClass); ?>"><?php echo escape($message); ?></div>
        <?php endif; ?>
        <div class="grid">
            <div>
                <?php if (!empty($auction['image_path'])): ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/<?php echo escape($auction['image_path']); ?>" alt="<?php echo escape($auction['title']); ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/680x360?text=Auction+Image" alt="No image">
                <?php endif; ?>
            </div>
            <div>
                <p><?php echo nl2br(escape($auction['description'])); ?></p>
                <p><strong>Seller:</strong> <?php echo escape($auction['username']); ?></p>
                <p><strong>Current Price:</strong> <?php echo formatPrice($currentPrice); ?></p>
                <p><strong>Ends:</strong> <?php echo $timeLeft; ?></p>
                <p><strong>Status:</strong> <?php echo auctionHasEnded($auction) ? 'Closed' : 'Open'; ?></p>
                <?php if ($user && (int)$user['id'] === (int)$auction['user_id']): ?>
                    <a class="button-hover button-secondary" href="<?php echo BASE_URL; ?>/edit_auction.php?id=<?php echo $auction['id']; ?>">Edit Auction</a>
                <?php endif; ?>
                <?php if ($winnerMessage): ?>
                    <div class="message success"><?php echo escape($winnerMessage); ?></div>
                <?php endif; ?>
                <?php if ($user && auctionHasEnded($auction) && (int)$user['id'] === (int)$auction['winner_id']): ?>
                    <a class="button-hover" href="<?php echo BASE_URL; ?>/payment.php?id=<?php echo $auction['id']; ?>">Pay now</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <section class="card">
        <h3>Place a Bid</h3>
        <?php if ($canBid): ?>
            <form method="post" action="<?php echo BASE_URL; ?>/bid.php">
                <input type="hidden" name="auction_id" value="<?php echo $auction['id']; ?>">
                <label for="bid_amount">Your bid (must be higher than <?php echo formatPrice($currentPrice); ?>)</label>
                <input id="bid_amount" name="bid_amount" type="number" step="0.01" min="0.01" required style="color: #000;">
                <input type="submit" value="Place bid">
            </form>
        <?php elseif (!isLoggedIn()): ?>
            <p>Please <a href="<?php echo BASE_URL; ?>/login.php">login</a> to place a bid.</p>
        <?php else: ?>
            <p class="message">Bidding is closed for this auction.</p>
        <?php endif; ?>
    </section>
    <section class="card">
        <h3>Bid History</h3>
        <?php if (empty($bids)): ?>
            <p>No bids have been placed yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Placed At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bids as $bid): ?>
                        <tr>
                            <td><?php echo escape($bid['username']); ?></td>
                            <td><?php echo formatPrice($bid['amount']); ?></td>
                            <td><?php echo escape((new DateTime($bid['placed_at']))->format('Y-m-d H:i')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
