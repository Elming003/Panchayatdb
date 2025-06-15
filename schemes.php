<?php
require_once('config/config.php');

// Fetch all active schemes for public view
$sql = "SELECT * FROM schemes WHERE status = 'active' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Government Schemes - Village Database</title>
    <link rel="stylesheet" href="res/css/scheme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <li><a href="home.php">Home</a></li>
                <li><a href="#population">Population</a></li>
                <li><a href="#committee">Committee</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="#notices">Notices</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Log Out</a></li>
                <?php endif; ?>
                <li><a href="<?= isset($_SESSION['user_id']) ? ($_SESSION['role'] === 'admin' ? 'admin-dashboard.php' : ($_SESSION['role'] === 'member' ? 'member-dashboard.php' : 'general-dashboard.php')) : 'signin.php' ?>"><i class="fas fa-user"></i></a></li>
            </ul>
        </nav>
    </div>
</header>

<!-- Hero Banner -->
<section class="hero" id="home">
    <div class="hero-content">
        <h2>Government Schemes</h2>
        <p>Explore various government schemes available for our village residents</p>
        <div class="hero-buttons">
            <a href="home.php" class="btn btn-secondary">← Back to Home</a>
        </div>
    </div>
</section>

<!-- Schemes Section -->
<section class="data-section" id="schemes">
    <div class="container">
        <h2 class="section-title">Available Government Schemes</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="schemes-container">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="scheme-card">
                        <div class="scheme-header">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <span class="department-badge"><?php echo htmlspecialchars($row['department']); ?></span>
                        </div>
                        
                        <div class="scheme-content">
                            <p class="scheme-description"><?php echo htmlspecialchars($row['description']); ?></p>
                            
                            <div class="scheme-details">
                                <div class="detail-section">
                                    <h4>📋 Eligibility Criteria</h4>
                                    <p><?php echo nl2br(htmlspecialchars($row['eligibility_criteria'])); ?></p>
                                </div>
                                
                                <div class="detail-section">
                                    <h4>🎁 Benefits</h4>
                                    <p><?php echo nl2br(htmlspecialchars($row['benefits'])); ?></p>
                                </div>
                                
                                <div class="detail-section">
                                    <h4>📝 How to Apply</h4>
                                    <p><?php echo nl2br(htmlspecialchars($row['application_process'])); ?></p>
                                </div>
                            </div>
                            
                            <div class="scheme-dates">
                                <p><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($row['start_date'])); ?></p>
                                <?php if ($row['end_date']): ?>
                                    <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($row['end_date'])); ?></p>
                                <?php else: ?>
                                    <p><strong>End Date:</strong> Ongoing</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <p>No active schemes available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

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
                    <li><a href="home.php">Home</a></li>
                    <li><a href="home.php#population">Population</a></li>
                    <li><a href="home.php#committee">Committee</a></li>
                    <li><a href="home.php#contact">Contact</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 Village Database Management System. by elming.</p>
        </div>
    </div>
</footer>

<style>
.schemes-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 30px;
    margin-top: 30px;
}

.scheme-card {
    background-color: var(--white);
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.scheme-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.scheme-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.scheme-header h3 {
    color: var(--primary-color);
    margin: 0;
    flex: 1;
}

.department-badge {
    background-color: var(--primary-light);
    color: var(--primary-dark);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.scheme-description {
    font-size: 1.1rem;
    margin-bottom: 25px;
    color: var(--text-color);
    line-height: 1.6;
}

.scheme-details {
    margin-bottom: 25px;
}

.detail-section {
    margin-bottom: 20px;
}

.detail-section h4 {
    color: var(--secondary-color);
    margin-bottom: 10px;
    font-size: 1rem;
}

.detail-section p {
    color: var(--text-light);
    line-height: 1.6;
}

.scheme-dates {
    border-top: 1px solid #eee;
    padding-top: 15px;
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.scheme-dates p {
    margin: 0;
    color: var(--text-light);
    font-size: 0.95rem;
}

@media (max-width: 768px) {
    .scheme-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .scheme-dates {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

</body>
</html>
