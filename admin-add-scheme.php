<?php
require_once('config/config.php');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $department = trim($_POST['department']);
    $eligibility_criteria = trim($_POST['eligibility_criteria']);
    $benefits = trim($_POST['benefits']);
    $application_process = trim($_POST['application_process']);
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;
    $status = $_POST['status'];

    if ($title && $description && $department && $eligibility_criteria && $benefits && $application_process && $start_date && $status) {
        $stmt = $conn->prepare("INSERT INTO schemes (title, description, department, eligibility_criteria, benefits, application_process, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $title, $description, $department, $eligibility_criteria, $benefits, $application_process, $start_date, $end_date, $status);
        if ($stmt->execute()) {
            header("Location: admin-schemes.php");
            exit();
        } else {
            $message = "Failed to add scheme.";
        }
        $stmt->close();
    } else {
        $message = "Please fill all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Scheme - Admin</title>
    <link href="res/css/general-add-complaint.css" rel="stylesheet">
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1>Panchayat Dashboard</h1>
        </div>
        <nav>
            <ul>
                <li><a href="admin-schemes.php">Back to Schemes</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-content">
        <h2>Add New Scheme</h2>
        <p>Add information about a new government scheme.</p>
    </div>
</section>

<section class="data-section">
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <div class="form-group">
                    <label for="title">Scheme Title</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" placeholder="e.g., Agriculture, Rural Development" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Brief description of the scheme..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="eligibility_criteria">Eligibility Criteria</label>
                    <textarea id="eligibility_criteria" name="eligibility_criteria" rows="3" placeholder="Who can apply for this scheme..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="benefits">Benefits</label>
                    <textarea id="benefits" name="benefits" rows="3" placeholder="What benefits does this scheme provide..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="application_process">Application Process</label>
                    <textarea id="application_process" name="application_process" rows="3" placeholder="How to apply for this scheme..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date (Optional)</label>
                    <input type="date" id="end_date" name="end_date">
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">📋 Add Scheme</button>
            </form>
        </div>
    </div>
</section>

<footer>
    <div class="container">
        <p>© 2025 Panchayat Dashboard. All Rights Reserved.</p>
    </div>
</footer>

</body>
</html> 