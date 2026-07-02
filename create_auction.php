<?php
require_once 'includes/functions.php';
requireLogin();

$errors = [];
$title = '';
$description = '';
$startingPrice = '';
$endDate = '';
$success = false;

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

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $imagePath = uploadImage($_FILES['image']);
        if ($imagePath === null) {
            $errors[] = 'Unable to upload the image. Allowed formats: JPG, PNG, GIF.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO auctions (user_id, title, description, starting_price, end_date, image_path) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_SESSION['user']['id'],
            $title,
            $description,
            (float)$startingPrice,
            $endDate,
            $imagePath,
        ]);

        $success = true;
        $title = '';
        $description = '';
        $startingPrice = '';
        $endDate = '';
    }
}
?>
<?php include 'includes/header.php'; ?>
<main class="container">
    <section class="card">
        <h2>Create Auction</h2>
        <?php if ($success): ?>
            <div class="message success">Your auction has been created successfully.</div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="message error"><ul><?php foreach ($errors as $error): ?><li><?php echo escape($error); ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form method="post" action="<?php echo BASE_URL; ?>/create_auction.php" enctype="multipart/form-data">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" value="<?php echo escape($title); ?>" required style="color: #000;">

            <label for="description">Description</label>
            <textarea id="description" name="description" required style="color: #000;"><?php echo escape($description); ?></textarea>

            <label for="starting_price">Starting Price</label>
            <input id="starting_price" name="starting_price" type="number" step="0.01" value="<?php echo escape($startingPrice); ?>" required style="color: #000;">

            <label for="end_date">End Date</label>
            <input id="end_date" name="end_date" type="datetime-local" value="<?php echo escape($endDate); ?>" required style="color: #000;">

            <label for="image">Image (optional)</label>
            <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/gif">

            <input type="submit" value="Create Auction">
        </form>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
