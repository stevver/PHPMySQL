<?php
// login.php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Kui kasutaja on juba sisse loginud, suuname edasi
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if(empty($email) || empty($password)) {
        $error = 'Palun täida kõik väljad';
    } else {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && password_verify($password, $user['password'])) {
            // Logime kasutaja sisse
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            
            // "Mäleta mind" funktsionaalsus
            if($remember) {
                $cookieValue = base64_encode($user['id'] . ':' . password_hash($user['password'], PASSWORD_DEFAULT));
                setcookie('remember_me', $cookieValue, time() + (86400 * 30), "/"); // 30 päeva
            }
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Vale e-posti aadress või parool';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="text-center"><i class="fas fa-sign-in-alt me-2"></i>Logi sisse</h2>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posti aadress</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Parool</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Jäta mind meelde</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Logi sisse
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Pole veel kasutajat? <a href="register.php">Registreeru siin</a></p>
                        <p><a href="#">Unustasid parooli?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>