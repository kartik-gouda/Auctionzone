<?php
require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser();

$auctionStmt = $pdo->prepare('SELECT * FROM auctions WHERE user_id = ? ORDER BY created_at DESC');
$auctionStmt->execute([$user['id']]);
$myAuctions = $auctionStmt->fetchAll();

$bidStmt = $pdo->prepare(
    'SELECT b.amount, b.placed_at, a.title, a.id AS auction_id, a.end_date FROM bids b JOIN auctions a ON b.auction_id = a.id WHERE b.user_id = ? ORDER BY b.placed_at DESC'
);
$bidStmt->execute([$user['id']]);
$myBids = $bidStmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<main class="container">
    <section class="card">
        <h2>Welcome, <?php echo escape($user['username']); ?></h2>
        <p>Use the buttons above to create auctions or view your bids.</p>
    </section>

    <section class="card">
        <h3>My Auctions</h3>
        <?php if (empty($myAuctions)): ?>
            <p class="message">You haven't created any auctions yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Current Price</th>
                        <th>Ends</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myAuctions as $auction): ?>
                        <tr>
                            <td><a href="<?php echo BASE_URL; ?>/auction.php?id=<?php echo $auction['id']; ?>"><?php echo escape($auction['title']); ?></a></td>
                            <td><?php echo formatPrice(auctionCurrentPrice($auction)); ?></td>
                            <td><?php echo escape((new DateTime($auction['end_date']))->format('Y-m-d H:i')); ?></td>
                            <td><?php echo auctionHasEnded($auction) ? 'Closed' : 'Open'; ?></td>
                            <td><a class="button-hover button-secondary" href="<?php echo BASE_URL; ?>/edit_auction.php?id=<?php echo $auction['id']; ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="card">
        <h3>My Bids</h3>
        <?php if (empty($myBids)): ?>
            <p class="message">You haven't placed any bids yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Auction</th>
                        <th>Bid Amount</th>
                        <th>Placed At</th>
                        <th>Ends</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myBids as $bid): ?>
                        <tr>
                            <td><a href="<?php echo BASE_URL; ?>/auction.php?id=<?php echo $bid['auction_id']; ?>"><?php echo escape($bid['title']); ?></a></td>
                            <td><?php echo formatPrice($bid['amount']); ?></td>
                            <td><?php echo escape((new DateTime($bid['placed_at']))->format('Y-m-d H:i')); ?></td>
                            <td><?php echo escape((new DateTime($bid['end_date']))->format('Y-m-d H:i')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
