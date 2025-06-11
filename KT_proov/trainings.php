<?php
// trainings.php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Tänane kuupäev
$today = date('d.m.Y');

$trainings = getUpcomingTrainings($conn, $today);
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-calendar-alt me-3"></i>Eesolevad treeningud</h1>
        <?php if($isAdmin): ?>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTrainingModal">
                <i class="fas fa-plus me-2"></i>Lisa uus treening
            </button>
        <?php endif; ?>
    </div>
    
    <?php if(isset($_GET['training_id'])): 
        $trainingId = $_GET['training_id'];
        $training = getTrainingData($conn, $trainingId);

        // Kontrolli, kas treening on lõppenud
        $now = date('Y-m-d H:i:s');
        if (!$isAdmin && isset($training['end_time']) && $training['end_time'] <= $now) {
            echo '<div class="alert alert-warning">See treening on lõppenud.</div>';
        } else {
            $registeredCount = getRegisteredCount($conn, $trainingId);
            $spotsLeft = $training['max_participants'] - $registeredCount;
            $percentage = ($registeredCount / $training['max_participants']) * 100;
            $canRegister = $spotsLeft > 0;
            $isRegistered = $userId ? isUserRegistered($conn, $userId, $trainingId) : false;
    ?>
    <div class="training-details mb-5">
        <div class="row">
            <div class="col-md-8">
                <h2><?= htmlspecialchars($training['title']) ?></h2>
                <div class="d-flex justify-content-between mb-3">
                    <p class="mb-0"><i class="far fa-calendar me-2"></i><?= date('d.m.Y', strtotime($training['date'])) ?></p>
                    <p class="mb-0"><i class="far fa-clock me-2"></i>
                        <?= date('H:i', strtotime($training['time'])) ?> - 
                        <?= isset($training['end_time']) ? date('H:i', strtotime($training['end_time'])) : date('H:i', strtotime($training['time']) + $training['duration'] * 60) ?>
                    </p>
                </div>
                <p><i class="fas fa-user-tie me-2"></i>Juhendaja: <?= htmlspecialchars($training['instructor']) ?></p>
                <p><?= htmlspecialchars($training['description']) ?></p>
                
                <div class="progress mb-3">
                    <div class="progress-bar <?= $spotsLeft > 5 ? 'bg-success' : ($spotsLeft > 0 ? 'bg-warning' : 'bg-danger') ?>" 
                         role="progressbar" 
                         style="width: <?= $percentage ?>%;" 
                         aria-valuenow="<?= $percentage ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100"></div>
                </div>
                <p>Registreerunud: <strong><?= $registeredCount ?>/<?= $training['max_participants'] ?></strong> (<?= $spotsLeft ?> vaba kohta)</p>
                
                <?php if($userId): ?>
                    <?php if($isRegistered): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>Olete sellele treeningule registreerunud
                        </div>
                        <?php if(canCancelTraining($training['date'], $training['time'])): ?>
                            <button class="btn btn-danger cancel-registration" 
                                    data-training-id="<?= $trainingId ?>">
                                <i class="fas fa-times me-2"></i>Tühista registreering
                            </button>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Registreeringut saab tühistada kuni 2 tundi enne treeningu algust
                            </div>
                        <?php endif; ?>
                    <?php elseif($canRegister): ?>
                        <button class="btn btn-primary register-training" 
                                data-training-id="<?= $trainingId ?>">
                            <i class="fas fa-user-plus me-2"></i>Registreeri treeningule
                        </button>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>Treening on täis, uutele registreeringutele pole vabu kohti
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Registreerimiseks peate olema sisse logitud. 
                        <a href="login.php" class="alert-link">Logi sisse</a> või 
                        <a href="register.php" class="alert-link">registreeri kasutaja</a>.
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Treeningu andmed</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Asukoht:</span>
                                <span>Peetri spordisaal</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Kestus:</span>
                                <span><?= $training['duration'] ?> minutit</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Hind:</span>
                                <span>Tasuta</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } endif; ?>
    
    <div class="row">
        <?php foreach($trainings as $training): 
            $registeredCount = getRegisteredCount($conn, $training['id']);
            $percentage = ($registeredCount / $training['max_participants']) * 100;
            $spotsLeft = $training['max_participants'] - $registeredCount;
            
            $statusClass = $spotsLeft > 5 ? 'bg-success' : ($spotsLeft > 0 ? 'bg-warning' : 'bg-danger');
            $statusText = $spotsLeft > 5 ? "Vabu kohti: $spotsLeft" : ($spotsLeft > 0 ? "Vähe vabu kohti" : "Täis");
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="training-card">
                <div class="training-header">
                    <?= htmlspecialchars($training['title']) ?>
                    <span class="badge <?= $statusClass ?> float-end"><?= $statusText ?></span>
                </div>
                <div class="training-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span><i class="far fa-calendar me-2"></i><?= date('d.m.Y', strtotime($training['date'])) ?></span>
                        <span><i class="far fa-clock me-2"></i><?= date('H:i', strtotime($training['time'])) ?></span>
                    </div>
                    <p class="mb-3"><i class="fas fa-user-tie me-2"></i>Juhendaja: <?= htmlspecialchars($training['instructor']) ?></p>
                    
                    <div class="progress">
                        <div class="progress-bar <?= $statusClass ?>" role="progressbar" 
                             style="width: <?= $percentage ?>%;" 
                             aria-valuenow="<?= $percentage ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between small mb-3">
                        <span>Registreerunud: <?= $registeredCount ?>/<?= $training['max_participants'] ?></span>
                        <span><?= round($percentage) ?>% täis</span>
                    </div>
                    
                    <a href="trainings.php?training_id=<?= $training['id'] ?>" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-user-plus me-2"></i>Registreeri
                    </a>
                    <a href="trainings.php?training_id=<?= $training['id'] ?>" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-info-circle me-2"></i>Detailid
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if($isAdmin): ?>
<!-- Add Training Modal -->
<div class="modal fade" id="addTrainingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Lisa uus treening</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="admin.php?action=add_training" method="POST">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="trainingName" class="form-label">Treeningu nimi *</label>
                            <input type="text" class="form-control" id="trainingName" name="title" required>
                        </div>
                        <div class="col-md-6">
                            <label for="trainingType" class="form-label">Treeningu tüüp *</label>
                            <select class="form-select" id="trainingType" name="type" required>
                                <option value="" selected disabled>Vali tüüp</option>
                                <option value="Jooga">Jooga</option>
                                <option value="Jõutreening">Jõutreening</option>
                                <option value="Kardio">Kardio</option>
                                <option value="Funktsionaalne">Funktsionaalne treening</option>
                                <option value="Pilates">Pilates</option>
                                <option value="Poksimine">Poksimine</option>
                                <option value="Ujumine">Ujumine</option>
                                <option value="Jõusaal">Jõusaal</option>
                                <option value="Jalgpall">Jalgpall</option>
                                <option value="Korvpall">Korvpall</option>
                                <option value="Muu">Muu</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="trainingDate" class="form-label">Kuupäev *</label>
                            <input type="date" class="form-control" id="trainingDate" name="date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label for="startTime" class="form-label">Algusaeg *</label>
                            <input type="time" class="form-control" id="startTime" name="time" required>
                        </div>
                        <div class="col-md-3">
                            <label for="duration" class="form-label">Kestus (min) *</label>
                            <input type="number" class="form-control" id="duration" name="duration" min="15" max="180" value="60" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="instructor" class="form-label">Juhendaja *</label>
                            <input type="text" class="form-control" id="instructor" name="instructor" required>
                        </div>
                        <div class="col-md-6">
                            <label for="maxParticipants" class="form-label">Maksimaalne osalejate arv *</label>
                            <input type="number" class="form-control" id="maxParticipants" name="max_participants" min="1" value="15" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="trainingDescription" class="form-label">Kirjeldus</label>
                        <textarea class="form-control" id="trainingDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Katkesta</button>
                    <button type="submit" class="btn btn-primary">Salvesta treening</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>