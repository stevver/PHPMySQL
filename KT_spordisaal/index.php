<?php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Tänane kuupäev
$today = date('d.m.Y');

$upcomingTrainings = getUpcomingTrainings($conn, $today);
?>

<section class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold">Registreeri end sporditreeningutele</h1>
        <p class="lead">Vali endale sobiv treening ja tee esimene samm tervislikuma eluviisi poole</p>
        <a href="trainings.php" class="btn btn-primary btn-lg mt-3">
            <i class="fas fa-search me-2"></i>Sirvi treeninguid
        </a>
    </div>
</section>

<div class="container my-5">
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-running"></i>
                </div>
                <h3><?= count($upcomingTrainings) ?></h3>
                <p>Eesolevat treeningut</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3><?= getTotalRegistrations($conn, $today) ?></h3>
                <p>Aktiivset registreeringut</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3><?= getTotalUsers($conn) ?></h3>
                <p>Aktiivset kasutajat</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3><?= getTrainingsToday($conn, $today) ?></h3>
                <p>Täna toimuvat treeningut</p>
            </div>
        </div>
    </div>
    
    <section class="mb-5">
        <h2 class="mb-4"><i class="fas fa-calendar-alt me-3"></i>Eesolevad treeningud</h2>
        
        <div class="row">
            <?php foreach(array_slice($upcomingTrainings, 0, 3) as $training): 
                $registeredCount = getRegisteredCount($conn, $training['id']);
                $percentage = ($registeredCount / $training['max_participants']) * 100;
                $spotsLeft = $training['max_participants'] - $registeredCount;
                
                $statusClass = $spotsLeft > 5 ? 'bg-success' : ($spotsLeft > 0 ? 'bg-warning' : 'bg-danger');
                $statusText = $spotsLeft > 5 ? "Vabu kohti: $spotsLeft" : ($spotsLeft > 0 ? "Vähe vabu kohti" : "Täis");
            ?>
            <div class="col-md-4">
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
        
        <div class="text-center mt-4">
            <a href="trainings.php" class="btn btn-outline-primary">
                <i class="fas fa-list me-2"></i>Vaata kõiki treeninguid
            </a>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>