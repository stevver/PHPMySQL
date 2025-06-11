<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spordisaali Treeningud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding-top: 56px;
        }
        
        .navbar {
            background-color: var(--secondary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.8), rgba(0,0,0,0.7));
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .training-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .training-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.15);
        }
        
        .training-header {
            background-color: var(--primary);
            color: white;
            padding: 15px;
            font-weight: bold;
        }
        
        .training-body {
            padding: 20px;
        }
        
        .progress {
            height: 10px;
            margin: 15px 0;
        }
        
        .stats-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .badge-custom {
            font-size: 0.9rem;
            padding: 6px 12px;
            font-weight: normal;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-danger {
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        footer {
            background-color: var(--secondary);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
        
        .admin-section {
            background-color: #f0f5ff;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .user-card {
            border-left: 4px solid var(--primary);
        }
        
        .training-details {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .participant-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
        
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .stats-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-dumbbell me-2"></i>SPORDISAAL
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i> Avaleht</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="trainings.php"><i class="fas fa-calendar-alt me-1"></i> Treeningud</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"><i class="fas fa-list me-1"></i> Minu registreeringud</a>
                        </li>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-1"></i> Haldus
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="admin.php?section=trainings"><i class="fas fa-running me-2"></i> Treeningud</a></li>
                                <li><a class="dropdown-item" href="admin.php?section=users"><i class="fas fa-users me-2"></i> Kasutajad</a></li>
                                <li><a class="dropdown-item" href="admin.php?section=registrations"><i class="fas fa-clipboard-list me-2"></i> Registreeringud</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin.php?section=stats"><i class="fas fa-chart-bar me-2"></i> Statistika</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-user-circle me-2"></i> Minu profiil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logi välja</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light me-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Logi sisse
                        </a>
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Registreeru
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>