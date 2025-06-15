<?php
/*require_once('config/config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $house_no = trim($_POST['house_no']);
    $owner_name = trim($_POST['owner_name']);
    $number_of_members = (int)$_POST['number_of_members'];

    // Check for existing house number
    $stmt = $conn->prepare("SELECT house_id FROM houses WHERE house_no = ?");
    $stmt->bind_param("s", $house_no);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "House number already exists!";
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO houses (house_no, owner_name, number_of_members) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $house_no, $owner_name, $number_of_members);

        if ($stmt->execute()) {
            header("Location: member-houses.php");
            exit();
        } else {
            $message = "Failed to add house.";
        }
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add House</title>
    <link href="res/css/general-add-complaint.css" rel="stylesheet">
</head>
<body>

<header>
    <div class="container">
        <div class="logo"><h1>Panchayat Dashboard</h1></div>
        <nav>
            <ul>
                <li><a href="member-houses.php">Back to Houses</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Add New House</h2>
    </div>
</section>

<section class="data-section">
    <div class="container">
        <?php if ($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>

        <form method="POST" class="card">
            <div class="form-group">
                <label>House Number</label>
                <input type="text" name="house_no" required>
            </div>
            <div class="form-group">
                <label>Owner Name</label>
                <input type="text" name="owner_name" required>
            </div>
            <div class="form-group">
                <label>Number of Members</label>
                <input type="number" name="number_of_members" required>
            </div>
            <button type="submit" class="btn btn-primary">Add House</button>
        </form>
    </div>
</section>

</body>
</html>
