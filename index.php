<?php
require_once 'includes/functions.php';

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'all';
$where = [];
$params = [];

if ($q !== '') {
    $where[] = '(a.title LIKE ? OR a.description LIKE ? OR u.username LIKE ?)';
    $term = '%' . $q . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($status === 'open') {
    $where[] = 'a.end_date > NOW()';
} elseif ($status === 'closed') {
    $where[] = 'a.end_date <= NOW()';
}

$sql = 'SELECT a.*, u.username FROM auctions a JOIN users u ON a.user_id = u.id';
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY a.end_date ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$auctions = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<main class="container">
    <section class="hero">
        <h1>Buy smart, sell fast, and win the auction every time.</h1>
        <p>Discover premium auction listings, launch your own sale, and manage bids with a clean marketplace experience.</p>
        <div class="hero-actions">
            <a class="button-hover" href="<?php echo BASE_URL; ?>/create_auction.php">Create Auction</a>
            <a class="button-hover button-secondary" href="<?php echo BASE_URL; ?>/register.php">Join now</a>
        </div>
    </section>

    <section class="card">
        <h2>Auctions</h2>
        <form method="get" action="<?php echo BASE_URL; ?>/index.php" class="search-form">
            <div class="form-group">
                <label for="q">Search</label>
                <input id="q" name="q" type="text" value="<?php echo escape($q); ?>" placeholder="Search title, description or seller">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="all"<?php echo $status === 'all' ? ' selected' : ''; ?>>All</option>
                    <option value="open"<?php echo $status === 'open' ? ' selected' : ''; ?>>Open</option>
                    <option value="closed"<?php echo $status === 'closed' ? ' selected' : ''; ?>>Closed</option>
                </select>
            </div>

            <div class="form-group">
                <input type="submit" value="Filter" class="button-hover">
            </div>
        </form>

        <?php if (empty($auctions)): ?>
            <p class="message">No auctions are available yet. Be the first to create one.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($auctions as $auction): ?>
                    <?php
                    $currentPrice = auctionCurrentPrice($auction);
                    $endsAt = new DateTime($auction['end_date']);
                    $now = new DateTime();
                    $timeLeft = $endsAt > $now ? $now->diff($endsAt)->format('%a days %h hrs %i min') : 'Closed';
                    ?>
                    <article class="auction-card">
                        <?php if (!empty($auction['image_path'])): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/<?php echo escape($auction['image_path']); ?>" alt="<?php echo escape($auction['title']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/420x240?text=Auction+Image" alt="No image">
                        <?php endif; ?>
                        <h3><?php echo escape($auction['title']); ?></h3>
                        <p><?php echo escape(substr($auction['description'], 0, 120)); ?><?php echo strlen($auction['description']) > 120 ? '...' : ''; ?></p>
                        <div class="meta">
                            <span>Current: <?php echo formatPrice($currentPrice); ?></span>
                            <span>Ends in: <?php echo escape($timeLeft); ?></span>
                        </div>
                        <div class="meta">
                            <span>Seller: <?php echo escape($auction['username']); ?></span>
                            <a class="button-hover" href="<?php echo BASE_URL; ?>/auction.php?id=<?php echo $auction['id']; ?>">View auction</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
