<?php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if(!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Profiili muutmine
$profileSuccess = '';
$profileError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    if (empty($firstName) || empty($lastName) || empty($email)) {
        $profileError = 'Kõik väljad on kohustuslikud!';
    } elseif (!validateEmail($email)) {
        $profileError = 'Vigane e-posti aadress!';
    } else {
        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?");
        $stmt->execute([$firstName, $lastName, $email, $userId]);
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $profileSuccess = 'Profiil edukalt uuendatud!';
    }
}

require_once 'includes/header.php';

$user = getUserData($conn, $userId);
$registrations = getUserRegistrations($conn, $userId);
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Minu profiil</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-user fa-3x text-primary"></i>
                        </div>
                    </div>
                    <h5 class="card-title text-center"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                    <ul class="list-group list-group-flush mt-3">
                        <li class="list-group-item">
                            <i class="fas fa-envelope me-2"></i> <?= htmlspecialchars($user['email']) ?>
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-id-card me-2"></i> <?= htmlspecialchars($user['personal_id']) ?>
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-calendar-alt me-2"></i> Liitunud: <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit me-2"></i>Muuda profiili
                        </button>
                    </div>
                    <?php if($profileSuccess): ?>
                        <div class="alert alert-success mt-3"><?= $profileSuccess ?></div>
                    <?php elseif($profileError): ?>
                        <div class="alert alert-danger mt-3"><?= $profileError ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-list me-3"></i>Minu registreeringud</h2>
                <a href="trainings.php" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>Registreeri uuele treeningule
                </a>
            </div>
            
            <?php if(empty($registrations)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Teil pole ühtegi aktiivset registreeringut. 
                    <a href="trainings.php" class="alert-link">Sirvi treeninguid</a> ja registreeri endale sobivale.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Treening</th>
                                <th>Tüüp</th>
                                <th>Kuupäev</th>
                                <th>Aeg</th>
                                <th>Registreeritud</th>
                                <th>Staatus</th>
                                <th>Tegevused</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($registrations as $reg): 
                                $trainingDate = new DateTime($reg['date']);
                                $currentDate = new DateTime("2025-06-11");
                                $isPast = $trainingDate < $currentDate;
                                $canCancel = !$isPast && canCancelTraining($reg['date'], $reg['time']);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($reg['title']) ?></td>
                                <td><?= htmlspecialchars($reg['type']) ?></td>
                                <td><?= date('d.m.Y', strtotime($reg['date'])) ?></td>
                                <td><?= date('H:i', strtotime($reg['time'])) ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($reg['registered_at'])) ?></td>
                                <td>
                                    <?php if($reg['status'] == 'active'): ?>
                                        <?php if($isPast): ?>
                                            <span class="badge bg-secondary status-badge">Lõppenud</span>
                                        <?php else: ?>
                                            <span class="badge bg-success status-badge">Aktiivne</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning status-badge">Tühistatud</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($reg['status'] == 'active' && !$isPast): ?>
                                        <?php if($canCancel): ?>
                                            <button class="btn btn-sm btn-danger cancel-registration" 
                                                    data-registration-id="<?= $reg['id'] ?>">
                                                <i class="fas fa-times me-1"></i>Tühista
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">Ei saa enam tühistada</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Profiili muutmise modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProfileModalLabel">Muuda profiili</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sulge"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="update_profile" value="1">
        <div class="mb-3">
            <label class="form-label">Eesnimi</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Perekonnanimi</label>
            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">E-post</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sulge</button>
        <button type="submit" class="btn btn-primary">Salvesta muudatused</button>
      </div>
    </form>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>