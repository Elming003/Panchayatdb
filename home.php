<?php
session_start(); // Must be first

$profile_link = 'signin.php'; // Default
$profile_picture = 'res/image/user.png'; // Default profile picture

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // Set profile link based on role
    switch ($_SESSION['role']) {
        case 'admin':
            $profile_link = 'admin-dashboard.php';
            break;
        case 'user':
            $profile_link = 'general-dashboard.php';
            break;
        case 'member':
            $profile_link = 'member-dashboard.php';
            break;
    }
    
    // Fetch user's profile picture
    require_once('config/config.php');
    $stmt = $conn->prepare("SELECT profile_picture FROM user_details WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (!empty($user['profile_picture'])) {
            $profile_picture = $user['profile_picture'];
        }
    }
    $stmt->close();
}

  require_once('config/config.php');

  // Query to fetch notices from the database
  $sql = "SELECT * FROM notices ORDER BY created_at DESC";
  $notice_result = $conn->query($sql);
  //
  $positions = [
    'headman' => "SELECT u.user_id, ud.first_name, ud.middle_name, ud.last_name, ud.profile_picture, ud.phone_no, vr.created_at
                  FROM village_representatives vr
                  JOIN users u ON vr.user_id = u.user_id
                  JOIN user_details ud ON u.user_id = ud.user_id
                  WHERE vr.position = 'headman'",
    'secretary' => "SELECT u.user_id, ud.first_name, ud.middle_name, ud.last_name, ud.profile_picture, ud.phone_no, vr.created_at
                    FROM village_representatives vr
                    JOIN users u ON vr.user_id = u.user_id
                    JOIN user_details ud ON u.user_id = ud.user_id
                    WHERE vr.position = 'secretary'",
    'treasurer' => "SELECT u.user_id, ud.first_name, ud.middle_name, ud.last_name, ud.profile_picture, ud.phone_no, vr.created_at
                    FROM village_representatives vr
                    JOIN users u ON vr.user_id = u.user_id
                    JOIN user_details ud ON u.user_id = ud.user_id
                    WHERE vr.position = 'treasurer'",
    'health coordinator' => "SELECT u.user_id, ud.first_name, ud.middle_name, ud.last_name, ud.profile_picture, ud.phone_no, vr.created_at 
                             FROM village_representatives vr
                             JOIN users u ON vr.user_id = u.user_id
                             JOIN user_details ud ON u.user_id = ud.user_id
                             WHERE vr.position = 'health coordinator'",
    'education officer' => "SELECT u.user_id, ud.first_name, ud.middle_name, ud.last_name, ud.profile_picture, ud.phone_no, vr.created_at 
                            FROM village_representatives vr
                            JOIN users u ON vr.user_id = u.user_id
                            JOIN user_details ud ON u.user_id = ud.user_id
                            WHERE vr.position = 'education officer'"
];

$committee_data = [];

// Loop through each position to fetch the representative data
foreach ($positions as $position => $query) {
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $committee_data[$position] = $result->fetch_assoc();
    } else {
        // No representative found for this position
        $committee_data[$position] = null;
    }
}

// Fetch population data (latest row only)
$population_query = "SELECT COUNT(*) AS total_population FROM people";
$population_result = $conn->query($population_query);
$population_data = $population_result && $population_result->num_rows > 0 ? $population_result->fetch_assoc() : null;

// Calculate registered population
$registered_sql = "SELECT 
    COUNT(*) as total_count,
    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= 18 THEN 1 ELSE 0 END) as above_18,
    SUM(CASE WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN 1 ELSE 0 END) as below_18
FROM user_details";
$registered_result = $conn->query($registered_sql);
$registered_counts = $registered_result->fetch_assoc();

$schemes_sql = "SELECT * FROM schemes WHERE status = 'active' ORDER BY created_at DESC LIMIT 3";
$schemes_result = $conn->query($schemes_sql);

// Fetch total houses
$houses_query = "SELECT COUNT(*) AS total_houses FROM houses";
$houses_result = $conn->query($houses_query);
$total_houses = $houses_result && $houses_result->num_rows > 0 ? $houses_result->fetch_assoc()['total_houses'] : 0;


// Get count of people from the people table with detailed statistics
$people_count_query = "SELECT 
    COUNT(*) as total_people,
    SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male_count,
    SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female_count,
    SUM(CASE WHEN gender = 'other' THEN 1 ELSE 0 END) as other_count,
    SUM(CASE WHEN age >= 18 THEN 1 ELSE 0 END) as adults,
    SUM(CASE WHEN age < 18 THEN 1 ELSE 0 END) as children,
    COUNT(DISTINCT house_number) as unique_houses
FROM people";
$people_result = $conn->query($people_count_query);
$people_data = $people_result && $people_result->num_rows > 0 ? $people_result->fetch_assoc() : [
    'total_people' => 0,
    'male_count' => 0,
    'female_count' => 0,
    'other_count' => 0,
    'adults' => 0,
    'children' => 0,
    'unique_houses' => 0
];

// Calculate coverage percentage
$population_coverage = 0;
if ($population_data && $people_data && $population_data['total_population'] > 0) {
    $population_coverage = round(($people_data['total_people'] / $population_data['total_population']) * 100);
}

// Get detailed age distribution
$age_distribution_query = "SELECT 
    SUM(CASE WHEN age < 5 THEN 1 ELSE 0 END) as age_under_5,
    SUM(CASE WHEN age >= 5 AND age < 18 THEN 1 ELSE 0 END) as age_5_to_17,
    SUM(CASE WHEN age >= 18 AND age < 30 THEN 1 ELSE 0 END) as age_18_to_29,
    SUM(CASE WHEN age >= 30 AND age < 45 THEN 1 ELSE 0 END) as age_30_to_44,
    SUM(CASE WHEN age >= 45 AND age < 60 THEN 1 ELSE 0 END) as age_45_to_59,
    SUM(CASE WHEN age >= 60 THEN 1 ELSE 0 END) as age_60_plus
FROM people";
$age_distribution_result = $conn->query($age_distribution_query);
$age_distribution = $age_distribution_result->fetch_assoc();

// Get education level distribution
$education_query = "SELECT 
    education_level, 
    COUNT(*) as count 
FROM people 
WHERE education_level IS NOT NULL AND education_level != '' 
GROUP BY education_level 
ORDER BY count DESC";
$education_result = $conn->query($education_query);

// Get top occupations
$occupation_query = "SELECT 
    occupation, 
    COUNT(*) as count 
FROM people 
WHERE occupation IS NOT NULL AND occupation != '' 
GROUP BY occupation 
ORDER BY count DESC 
LIMIT 5";
$occupation_result = $conn->query($occupation_query);

// Get household size distribution
$household_size_query = "SELECT 
    house_number, 
    COUNT(*) as household_size 
FROM people 
GROUP BY house_number 
ORDER BY household_size DESC";
$household_result = $conn->query($household_size_query);

// Calculate household statistics
$household_stats = [
    'total_households' => 0,
    'avg_size' => 0,
    'largest_size' => 0,
    'single_person' => 0,
    'small_family' => 0, // 2-4 people
    'medium_family' => 0, // 5-7 people
    'large_family' => 0 // 8+ people
];

if ($household_result && $household_result->num_rows > 0) {
    $household_stats['total_households'] = $household_result->num_rows;
    $total_people = 0;
    $max_size = 0;
    
    // Reset the pointer to the beginning
    $household_result->data_seek(0);
    
    while ($household = $household_result->fetch_assoc()) {
        $size = $household['household_size'];
        $total_people += $size;
        
        if ($size > $max_size) {
            $max_size = $size;
        }
        
        if ($size == 1) {
            $household_stats['single_person']++;
        } elseif ($size >= 2 && $size <= 4) {
            $household_stats['small_family']++;
        } elseif ($size >= 5 && $size <= 7) {
            $household_stats['medium_family']++;
        } else {
            $household_stats['large_family']++;
        }
    }
    
    $household_stats['avg_size'] = $total_people / $household_stats['total_households'];
    $household_stats['largest_size'] = $max_size;
}
  ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Village Database Management System</title>
    <link rel="stylesheet" href="res/css/home.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="css/fontawesome.min.css">

  </head>
  <body>
    <!-- Navigation Bar -->
    <header>
      <div class="container">
        <div class="logo">
          <h1>PanchayatDB</h1>
        </div>
        <nav>
  <ul>
 
    <li><a href="#population">Population</a></li>
    <li><a href="#committee">Committee</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="#notices">Notices</a></li>
    <li><a href="schemes.php">Govt. Schemes</a></li>
    
    <?php if (isset($_SESSION['user_id'])): ?>
      <li><a href="logout.php">Log Out</a></li>
    <?php else: ?>
      
    <?php endif; ?>
    <li class="profile-item">
      <a href="<?= $profile_link ?>" class="profile-link">
        <img src="<?= $profile_picture ?>" alt="Profile" class="profile-image">
      </a>
    </li>
  </ul>
</nav>

      </div>
    </header>

    <!-- Hero Banner -->
    <section class="hero" id="home">
      <div class="hero-content">
        <h2>Welcome to Your Panchayat Management Portal</h2>
        <p>A comprehensive database system for efficient village management</p>
        <div class="hero-buttons">
          <a href="<?= $profile_link ?>" class="btn btn-primary">Explore Data</a>
        </div>
      </div>
    </section>

    <style>
      .profile-item {
        display: flex;
        align-items: center;
      }
      .profile-link {
        display: flex;
        align-items: center;
        padding: 5px;
        border-radius: 50%;
        transition: background-color 0.2s;
      }
      .profile-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
      }
      .profile-image {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.8);
      }
    </style>

    <!-- Main Content -->
    <main>

     <!-- Population Summary Section -->
<section class="data-section" id="population">
  <div class="container">
    <h2 class="section-title">Village Population & Housing Overview</h2>
    
    <!-- Report Navigation Tabs -->
    <div class="report-tabs">
      <button class="tab-btn active" data-tab="summary">Summary</button>
    </div>
    
    <!-- Summary Tab (Default View) -->
    <div class="report-tab-content active" id="summary-tab">
      <div class="cards-container">
        <div class="card">
          <div class="card-icon population-icon"></div>
          <h3>Total Population</h3>
          <p class="data-number"><?= $population_data ? number_format($population_data['total_population']) : 'N/A' ?></p>
          <div class="data-details">
            <p>Census Records</p>
            <div class="progress-container">
              <div class="progress-bar" style="width: <?= $population_coverage ?>%"></div>
            </div>
            <p class="registered-count">
              <strong><?= $people_data ? number_format($people_data['total_people']) : '0' ?></strong> individuals registered
              <span class="coverage-text">(<?= $population_coverage ?>% coverage)</span>
            </p>
          </div>
        </div>
        
        <div class="card">
          <div class="card-icon gender-icon"></div>
          <h3>Gender Distribution</h3>
          <div class="gender-stats">
            <div class="gender-stat">
              <span class="gender-label">Male</span>
              <span class="gender-count"><?= number_format($people_data['male_count'] ?? 0) ?></span>
              <span class="gender-percent"><?= $people_data['total_people'] > 0 ? round(($people_data['male_count'] / $people_data['total_people']) * 100) : 0 ?>%</span>
            </div>
            <div class="gender-stat">
              <span class="gender-label">Female</span>
              <span class="gender-count"><?= number_format($people_data['female_count'] ?? 0) ?></span>
              <span class="gender-percent"><?= $people_data['total_people'] > 0 ? round(($people_data['female_count'] / $people_data['total_people']) * 100) : 0 ?>%</span>
            </div>
            <div class="gender-stat">
              <span class="gender-label">Other</span>
              <span class="gender-count"><?= number_format($people_data['other_count'] ?? 0) ?></span>
              <span class="gender-percent"><?= $people_data['total_people'] > 0 ? round(($people_data['other_count'] / $people_data['total_people']) * 100) : 0 ?>%</span>
            </div>
          </div>
          <div class="mini-chart-container">
            <canvas id="genderChart" height="80"></canvas>
          </div>
        </div>
        
        <div class="card">
          <div class="card-icon age-icon"></div>
          <h3>Age Groups</h3>
          <div class="age-stats">
            <div class="age-stat">
              <span class="age-label">Adults (18+)</span>
              <span class="age-count"><?= number_format($people_data['adults'] ?? 0) ?></span>
              <div class="age-bar-container">
                <div class="age-bar" style="width: <?= $people_data['total_people'] > 0 ? round(($people_data['adults'] / $people_data['total_people']) * 100) : 0 ?>%"></div>
              </div>
            </div>
            <div class="age-stat">
              <span class="age-label">Children (<18)</span>
              <span class="age-count"><?= number_format($people_data['children'] ?? 0) ?></span>
              <div class="age-bar-container">
                <div class="age-bar children-bar" style="width: <?= $people_data['total_people'] > 0 ? round(($people_data['children'] / $people_data['total_people']) * 100) : 0 ?>%"></div>
              </div>
            </div>
          </div>
          <div class="mini-chart-container">
            <canvas id="ageGroupChart" height="80"></canvas>
          </div>
        </div>
        
        <div class="card">
          <div class="card-icon house-icon"></div>
          <h3>Households</h3>
          <p class="data-number"><?= $household_stats['total_households'] ?></p>
          <p>Registered Houses</p>
          <div class="household-stats">
            <div class="household-stat">
              <!-- <span class="stat-label">Average Size:</span>
              <span class="stat-value"><?= number_format($household_stats['avg_size'], 1) ?> people</span> -->
            </div>
            <!-- <div class="household-stat">
              <span class="stat-label">Largest:</span>
              <span class="stat-value"><?= $household_stats['largest_size'] ?> people</span> -->
            </div>
          </div>
        </div>
      </div>
    </div>
    
   
  </div>
  
  <!-- <script>
    // Tab switching functionality
    document.addEventListener('DOMContentLoaded', function() {
      const tabButtons = document.querySelectorAll('.tab-btn');
      const tabContents = document.querySelectorAll('.report-tab-content');
      
      tabButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Remove active class from all buttons and contents
          tabButtons.forEach(btn => btn.classList.remove('active'));
          tabContents.forEach(content => content.classList.remove('active'));
          
          // Add active class to clicked button
          this.classList.add('active');
          
          // Show corresponding content
          const tabId = this.getAttribute('data-tab');
          document.getElementById(tabId + '-tab').classList.add('active');
        });
      });
      
      // Initialize charts
      initializeCharts();
    });
    
    function initializeCharts() {
      // Gender mini chart
      const genderCtx = document.getElementById('genderChart').getContext('2d');
      new Chart(genderCtx, {
        type: 'doughnut',
        data: {
          labels: ['Male', 'Female', 'Other'],
          datasets: [{
            data: [
              <?= $people_data ? $people_data['male_count'] : 0 ?>, 
              <?= $people_data ? $people_data['female_count'] : 0 ?>, 
              <?= $people_data ? $people_data['other_count'] : 0 ?>
            ],
            backgroundColor: [
              'rgba(54, 162, 235, 0.5)',
              'rgba(255, 99, 132, 0.5)',
              'rgba(255, 206, 86, 0.5)'
            ],
            borderColor: [
              'rgba(54, 162, 235, 1)',
              'rgba(255, 99, 132, 1)',
              'rgba(255, 206, 86, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });
      
      // Age group mini chart
      const ageGroupCtx = document.getElementById('ageGroupChart').getContext('2d');
      new Chart(ageGroupCtx, {
        type: 'bar',
        data: {
          labels: ['Adults (18+)', 'Children (<18)'],
          datasets: [{
            data: [
              <?= $people_data ? $people_data['adults'] : 0 ?>, 
              <?= $people_data ? $people_data['children'] : 0 ?>
            ],
            backgroundColor: [
              'rgba(54, 162, 235, 0.5)',
              'rgba(75, 192, 192, 0.5)'
            ],
            borderColor: [
              'rgba(54, 162, 235, 1)',
              'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              display: false
            },
            x: {
              display: false
            }
          }
        }
      });
      
      // Age distribution chart
      const ageDistributionCtx = document.getElementById('ageDistributionChart').getContext('2d');
      new Chart(ageDistributionCtx, {
        type: 'bar',
        data: {
          labels: ['Under 5', '5-17', '18-29', '30-44', '45-59', '60+'],
          datasets: [{
            label: 'Number of People',
            data: [
              <?= $age_distribution['age_under_5'] ?>,
              <?= $age_distribution['age_5_to_17'] ?>,
              <?= $age_distribution['age_18_to_29'] ?>,
              <?= $age_distribution['age_30_to_44'] ?>,
              <?= $age_distribution['age_45_to_59'] ?>,
              <?= $age_distribution['age_60_plus'] ?>
            ],
            backgroundColor: [
              'rgba(54, 162, 235, 0.5)',
              'rgba(75, 192, 192, 0.5)',
              'rgba(255, 159, 64, 0.5)',
              'rgba(153, 102, 255, 0.5)',
              'rgba(255, 99, 132, 0.5)',
              'rgba(201, 203, 207, 0.5)'
            ],
            borderColor: [
              'rgba(54, 162, 235, 1)',
              'rgba(75, 192, 192, 1)',
              'rgba(255, 159, 64, 1)',
              'rgba(153, 102, 255, 1)',
              'rgba(255, 99, 132, 1)',
              'rgba(201, 203, 207, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Number of People'
              }
            }
          }
        }
      });
      
      // Gender distribution chart
      const genderDistributionCtx = document.getElementById('genderDistributionChart').getContext('2d');
      new Chart(genderDistributionCtx, {
        type: 'pie',
        data: {
          labels: ['Male', 'Female', 'Other'],
          datasets: [{
            data: [
              <?= $people_data ? $people_data['male_count'] : 0 ?>, 
              <?= $people_data ? $people_data['female_count'] : 0 ?>, 
              <?= $people_data ? $people_data['other_count'] : 0 ?>
            ],
            backgroundColor: [
              'rgba(54, 162, 235, 0.5)',
              'rgba(255, 99, 132, 0.5)',
              'rgba(255, 206, 86, 0.5)'
            ],
            borderColor: [
              'rgba(54, 162, 235, 1)',
              'rgba(255, 99, 132, 1)',
              'rgba(255, 206, 86, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });
      
      // Occupation chart
      const occupationCtx = document.getElementById('occupationChart').getContext('2d');
      new Chart(occupationCtx, {
        type: 'bar',
        data: {
          labels: [
            <?php
            if ($occupation_result && $occupation_result->num_rows > 0) {
                $occupation_result->data_seek(0);
                $occupations = [];
                $occupation_counts = [];
                $count = 0;
                
                while ($occupation = $occupation_result->fetch_assoc()) {
                    if ($count < 5) {
                        $occupations[] = "'" . addslashes($occupation['occupation']) . "'";
                        $occupation_counts[] = $occupation['count'];
                        $count++;
                    }
                }
                
                echo implode(', ', $occupations);
            }
            ?>
          ],
          datasets: [{
            label: 'Number of People',
            data: [
              <?php
              if (!empty($occupation_counts)) {
                  echo implode(', ', $occupation_counts);
              }
              ?>
            ],
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
      
      // Education chart
      const educationCtx = document.getElementById('educationChart').getContext('2d');
      new Chart(educationCtx, {
        type: 'horizontalBar',
        data: {
          labels: [
            <?php
            if ($education_result && $education_result->num_rows > 0) {
                $education_result->data_seek(0);
                $education_levels = [];
                $education_counts = [];
                
                while ($education = $education_result->fetch_assoc()) {
                    $education_levels[] = "'" . addslashes($education['education_level']) . "'";
                    $education_counts[] = $education['count'];
                }
                
                echo implode(', ', $education_levels);
            }
            ?>
          ],
          datasets: [{
            label: 'Number of People',
            data: [
              <?php
              if (!empty($education_counts)) {
                  echo implode(', ', $education_counts);
              }
              ?>
            ],
            backgroundColor: 'rgba(153, 102, 255, 0.5)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              beginAtZero: true
            }
          }
        }
      });
      
      // Household size chart
      const householdSizeCtx = document.getElementById('householdSizeChart').getContext('2d');
      new Chart(householdSizeCtx, {
        type: 'pie',
        data: {
          labels: ['Single Person', 'Small (2-4)', 'Medium (5-7)', 'Large (8+)'],
          datasets: [{
            data: [
              <?= $household_stats['single_person'] ?>,
              <?= $household_stats['small_family'] ?>,
              <?= $household_stats['medium_family'] ?>,
              <?= $household_stats['large_family'] ?>
            ],
            backgroundColor: [
              'rgba(54, 162, 235, 0.5)',
              'rgba(75, 192, 192, 0.5)',
              'rgba(255, 159, 64, 0.5)',
              'rgba(255, 99, 132, 0.5)'
            ],
            borderColor: [
              'rgba(54, 162, 235, 1)',
              'rgba(75, 192, 192, 1)',
              'rgba(255, 159, 64, 1)',
              'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });
    } -->
  </script>
  
  <style>
    /* Tab Navigation Styles */
    .report-tabs {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
    }
    
    .tab-btn {
      background: none;
      border: none;
      padding: 10px 20px;
      margin: 0 5px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 500;
      color: #555;
      border-radius: 5px 5px 0 0;
      transition: all 0.3s ease;
    }
    
    .tab-btn:hover {
      background-color: #f5f5f5;
      color: var(--primary-color);
    }
    
    .tab-btn.active {
      background-color: var(--primary-color);
      color: white;
    }
    
    /* Tab Content Styles */
    .report-tab-content {
      display: none;
    }
    
    .report-tab-content.active {
      display: block;
    }
    
    /* Report Grid Layout */
    .report-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
    
    .report-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 20px;
    }
    
    .wide-card {
      grid-column: span 2;
    }
    
    /* Chart Containers */
    .chart-container {
      height: 300px;
      position: relative;
      margin-bottom: 15px;
    }
    
    .mini-chart-container {
      height: 100px;
      position: relative;
      margin-top: 10px;
    }
    
    /* Legend Styles */
    .age-pyramid-legend,
    .household-size-legend {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      margin-top: 15px;
    }
    
    .legend-item {
      display: flex;
      align-items: center;
      font-size: 0.9em;
    }
    
    .legend-color {
      width: 15px;
      height: 15px;
      border-radius: 3px;
      margin-right: 5px;
    }
    
    /* Detailed Stats Styles */
    .household-detailed-stats,
    .education-stats {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    
    .household-stat,
    .education-stat {
      display: flex;
      justify-content: space-between;
      padding: 5px 0;
      border-bottom: 1px solid #eee;
    }
    
    .stat-label {
      font-weight: 500;
      color: #555;
    }
    
    .stat-value {
      font-weight: 600;
      color: var(--primary-color);
    }
    
    /* Education Levels List */
    .education-levels {
      margin-top: 15px;
    }
    
    .education-levels h4 {
      margin-bottom: 10px;
      font-size: 1em;
      color: #555;
    }
    
    .education-levels ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .education-levels li {
      display: flex;
      justify-content: space-between;
      padding: 5px 0;
      border-bottom: 1px solid #f5f5f5;
    }
    
    /* Occupation List */
    .occupation-list {
      margin-top: 15px;
    }
    
    .occupation-item {
      display: flex;
      justify-content: space-between;
      padding: 5px 0;
      border-bottom: 1px solid #f5f5f5;
    }
    
    .occupation-name {
      font-weight: 500;
    }
    
    .occupation-count {
      color: var(--primary-color);
      font-weight: 600;
    }
    
    /* Households Table */
    .households-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .households-table th,
    .households-table td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    
    .households-table th {
      background-color: #f5f5f5;
      font-weight: 600;
      color: #555;
    }
    
    .households-table tr:hover {
      background-color: #f9f9f9;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
      .report-grid {
        grid-template-columns: 1fr;
      }
      
      .wide-card {
        grid-column: span 1;
      }
      
      .report-tabs {
        flex-wrap: wrap;
      }
      
      .tab-btn {
        margin-bottom: 5px;
      }
    }
    
    /* Original Styles */
    .registered-count {
      display: block;
      font-size: 0.9em;
      color: #444;
      margin-top: 5px;
    }
    
    .coverage-text {
      font-size: 0.85em;
      color: #666;
    }
    
    .progress-container {
      width: 100%;
      height: 6px;
      background-color: #e0e0e0;
      border-radius: 3px;
      margin: 8px 0;
      overflow: hidden;
    }
    
    .progress-bar {
      height: 100%;
      background-color: var(--primary-color);
      border-radius: 3px;
    }
    
    .data-details {
      margin-top: 10px;
    }
    
    .gender-stats, .age-stats {
      margin-top: 10px;
      width: 100%;
    }
    
    .gender-stat, .age-stat {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      margin-bottom: 8px;
    }
    
    .gender-label, .age-label {
      width: 40%;
      font-size: 0.9em;
    }
    
    .gender-count, .age-count {
      width: 30%;
      font-weight: 500;
      text-align: right;
      font-size: 0.9em;
    }
    
    .gender-percent {
      width: 30%;
      text-align: right;
      font-size: 0.85em;
      color: #666;
    }
    
    .age-bar-container {
      width: 30%;
      height: 8px;
      background-color: #e0e0e0;
      border-radius: 4px;
      overflow: hidden;
    }
    
    .age-bar {
      height: 100%;
      background-color: var(--primary-color);
      border-radius: 4px;
    }
    
    .children-bar {
      background-color: #4caf50;
    }
    
    .household-stats {
      margin-top: 10px;
    }
    
    .household-stat {
      display: flex;
      justify-content: space-between;
      margin-bottom: 5px;
    }
    
    .gender-icon {
      background-color: #9c27b0;
    }
    
    .age-icon {
      background-color: #4caf50;
    }
  </style>
</section>

      <!-- Village Committee Section -->
      <section class="data-section" id="committee">
        <div class="container">
          <h2 class="section-title">Village Headman & Committee</h2>
          <div class="committee-container">
            <?php
            // Headman Section
            if ($committee_data['headman']):
                $headman = $committee_data['headman'];
                $profile_picture = !empty($headman['profile_picture']) ? $headman['profile_picture'] : 'res/image/avatar.svg';
                $first_name = htmlspecialchars($headman['first_name']);
                $middle_name = htmlspecialchars($headman['middle_name']);
                $last_name = htmlspecialchars($headman['last_name']);
                ?>
                <div class="committee-card headman-card">
                  <div class="profile-image" style="background-image: url('<?= $profile_picture ?>');"></div>
                  <h3><?= $first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name ?></h3>
                  <p class="position">Village Headman</p>
                  <p>Serving since <?= date('Y', strtotime($headman['created_at'])) ?></p>
                  <a href="#contact" class="btn btn-small">Contact: <?= $headman['phone_no']?></a>
                </div>
            <?php else: ?>
                <div class="committee-card headman-card">
                  <div class="profile-image"></div>
                  <h3>No Representative Assigned</h3>
                  <p class="position">Village Headman</p>
                  <p><em>No representative assigned yet.</em></p>
                </div>
            <?php endif; ?>

            <!-- Other Committee Members -->
            <div class="committee-members">
                <?php
                $positions_display = [
                    'secretary' => 'Secretary',
                    'treasurer' => 'Treasurer',
                    'health coordinator' => 'Health Coordinator',
                    'education officer' => 'Education Officer'
                ];

                foreach ($positions_display as $key => $title):
                    if ($committee_data[$key]):  // Check if a representative is assigned
                        $rep = $committee_data[$key];
                        $profile_picture = !empty($rep['profile_picture']) ? $rep['profile_picture'] : 'res/image/avatar.svg';
                        $first_name = htmlspecialchars($rep['first_name']);
                        $middle_name = htmlspecialchars($rep['middle_name']);
                        $last_name = htmlspecialchars($rep['last_name']);
                        ?>
                        <div class="committee-card <?= strtolower(str_replace(' ', '-', $title)) ?>-card">
                          <div class="profile-image" style="background-image: url('<?= $profile_picture ?>');"></div>
                          <h3><?= $first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name ?></h3>
                          <p class="position"><?= htmlspecialchars($title) ?></p>
                          <a href="#contact" class="btn btn-small">Contact: <?= $rep['phone_no']?></a>
                        </div>
                    <?php else: ?>
                        <div class="committee-card <?= strtolower(str_replace(' ', '-', $title)) ?>-card">
                          <div class="profile-image"></div>
                          <h3>No Representative Assigned</h3>
                          <p class="position"><?= $title ?></p>
                          <p><em>No representative assigned yet.</em></p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
          </div>
        </div>
      </section>


      <!-- Contact Section -->
      <section class="contact-section alt-bg" style="padding: 60px 0;" id="contact">
        <div class="container">
          <h2 class="section-title">Contact Information</h2>
          <div class="contact-details">
            <a href="https://maps.google.com/?q=123+Main+Street,+Village+Center" target="_blank" class="contact-card">
              <div class="card-icon">
                <i class="fas fa-map-marker-alt"></i>
              </div>
              <h3>Address</h3>
              <p>Village Administration Office</p>
              <p>25.526582, 91.817353 East Khasi Hills, Meghalaya 793005</p>
              
            </a>
            <a href="tel:1234567890" class="contact-card">
              <div class="card-icon">
                <i class="fas fa-phone-alt"></i>
              </div>
              <h3>Phone</h3>
              <p>Office: 9612931809</p>
              <p>Emergency: 1234567890</p>
              
            </a>
            <a href="mailto:info@villagedb.example" class="contact-card">
              <div class="card-icon">
                <i class="fas fa-envelope"></i>
              </div>
              <h3>Email</h3>
              <p>elminglangstieh@gmail.com</p>
              <p>elminglangstieh@gmail.com</p>
              <!-- <span class="contact-action">Send Email <i class="fas fa-arrow-right"></i></span> -->
            </a>
            <div class="contact-card">
              <div class="card-icon">
                <i class="fas fa-clock"></i>
              </div>
              <h3>Office Hours</h3>
              <p>Monday - Friday: 9am - 5pm</p>
              <p>Saturday: 9am - 12pm</p>
              
            </div>
          </div>
        </div>

        <style>
          .contact-card {
            display: block;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            padding-bottom: 50px;
          }
          
          .contact-card .card-icon {
            background-color: var(--primary-color);
            color: white;
            font-size: 24px;
          }
          
          .contact-card .card-icon i {
            font-size: 28px;
          }
          
          .contact-action {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: var(--primary-light);
            color: var(--primary-color);
            padding: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
          }
          
          .contact-card:hover .contact-action {
            background-color: var(--primary-color);
            color: white;
          }
          
          .contact-card:hover {
            transform: translateY(-10px);
          }
          
          .contact-card i {
            transition: transform 0.3s ease;
          }
          
          .contact-card:hover i.fa-arrow-right {
            transform: translateX(5px);
          }
        </style>
      </section>

      <!-- Notices Section -->
      <section class="data-section" id="notices">
        <div class="container">
          <h2 class="section-title">Notices</h2>
            <div class="notices-container">
                <?php
                if ($notice_result->num_rows > 0) {
                    // Output data of each row
                    while($row = $notice_result->fetch_assoc()) {
                        echo '<div class="notice-card">';
                        echo '    <h3>' . htmlspecialchars($row['title']) . '</h3>';  
                        echo '    <i>' . htmlspecialchars($row['category']) . '</i>';  
                        echo '    <p class="date">' . date('F j, Y', strtotime($row['created_at'])) . '</p>';
                        echo '    <p>' . htmlspecialchars($row['content']) . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No notices available.</p>';
                }
                ?>
            </div>
        </div>
      </section>

      <!-- schemes Section -->
      <section class="data-section" id="schemes">
        <div class="container">
          <h2 class="section-title">Recent Government Schemes</h2>
            <div class="notices-container">
                <?php
                  if ($schemes_result->num_rows > 0) {
                      // Output data of each row
                      while($scheme = $schemes_result->fetch_assoc()) {
                          echo '<div class="notice-card">';
                          echo '    <h3>' . htmlspecialchars($scheme['title']) . '</h3>';
                          echo '    <p class="department"><strong>Department:</strong> ' . htmlspecialchars($scheme['department']) . '</p>';
                          echo '    <p class="description">' . htmlspecialchars(substr($scheme['description'], 0, 150)) . '...</p>';
                          echo '    <p class="benefits"><strong>Benefits:</strong> ' . htmlspecialchars(substr($scheme['benefits'], 0, 100)) . '...</p>';
                          echo '    <p class="dates"><strong>Valid till:</strong> ' . ($scheme['end_date'] ? date('F j, Y', strtotime($scheme['end_date'])) : 'Ongoing') . '</p>';
                          echo '</div>';
                      }
                  } else {
                      echo '<p>No schemes available.</p>';
                  }
                  $conn->close();
                ?>
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <a href="schemes.php" class="btn btn-primary">View All Schemes</a>
            </div>
        </div>
      </section>

    </main>

    <!-- Footer -->
    <footer>
      <div class="container">
        <div class="footer-content">
          <div class="footer-logo">
            <h2>VillageDB</h2>
            <p>Empowering Our Village with Data</p>
          </div>
          <div class="footer-links">
            <h3>Quick Links</h3>
            <ul> 
              <li><a href="#home">Home</a></li>
              <li><a href="#population">Population</a></li>
              <li><a href="#houses">Houses</a></li>
              <li><a href="#committee">Committee</a></li>
              <li><a href="#contact">Contact</a></li>
            </ul>
          </div>
        </div>
        <div class="copyright">
          <p>&copy; 2025 Village Database Management System.by elming.</p>
        </div>
      </div>
    </footer>
    <!-- Floating Profile Button -->
<script src="js/chart.js"></script>
  </body>
</html>
