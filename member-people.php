<?php
require_once 'config/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: unauthorized.php");
    exit();
}


// Get total count of people
$countQuery = "SELECT COUNT(*) as total FROM people";
$countResult = mysqli_query($conn, $countQuery);
$totalPeople = mysqli_fetch_assoc($countResult)['total'];

// Get gender distribution
$genderQuery = "SELECT gender, COUNT(*) as count FROM people GROUP BY gender";
$genderResult = mysqli_query($conn, $genderQuery);
$genderData = [];
while ($row = mysqli_fetch_assoc($genderResult)) {
    $genderData[$row['gender']] = $row['count'];
}

// Get age distribution
$ageQuery = "SELECT 
    SUM(CASE WHEN age < 18 THEN 1 ELSE 0 END) as children,
    SUM(CASE WHEN age >= 18 AND age < 60 THEN 1 ELSE 0 END) as adults,
    SUM(CASE WHEN age >= 60 THEN 1 ELSE 0 END) as seniors
    FROM people";
$ageResult = mysqli_query($conn, $ageQuery);
$ageData = mysqli_fetch_assoc($ageResult);

// Get education level distribution
$educationQuery = "SELECT education_level, COUNT(*) as count FROM people WHERE education_level IS NOT NULL GROUP BY education_level ORDER BY count DESC";
$educationResult = mysqli_query($conn, $educationQuery);

// Get occupation distribution
$occupationQuery = "SELECT occupation, COUNT(*) as count FROM people WHERE occupation IS NOT NULL GROUP BY occupation ORDER BY count DESC LIMIT 5";
$occupationResult = mysqli_query($conn, $occupationQuery);

// Fetch all people with pagination
$recordsPerPage = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

$searchTerm = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$searchCondition = '';
if (!empty($searchTerm)) {
    $searchCondition = " WHERE first_name LIKE '%$searchTerm%' OR last_name LIKE '%$searchTerm%' OR house_number LIKE '%$searchTerm%' OR occupation LIKE '%$searchTerm%'";
}

$query = "SELECT * FROM people $searchCondition ORDER BY person_id DESC LIMIT $offset, $recordsPerPage";
$result = mysqli_query($conn, $query);

// Get total records for pagination
$totalQuery = "SELECT COUNT(*) as total FROM people $searchCondition";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRecords = mysqli_fetch_assoc($totalResult)['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Village People Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header-title h1 {
            font-size: 24px;
            color: #333;
        }
        .header-title p {
            font-size: 14px;
            color: #666;
        }
        .header-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-primary:hover {
            background-color: #45a049;
        }
        .btn-secondary {
            background-color: #2196F3;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #0b7dda;
        }
        .btn-danger {
            background-color: #f44336;
            color: white;
        }
        .btn-danger:hover {
            background-color: #d32f2f;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .card-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }
        .card-value {
            font-size: 24px;
            font-weight: 600;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .card-subtitle {
            font-size: 14px;
            color: #666;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .search-container button {
            padding: 10px 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        .edit-btn {
            background-color: #2196F3;
            color: white;
        }
        .edit-btn:hover {
            background-color: #0b7dda;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        .delete-btn:hover {
            background-color: #d32f2f;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .pagination a:hover {
            background-color: #f5f5f5;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        .back-btn {
            margin-bottom: 20px;
            display: inline-block;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }
        .back-btn i {
            margin-right: 5px;
        }
        .chart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex: 1;
            min-width: 300px;
        }
        .chart-title {
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
        }
        .chart-data {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .chart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chart-label {
            font-size: 14px;
            color: #666;
        }
        .chart-value {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        .progress-bar {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }
        .progress {
            height: 100%;
            border-radius: 4px;
        }
        .male-progress {
            background-color: #2196F3;
        }
        .female-progress {
            background-color: #E91E63;
        }
        .other-progress {
            background-color: #9C27B0;
        }
        .children-progress {
            background-color: #4CAF50;
        }
        .adults-progress {
            background-color: #FF9800;
        }
        .seniors-progress {
            background-color: #795548;
        }
        .no-records {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="member-dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        
        <header>
            <div class="header-title">
                <h1>Village People Management</h1>
                <p>Manage individual resident records and view demographics</p>
            </div>
            <div class="header-actions">
                <a href="member-add-person.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Person</a>
            </div>
        </header>

        <?php if ($totalPeople > 0): ?>
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-title">Total People</div>
                <div class="card-value"><?php echo $totalPeople; ?></div>
                <div class="card-subtitle">Registered residents</div>
            </div>
            <div class="card">
                <div class="card-title">Gender Distribution</div>
                <div class="card-value">
                    <?php 
                    $maleCount = isset($genderData['male']) ? $genderData['male'] : 0;
                    $femaleCount = isset($genderData['female']) ? $genderData['female'] : 0;
                    echo $maleCount . ' / ' . $femaleCount; 
                    ?>
                </div>
                <div class="card-subtitle">Male / Female</div>
            </div>
            <div class="card">
                <div class="card-title">Age Groups</div>
                <div class="card-value">
                    <?php 
                    $childrenCount = isset($ageData['children']) ? $ageData['children'] : 0;
                    $seniorsCount = isset($ageData['seniors']) ? $ageData['seniors'] : 0;
                    echo $childrenCount . ' / ' . $seniorsCount; 
                    ?>
                </div>
                <div class="card-subtitle">Children / Seniors</div>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-card">
                <div class="chart-title">Gender Distribution</div>
                <div class="chart-data">
                    <?php
                    $maleCount = isset($genderData['male']) ? $genderData['male'] : 0;
                    $femaleCount = isset($genderData['female']) ? $genderData['female'] : 0;
                    $otherCount = isset($genderData['other']) ? $genderData['other'] : 0;
                    $totalGender = $maleCount + $femaleCount + $otherCount;
                    
                    if ($totalGender > 0):
                        $malePercentage = ($maleCount / $totalGender) * 100;
                        $femalePercentage = ($femaleCount / $totalGender) * 100;
                        $otherPercentage = ($otherCount / $totalGender) * 100;
                    ?>
                    <div class="chart-item">
                        <div class="chart-label">Male</div>
                        <div class="chart-value"><?php echo $maleCount; ?> (<?php echo round($malePercentage); ?>%)</div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress male-progress" style="width: <?php echo $malePercentage; ?>%"></div>
                    </div>
                    
                    <div class="chart-item">
                        <div class="chart-label">Female</div>
                        <div class="chart-value"><?php echo $femaleCount; ?> (<?php echo round($femalePercentage); ?>%)</div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress female-progress" style="width: <?php echo $femalePercentage; ?>%"></div>
                    </div>
                    
                    <?php if ($otherCount > 0): ?>
                    <div class="chart-item">
                        <div class="chart-label">Other</div>
                        <div class="chart-value"><?php echo $otherCount; ?> (<?php echo round($otherPercentage); ?>%)</div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress other-progress" style="width: <?php echo $otherPercentage; ?>%"></div>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-title">Age Distribution</div>
                <div class="chart-data">
                    <?php
                    $childrenCount = isset($ageData['children']) ? $ageData['children'] : 0;
                    $adultsCount = isset($ageData['adults']) ? $ageData['adults'] : 0;
                    $seniorsCount = isset($ageData['seniors']) ? $ageData['seniors'] : 0;
                    $totalAge = $childrenCount + $adultsCount + $seniorsCount;
                    
                    if ($totalAge > 0):
                        $childrenPercentage = ($childrenCount / $totalAge) * 100;
                        $adultsPercentage = ($adultsCount / $totalAge) * 100;
                        $seniorsPercentage = ($seniorsCount / $totalAge) * 100;
                    ?>
                    <div class="chart-item">
                        <div class="chart-label">Children (<18)</div>
                        <div class="chart-value"><?php echo $childrenCount; ?> (<?php echo round($childrenPercentage); ?>%)</div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress children-progress" style="width: <?php echo $childrenPercentage; ?>%"></div>
                    </div>
                    
                    <div class="chart-item">
                        <div class="chart-label">Adults (18-59)</div>
                        <div class="chart-value"><?php echo $adultsCount; ?> (<?php echo round($adultsPercentage); ?>%)</div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress adults-progress" style="width: <?php echo $adultsPercentage; ?>%"></div>
                    </div>
                    
                    <div class="chart-item">
                        <div class="chart-label">Seniors (60+)</div>
                        <div class="chart-value"><?php echo $seniorsCount; ?> (<?php echo round($seniorsPercentage); ?>%)</div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress seniors-progress" style="width: <?php echo $seniorsPercentage; ?>%"></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <div class="chart-card">
                <div class="chart-title">Top Education Levels</div>
                <div class="chart-data">
                    <?php
                    $colors = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#E91E63'];
                    $i = 0;
                    while ($row = mysqli_fetch_assoc($educationResult)):
                        if ($i >= 5) break; // Show only top 5
                        $color = $colors[$i % count($colors)];
                    ?>
                    <div class="chart-item">
                        <div class="chart-label"><?php echo $row['education_level'] ? htmlspecialchars($row['education_level']) : 'Not Specified'; ?></div>
                        <div class="chart-value"><?php echo $row['count']; ?></div>
                    </div>
                    <?php
                        $i++;
                    endwhile;
                    
                    if ($i === 0):
                    ?>
                    <div class="chart-item">
                        <div class="chart-label">No education data available</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-title">Top Occupations</div>
                <div class="chart-data">
                    <?php
                    $colors = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#E91E63'];
                    $i = 0;
                    while ($row = mysqli_fetch_assoc($occupationResult)):
                        if ($i >= 5) break; // Show only top 5
                        $color = $colors[$i % count($colors)];
                    ?>
                    <div class="chart-item">
                        <div class="chart-label"><?php echo $row['occupation'] ? htmlspecialchars($row['occupation']) : 'Not Specified'; ?></div>
                        <div class="chart-value"><?php echo $row['count']; ?></div>
                    </div>
                    <?php
                        $i++;
                    endwhile;
                    
                    if ($i === 0):
                    ?>
                    <div class="chart-item">
                        <div class="chart-label">No occupation data available</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="search-container">
            <form action="" method="GET" style="display: flex; width: 100%; gap: 10px;">
                <input type="text" name="search" placeholder="Search by name, house number, or occupation..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Search</button>
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                <a href="member-people.php" class="btn btn-danger"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>House #</th>
                    <th>Occupation</th>
                    <th>Education</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['person_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo ucfirst($row['gender']); ?></td>
                    <td><?php echo htmlspecialchars($row['house_number']); ?></td>
                    <td><?php echo $row['occupation'] ? htmlspecialchars($row['occupation']) : '-'; ?></td>
                    <td><?php echo $row['education_level'] ? htmlspecialchars($row['education_level']) : '-'; ?></td>
                    <td class="action-buttons">
                        <a href="member-edit-person.php?id=<?php echo $row['person_id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</a>
                        <a href="member-delete-person.php?id=<?php echo $row['person_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this person?');"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo ($page - 1); ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            if ($startPage > 1) {
                echo '<a href="?page=1' . (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . '">1</a>';
                if ($startPage > 2) {
                    echo '<span>...</span>';
                }
            }
            
            for ($i = $startPage; $i <= $endPage; $i++) {
                $activeClass = $i == $page ? 'active' : '';
                echo '<a href="?page=' . $i . (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . '" class="' . $activeClass . '">' . $i . '</a>';
            }
            
            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                    echo '<span>...</span>';
                }
                echo '<a href="?page=' . $totalPages . (isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . '">' . $totalPages . '</a>';
            }
            ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo ($page + 1); ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="no-records">
            <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
            <p>No people found matching your search criteria. <a href="member-people.php">Clear search</a></p>
            <?php else: ?>
            <p>No people records found. <a href="member-add-person.php">Add a new person</a> to get started.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
