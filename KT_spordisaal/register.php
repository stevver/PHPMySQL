<?php
// register.php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Kui kasutaja on juba sisse loginud, suuname edasi
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$success = false;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $personalId = trim($_POST['personal_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Valideerimine
    if(empty($firstName)) $errors['first_name'] = 'Eesnimi on kohustuslik';
    if(empty($lastName)) $errors['last_name'] = 'Perekonnanimi on kohustuslik';
    if(empty($personalId)) $errors['personal_id'] = 'Isikukood on kohustuslik';
    if(empty($email)) $errors['email'] = 'E-posti aadress on kohustuslik';
    if(empty($password)) $errors['password'] = 'Parool on kohustuslik';
    if($password !== $confirmPassword) $errors['confirm_password'] = 'Paroolid ei kattu';
    
    if(!empty($personalId) && !validatePersonalId($personalId)) {
        $errors['personal_id'] = 'Vigane isikukoodi vorming';
    }
    
    if(!empty($email) && !validateEmail($email)) {
        $errors['email'] = 'Vigane e-posti aadressi vorming';
    }
    
    // Kontrollime, kas e-posti aadress on juba kasutusel
    if(empty($errors['email'])) {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if($stmt->fetchColumn() > 0) {
            $errors['email'] = 'Selle e-posti aadressiga kasutaja on juba olemas';
        }
    }
    
    // Kontrollime, kas isikukood on juba kasutusel
    if(empty($errors['personal_id'])) {
        $sql = "SELECT COUNT(*) FROM users WHERE personal_id = :personal_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':personal_id', $personalId);
        $stmt->execute();
        if($stmt->fetchColumn() > 0) {
            $errors['personal_id'] = 'Selle isikukoodiga kasutaja on juba olemas';
        }
    }
    
    // Kui vigu pole, loome kasutaja
    if(empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (first_name, last_name, personal_id, email, password) 
                VALUES (:first_name, :last_name, :personal_id, :email, :password)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':personal_id', $personalId);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        
        if($stmt->execute()) {
            $success = true;
        } else {
            $errors['general'] = 'Registreerimisel tekkis viga. Palun proovi uuesti.';
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="text-center"><i class="fas fa-user-plus me-2"></i>Loo uus kasutaja</h2>
                </div>
                <div class="card-body">
                    <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Kasutaja on edukalt loodud! <a href="login.php" class="alert-link">Logi sisse</a>
                        </div>
                    <?php else: ?>
                        <?php if(isset($errors['general'])): ?>
                            <div class="alert alert-danger"><?= $errors['general'] ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">Eesnimi *</label>
                                    <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                           id="first_name" name="first_name" value="<?= htmlspecialchars($firstName ?? '') ?>">
                                    <?php if(isset($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Perekonnanimi *</label>
                                    <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                           id="last_name" name="last_name" value="<?= htmlspecialchars($lastName ?? '') ?>">
                                    <?php if(isset($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="personal_id" class="form-label">Isikukood *</label>
                                <input type="text" class="form-control <?= isset($errors['personal_id']) ? 'is-invalid' : '' ?>" 
                                       id="personal_id" name="personal_id" value="<?= htmlspecialchars($personalId ?? '') ?>"
                                       maxlength="11" placeholder="11-kohaline isikukood">
                                <?php if(isset($errors['personal_id'])): ?>
                                    <div class="invalid-feedback"><?= $errors['personal_id'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posti aadress *</label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
                                <?php if(isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Parool *</label>
                                    <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                           id="password" name="password">
                                    <?php if(isset($errors['password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Kinnita parool *</label>
                                    <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                           id="confirm_password" name="confirm_password">
                                    <?php if(isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    Nõustun <a href="#">kasutajatingimustega</a>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus me-2"></i>Loo kasutaja
                            </button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p>Juba kasutaja? <a href="login.php">Logi sisse</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>