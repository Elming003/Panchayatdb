<?php
require_once('config/config.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM houses WHERE house_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$house = $result->fetch_assoc();
$stmt->close();

if (!$house) {
    echo "House not found.";
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $house_no = trim($_POST['house_no']);
    $owner_name = trim($_POST['owner_name']);
    $number_of_members = (int)$_POST['number_of_members'];

    // Check if another house has this number
    $stmt = $conn->prepare("SELECT house_id FROM houses WHERE house_no = ? AND house_id != ?");
    $stmt->bind_param("si", $house_no, $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "Another house with this number already exists!";
    } else {
        $stmt->close();
        $stmt = $conn->prepare("UPDATE houses SET house_no=?, owner_name=?, number_of_members=? WHERE house_id=?");
        $stmt->bind_param("ssii", $house_no, $owner_name, $number_of_members, $id);

        if ($stmt->execute()) {
            header("Location: member-houses.php");
            exit();
        } else {
            $message = "Failed to update house.";
        }
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit House</title>
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
    <div class="hero-content"><h2>Edit House</h2></div>
</section>

<section class="data-section">
    <div class="container">
        <?php if ($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
        <form method="POST" class="card">
            <div class="form-group">
                <label>House Number</label>
                <input type="text" name="house_no" value="<?php echo htmlspecialchars($house['house_no']); ?>" required>
            </div>
            <div class="form-group">
                <label>Owner Name</label>
                <input type="text" name="owner_name" value="<?php echo htmlspecialchars($house['owner_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Number of Members</label>
                <input type="number" name="number_of_members" value="<?php echo $house['number_of_members']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update House</button>
        </form>
    </div>
</section>

</body>
</html>
