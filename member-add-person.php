<?php 
require_once 'config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}


// Get existing house numbers for suggestions
$houses_query = "SELECT DISTINCT house_number FROM people ORDER BY house_number";
$houses_result = mysqli_query($conn, $houses_query);
$houses = [];
while ($house = mysqli_fetch_assoc($houses_result)) {
    $houses[] = $house['house_number'];
}

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $age = (int)$_POST['age'];
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $house_number = mysqli_real_escape_string($conn, $_POST['house_number']);
    $occupation = mysqli_real_escape_string($conn, $_POST['occupation']);
    $education_level = mysqli_real_escape_string($conn, $_POST['education_level']);
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($age) || empty($gender) || empty($house_number)) {
        $error_message = "Please fill all required fields.";
    } 
    // Validate age
    else if ($age <= 0 || $age > 120) {
        $error_message = "Please enter a valid age between 1 and 120.";
    }
    else {
        // Insert new person
        $insert_query = "INSERT INTO people (first_name, last_name, age, gender, house_number, occupation, education_level) 
                        VALUES ('$first_name', '$last_name', $age, '$gender', '$house_number', '$occupation', '$education_level')";
        
        if (mysqli_query($conn, $insert_query)) {
            $success_message = "Person added successfully!";
            
            // Update population count
            // $update_population = "UPDATE population SET 
            //                     total = total + 1";
            
            // // Update gender counts
            // if ($gender == 'male') {
            //     $update_population .= ", males = males + 1";
            // } else if ($gender == 'female') {
            //     $update_population .= ", females = females + 1";
            // }
            
            // // Update age group counts
            // if ($age < 18) {
            //     $update_population .= ", children = children + 1";
            // } else if ($age >= 60) {
            //     $update_population .= ", seniors = seniors + 1";
            // } else {
            //     $update_population .= ", adults = adults + 1";
            // }
            
            // mysqli_query($conn, $update_population);
            
            // Clear form data after successful submission
            $_POST = array();
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Person - Panchayat Database</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            font-size: 24px;
            color: #333;
        }
        .back-btn {
            background-color: #6c5ce7;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        .form-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        .submit-btn {
            background-color: #6c5ce7;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            width: 100%;
            margin-top: 10px;
        }
        .submit-btn:hover {
            background-color: #5b4cc4;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .datalist-container {
            position: relative;
        }
        .house-suggestions {
            margin-top: 5px;
            font-size: 12px;
            color: #6c757d;
        }
        .house-suggestions span {
            display: inline-block;
            background-color: #e9ecef;
            padding: 2px 8px;
            margin: 2px;
            border-radius: 3px;
            cursor: pointer;
        }
        .house-suggestions span:hover {
            background-color: #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Add New Person</h1>
            <a href="member-people.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to People
            </a>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="required">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name" class="required">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="age" class="required">Age</label>
                        <input type="number" id="age" name="age" min="1" max="120" value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="gender" class="required">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="house_number" class="required">House Number</label>
                    <div class="datalist-container">
                        <input type="text" id="house_number" name="house_number" value="<?php echo isset($_POST['house_number']) ? htmlspecialchars($_POST['house_number']) : ''; ?>" required>
                        <?php if (count($houses) > 0): ?>
                        <div class="house-suggestions">
                            <small>Existing house numbers (click to select):</small><br>
                            <?php foreach ($houses as $house): ?>
                                <span onclick="selectHouse('<?php echo htmlspecialchars($house); ?>')"><?php echo htmlspecialchars($house); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="occupation">Occupation</label>
                    <input type="text" id="occupation" name="occupation" value="<?php echo isset($_POST['occupation']) ? htmlspecialchars($_POST['occupation']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="education_level">Education Level</label>
                    <select id="education_level" name="education_level">
                        <option value="">Select Education Level</option>
                        <option value="No formal education" <?php echo (isset($_POST['education_level']) && $_POST['education_level'] == 'No formal education') ? 'selected' : ''; ?>>No formal education</option>
                        <option value="Primary education" <?php echo (isset($_POST['education_level']) && $_POST['education_level'] == 'Primary education') ? 'selected' : ''; ?>>Primary education</option>
                        <option value="Secondary education" <?php echo (isset($_POST['education_level']) && $_POST['education_level'] == 'Secondary education') ? 'selected' : ''; ?>>Secondary education</option>
                        <option value="Higher secondary" <?php echo (isset($_POST['education_level']) && $_POST['education_level'] == 'Higher secondary') ? 'selected' : ''; ?>>Higher secondary</option>
                        <option value="Diploma" <?php echo (isset($_POST['education_level']) && $_POST['education_level'] == 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                        <option value="Bachelor's degree" <?php echo (isset($_POST['education_level']) && $_POST['education_level'] == "Bachelor's degree") ? 'selected' : ''; ?>>Bachelor's degree</option>
                        <option value="Master's degree" <?php echo (isset($_POST['education_level']) && $_POST['education_level'] == "Master's degree") ? 'selected' : ''; ?>>Master's degree</option>
                        <option value="Doctorate" <?php echo (isset($_POST['education_level']) && $_POST['education_level'] == 'Doctorate') ? 'selected' : ''; ?>>Doctorate</option>
                    </select>
                </div>
                
                <button type="submit" class="submit-btn">Add Person</button>
            </form>
        </div>
    </div>

    <script>
        function selectHouse(house) {
            document.getElementById('house_number').value = house;
        }
    </script>
</body>
</html>
