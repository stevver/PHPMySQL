<?php
// admin.php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Tänane kuupäev
$today = date('d.m.Y');

// Kontrollime, kas kasutaja on admin
if(!isAdmin()) {
    header("Location: index.php");
    exit;
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $deleteId = (int)$_POST['delete_user_id'];
    if ($deleteId !== $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$deleteId]);
    }
    header("Location: admin.php?section=users");
    exit;
}

// Handle user edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user_id'])) {
    $editId = (int)$_POST['edit_user_id'];
    $firstName = trim($_POST['edit_first_name']);
    $lastName = trim($_POST['edit_last_name']);
    $email = trim($_POST['edit_email']);
    $role = $_POST['edit_role'] === 'admin' ? 'admin' : 'user';

    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, role=? WHERE id=?");
    $stmt->execute([$firstName, $lastName, $email, $role, $editId]);
    header("Location: admin.php?section=users");
    exit;
}

// Handle add training
$addTrainingError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_training'])) {
    $title = trim($_POST['title']);
    $type = trim($_POST['type']);
    $date = trim($_POST['date']);
    $time = trim($_POST['time']);
    $duration = trim($_POST['duration']);
    $max_participants = (int)$_POST['max_participants'];
    $instructor = trim($_POST['instructor']);
    $description = trim($_POST['description']);

    if (empty($title) || empty($type) || empty($date) || empty($time) || empty($duration) || empty($max_participants) || empty($instructor)) {
        $addTrainingError = 'Kõik väljad peale kirjelduse on kohustuslikud!';
    } else {
        $now = new DateTime();
        $trainingDateTime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$trainingDateTime || $trainingDateTime < $now) {
            $addTrainingError = 'Treeningu kuupäev ja kellaaeg ei tohi olla minevikus!';
        } else {
            $stmt = $conn->prepare("INSERT INTO trainings (title, type, date, time, duration, max_participants, instructor, description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $type, $date, $time, $duration, $max_participants, $instructor, $description]);
            header("Location: admin.php?section=trainings");
            exit;
        }
    }
}

// Handle training edit
$editTrainingError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_training_id'])) {
    $editId = (int)$_POST['edit_training_id'];
    $title = trim($_POST['edit_title']);
    $type = trim($_POST['edit_type']);
    $date = trim($_POST['edit_date']);
    $time = trim($_POST['edit_time']);
    $duration = trim($_POST['edit_duration']);
    $max_participants = (int)$_POST['edit_max_participants'];
    $instructor = trim($_POST['edit_instructor']);
    $description = trim($_POST['edit_description']);

    if (empty($title) || empty($type) || empty($date) || empty($time) || empty($duration) || empty($max_participants) || empty($instructor)) {
        $editTrainingError = 'Kõik väljad peale kirjelduse on kohustuslikud!';
    } else {
        $now = new DateTime();
        $trainingDateTime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        if (!$trainingDateTime || $trainingDateTime < $now) {
            $editTrainingError = 'Treeningu kuupäev ja kellaaeg ei tohi olla minevikus!';
        } else {
            $stmt = $conn->prepare("UPDATE trainings SET title=?, type=?, date=?, time=?, duration=?, max_participants=?, instructor=?, description=? WHERE id=?");
            $stmt->execute([$title, $type, $date, $time, $duration, $max_participants, $instructor, $description, $editId]);
            header("Location: admin.php?section=trainings");
            exit;
        }
    }
}

// Handle training delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_training_id'])) {
    $deleteId = (int)$_POST['delete_training_id'];
    $stmt = $conn->prepare("DELETE FROM trainings WHERE id=?");
    $stmt->execute([$deleteId]);
    header("Location: admin.php?section=trainings");
    exit;
}

// For osalejad modal
function getTrainingParticipants($conn, $trainingId) {
    $sql = "SELECT u.first_name, u.last_name, u.email
            FROM registrations r
            JOIN users u ON r.user_id = u.id
            WHERE r.training_id = ? AND r.status = 'active'";
    $stmt = $GLOBALS['conn']->prepare($sql);
    $stmt->execute([$trainingId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle registration delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_registration_id'])) {
    $deleteId = (int)$_POST['delete_registration_id'];
    $stmt = $conn->prepare("DELETE FROM registrations WHERE id = ?");
    $stmt->execute([$deleteId]);
    header("Location: admin.php?section=registrations");
    exit;
}

// Handle registration edit (status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_registration_id'])) {
    $editId = (int)$_POST['edit_registration_id'];
    $status = $_POST['edit_status'] === 'active' ? 'active' : 'cancelled';
    $stmt = $conn->prepare("UPDATE registrations SET status = ? WHERE id = ?");
    $stmt->execute([$status, $editId]);
    header("Location: admin.php?section=registrations");
    exit;
}

// Määrame aktiivse sektsiooni
$section = isset($_GET['section']) ? $_GET['section'] : 'trainings';

// Treeningute haldus
if($section === 'trainings') {
    $trainings = getUpcomingTrainings($conn, $today);
}

// Kasutajate haldus
if($section === 'users') {
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Registreeringute haldus
if($section === 'registrations') {
    $sql = "SELECT r.id, r.training_id, r.user_id, r.status, r.registered_at,
                   t.title AS training_title, t.date AS training_date, t.time AS training_time,
                   u.first_name, u.last_name, u.email
            FROM registrations r
            JOIN trainings t ON r.training_id = t.id
            JOIN users u ON r.user_id = u.id
            ORDER BY t.date DESC, t.time DESC, r.registered_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Statistika
if($section === 'stats') {
    $trainingCount = count(getUpcomingTrainings($conn, $today));
    $userCount = getTotalUsers($conn);
    $registrationCount = getTotalRegistrations($conn, $today);
    $trainingsToday = getTrainingsToday($conn, $today);
}

require_once 'includes/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4"><i class="fas fa-cog me-3"></i>Administraatori paneel</h1>
    
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $section === 'trainings' ? 'active' : '' ?>" 
               href="admin.php?section=trainings">
               <i class="fas fa-running me-2"></i>Treeningud
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $section === 'users' ? 'active' : '' ?>" 
               href="admin.php?section=users">
               <i class="fas fa-users me-2"></i>Kasutajad
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $section === 'registrations' ? 'active' : '' ?>" 
               href="admin.php?section=registrations">
               <i class="fas fa-list me-2"></i>Registreeringud
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $section === 'stats' ? 'active' : '' ?>" 
               href="admin.php?section=stats">
               <i class="fas fa-chart-bar me-2"></i>Statistika
            </a>
        </li>
    </ul>
    
    <?php if($section === 'trainings'): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-running me-2"></i>Treeningute haldus</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTrainingModal">
                <i class="fas fa-plus me-2"></i>Lisa uus treening
            </button>
        </div>
        <?php if($addTrainingError): ?>
            <div class="alert alert-danger"><?= $addTrainingError ?></div>
        <?php endif; ?>
        <?php if($editTrainingError): ?>
            <div class="alert alert-danger"><?= $editTrainingError ?></div>
        <?php endif; ?>
        <div class="row">
            <?php foreach($trainings as $training): 
                $registeredCount = getRegisteredCount($conn, $training['id']);
                $participants = getTrainingParticipants($conn, $training['id']);
            ?>
            <div class="col-md-6 mb-4">
                <div class="training-details">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h3><?= htmlspecialchars($training['title']) ?></h3>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><i class="far fa-calendar me-2"></i> <?= date('d.m.Y', strtotime($training['date'])) ?></p>
                            <p><i class="far fa-clock me-2"></i> <?= date('H:i', strtotime($training['time'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="fas fa-user-tie me-2"></i> <?= htmlspecialchars($training['instructor']) ?></p>
                            <p><i class="fas fa-users me-2"></i> <?= $registeredCount ?>/<?= $training['max_participants'] ?> osalejat</p>
                        </div>
                    </div>
                    
                    <?php if(!empty($training['description'])): ?>
                        <p><?= htmlspecialchars($training['description']) ?></p>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2 mt-3">
                        <!-- Muuda -->
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTrainingModal<?= $training['id'] ?>">
                            <i class="fas fa-edit me-2"></i>Muuda
                        </button>
                        <!-- Kustuta -->
                        <form method="post" style="display:inline;" onsubmit="return confirm('Kustuta see treening?');">
                            <input type="hidden" name="delete_training_id" value="<?= $training['id'] ?>">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-trash me-2"></i>Kustuta
                            </button>
                        </form>
                        <!-- Osalejad -->
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#participantsModal<?= $training['id'] ?>">
                            <i class="fas fa-list me-2"></i>Osalejad
                        </button>
                    </div>
                </div>
            </div>

            <!-- Muuda treeningut modal -->
            <div class="modal fade" id="editTrainingModal<?= $training['id'] ?>" tabindex="-1" aria-labelledby="editTrainingModalLabel<?= $training['id'] ?>" aria-hidden="true">
              <div class="modal-dialog">
                <form method="post" class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="editTrainingModalLabel<?= $training['id'] ?>">Muuda treeningut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sulge"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="edit_training_id" value="<?= $training['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Pealkiri</label>
                        <input type="text" name="edit_title" class="form-control" value="<?= htmlspecialchars($training['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tüüp</label>
                        <input type="text" name="edit_type" class="form-control" value="<?= htmlspecialchars($training['type']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kuupäev</label>
                        <input type="date" name="edit_date" class="form-control" value="<?= $training['date'] ?>" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Aeg</label>
                        <input type="time" name="edit_time" class="form-control" value="<?= $training['time'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kestus (minutit)</label>
                        <input type="number" name="edit_duration" class="form-control" value="<?= $training['duration'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Maksimaalne osalejate arv</label>
                        <input type="number" name="edit_max_participants" class="form-control" value="<?= $training['max_participants'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Juhendaja</label>
                        <input type="text" name="edit_instructor" class="form-control" value="<?= htmlspecialchars($training['instructor']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kirjeldus</label>
                        <textarea name="edit_description" class="form-control"><?= htmlspecialchars($training['description']) ?></textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sulge</button>
                    <button type="submit" class="btn btn-primary">Salvesta muudatused</button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Osalejad modal -->
            <div class="modal fade" id="participantsModal<?= $training['id'] ?>" tabindex="-1" aria-labelledby="participantsModalLabel<?= $training['id'] ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="participantsModalLabel<?= $training['id'] ?>">Treeningu osalejad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sulge"></button>
                  </div>
                  <div class="modal-body">
                    <?php if (count($participants) > 0): ?>
                      <ul class="list-group">
                        <?php foreach ($participants as $p): ?>
                          <li class="list-group-item">
                            <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?> (<?= htmlspecialchars($p['email']) ?>)
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php else: ?>
                      <div class="alert alert-info mb-0">Ühtegi osalejat pole.</div>
                    <?php endif; ?>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sulge</button>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Lisa uus treening modal -->
        <div class="modal fade" id="addTrainingModal" tabindex="-1" aria-labelledby="addTrainingModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form method="post" class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="addTrainingModalLabel">Lisa uus treening</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sulge"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="add_training" value="1">
                <div class="mb-3">
                    <label class="form-label">Pealkiri</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tüüp</label>
                    <input type="text" name="type" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kuupäev</label>
                    <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Aeg</label>
                    <input type="time" name="time" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kestus (minutit)</label>
                    <input type="number" name="duration" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Maksimaalne osalejate arv</label>
                    <input type="number" name="max_participants" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Juhendaja</label>
                    <input type="text" name="instructor" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kirjeldus</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sulge</button>
                <button type="submit" class="btn btn-primary">Lisa treening</button>
              </div>
            </form>
          </div>
        </div>
    <?php endif; ?>
    
    <?php if($section === 'users'): ?>
        <h2 class="mb-4"><i class="fas fa-users me-2"></i>Kasutajate haldus</h2>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nimi</th>
                        <th>E-post</th>
                        <th>Isikukood</th>
                        <th>Roll</th>
                        <th>Registreerunud</th>
                        <th>Tegevused</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['personal_id']) ?></td>
                        <td>
                            <span class="badge <?= $user['role'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                                <?= $user['role'] === 'admin' ? 'Administraator' : 'Kasutaja' ?>
                            </span>
                        </td>
                        <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <!-- Edit button triggers modal -->
                                <button class="btn btn-sm btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editUserModal<?= $user['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- Delete form -->
                                <?php if($user['id'] !== $_SESSION['user_id']): ?>
                                <form method="post" style="display:inline;" 
                                      onsubmit="return confirm('Kustuta kasutaja?');">
                                    <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <!-- Edit Modal -->
                            <div class="modal fade" id="editUserModal<?= $user['id'] ?>" tabindex="-1" aria-labelledby="editUserModalLabel<?= $user['id'] ?>" aria-hidden="true">
                              <div class="modal-dialog">
                                <form method="post" class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="editUserModalLabel<?= $user['id'] ?>">Muuda kasutajat</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sulge"></button>
                                  </div>
                                  <div class="modal-body">
                                    <input type="hidden" name="edit_user_id" value="<?= $user['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Eesnimi</label>
                                        <input type="text" name="edit_first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Perekonnanimi</label>
                                        <input type="text" name="edit_last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">E-post</label>
                                        <input type="email" name="edit_email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Roll</label>
                                        <select name="edit_role" class="form-select">
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Kasutaja</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administraator</option>
                                        </select>
                                    </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sulge</button>
                                    <button type="submit" class="btn btn-primary">Salvesta</button>
                                  </div>
                                </form>
                              </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if($section === 'registrations'): ?>
        <h2 class="mb-4"><i class="fas fa-list me-2"></i>Registreeringute haldus</h2>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Treening</th>
                        <th>Kuupäev</th>
                        <th>Kellaaeg</th>
                        <th>Kasutaja</th>
                        <th>E-post</th>
                        <th>Staatus</th>
                        <th>Registreeritud</th>
                        <th>Tegevused</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($registrations as $reg): ?>
                    <tr>
                        <td><?= htmlspecialchars($reg['training_title']) ?></td>
                        <td><?= date('d.m.Y', strtotime($reg['training_date'])) ?></td>
                        <td><?= date('H:i', strtotime($reg['training_time'])) ?></td>
                        <td><?= htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']) ?></td>
                        <td><?= htmlspecialchars($reg['email']) ?></td>
                        <td>
                            <span class="badge <?= $reg['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $reg['status'] === 'active' ? 'Aktiivne' : 'Tühistatud' ?>
                            </span>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($reg['registered_at'])) ?></td>
                        <td>
                            <!-- Edit status modal trigger -->
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRegistrationModal<?= $reg['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <!-- Delete form -->
                            <form method="post" style="display:inline;" onsubmit="return confirm('Kustuta registreering?');">
                                <input type="hidden" name="delete_registration_id" value="<?= $reg['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <!-- Edit Modal -->
                            <div class="modal fade" id="editRegistrationModal<?= $reg['id'] ?>" tabindex="-1" aria-labelledby="editRegistrationModalLabel<?= $reg['id'] ?>" aria-hidden="true">
                              <div class="modal-dialog">
                                <form method="post" class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="editRegistrationModalLabel<?= $reg['id'] ?>">Muuda registreeringu staatust</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sulge"></button>
                                  </div>
                                  <div class="modal-body">
                                    <input type="hidden" name="edit_registration_id" value="<?= $reg['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Staatus</label>
                                        <select name="edit_status" class="form-select">
                                            <option value="active" <?= $reg['status'] === 'active' ? 'selected' : '' ?>>Aktiivne</option>
                                            <option value="cancelled" <?= $reg['status'] === 'cancelled' ? 'selected' : '' ?>>Tühistatud</option>
                                        </select>
                                    </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sulge</button>
                                    <button type="submit" class="btn btn-primary">Salvesta</button>
                                  </div>
                                </form>
                              </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <?php if($section === 'stats'): ?>
        <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Süsteemi statistika</h2>
        
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-running"></i>
                    </div>
                    <h3><?= $trainingCount ?></h3>
                    <p>Eelolevat treeningut</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?= $userCount ?></h3>
                    <p>Kasutajat</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3><?= $registrationCount ?></h3>
                    <p>Aktiivset registreeringut</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <h3><?= $trainingsToday ?></h3>
                    <p>Täna toimuvat treeningut</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>