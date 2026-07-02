<?php
require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser();
$auctionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($auctionId <= 0) {
    redirect('/dashboard.php');
}

$stmt = $pdo->prepare('SELECT * FROM auctions WHERE id = ?');
$stmt->execute([$auctionId]);
$auction = $stmt->fetch();
if (!$auction || (int)$auction['user_id'] !== (int)$user['id']) {
    redirect('/dashboard.php');
}

$errors = [];
$success = false;

$title = $auction['title'];
$description = $auction['description'];
$startingPrice = $auction['starting_price'];
$endDate = date('Y-m-d\TH:i', strtotime($auction['end_date']));
$imagePath = $auction['image_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $startingPrice = trim($_POST['starting_price'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');

    if ($title === '' || $description === '' || $startingPrice === '' || $endDate === '') {
        $errors[] = 'All fields except image are required.';
    }

    if (!is_numeric($startingPrice) || (float)$startingPrice <= 0) {
        $errors[] = 'Please enter a valid starting price.';
    }

    if ($endDate !== '' && strtotime($endDate) <= time()) {
        $errors[] = 'The auction end date must be in the future.';
    }

    if (!empty($_FILES['image']['name'])) {
        $newImagePath = uploadImage($_FILES['image']);
        if ($newImagePath === null) {
            $errors[] = 'Unable to upload the image. Allowed formats: JPG, PNG, GIF.';
        } else {
            $imagePath = $newImagePath;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE auctions SET title = ?, description = ?, starting_price = ?, end_date = ?, image_path = ? WHERE id = ?');
        $stmt->execute([
            $title,
            $description,
            (float)$startingPrice,
            $endDate,
            $imagePath,
            $auctionId,
        ]);

        $success = true;
        $auction['title'] = $title;
        $auction['description'] = $description;
        $auction['starting_price'] = $startingPrice;
        $auction['end_date'] = $endDate;
        $auction['image_path'] = $imagePath;
    }
}
?>
<?php include 'includes/header.php'; ?>
<main class="container">
    <section class="card">
        <h2>Edit Auction</h2>
        <?php if ($success): ?>
            <div class="message success">Auction information updated successfully.</div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="message error"><ul><?php foreach ($errors as $error): ?><li><?php echo escape($error); ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form method="post" action="<?php echo BASE_URL; ?>/edit_auction.php?id=<?php echo $auctionId; ?>" enctype="multipart/form-data">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" value="<?php echo escape($title); ?>" required style="color: #000;">

            <label for="description">Description</label>
            <textarea id="description" name="description" required style="color: #000;"><?php echo escape($description); ?></textarea>

            <label for="starting_price">Starting Price</label>
            <input id="starting_price" name="starting_price" type="number" step="0.01" value="<?php echo escape($startingPrice); ?>" required style="color: #000;">

            <label for="end_date">End Date</label>
            <input id="end_date" name="end_date" type="datetime-local" value="<?php echo escape($endDate); ?>" required style="color: #000;">

            <label for="image">Image (leave blank to keep current)</label>
            <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/gif">

            <input type="submit" value="Save changes">
        </form>
    </section>
</main>
<?php include 'includes/footer.php'; ?>