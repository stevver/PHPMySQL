<?php
// includes/footer.php
?>
<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5><i class="fas fa-dumbbell me-2"></i>SPORDISAAL</h5>
                <p>Registreerimissüsteem sporditreeningutele. Liitu meiega tervislikuma eluviisi nimel!</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Kontakt</h5>
                <p><i class="fas fa-map-marker-alt me-2"></i> Spordi 12, Tallinn</p>
                <p><i class="fas fa-phone me-2"></i> +372 55667788</p>
                <p><i class="fas fa-envelope me-2"></i> info@spordisaal.ee</p>
            </div>
            <div class="col-md-4">
                <h5>Kiirlingid</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-white"><i class="fas fa-angle-right me-2"></i>Avaleht</a></li>
                    <li><a href="trainings.php" class="text-white"><i class="fas fa-angle-right me-2"></i>Treeningud</a></li>
                    <li><a href="#" class="text-white"><i class="fas fa-angle-right me-2"></i>Kontakt</a></li>
                    <li><a href="#" class="text-white"><i class="fas fa-angle-right me-2"></i>Kasutajatingimused</a></li>
                </ul>
            </div>
        </div>
        <hr class="bg-light">
        <div class="text-center">
            <p>&copy; 2025 Spordisaali treeningute registreerimissüsteem. Kõik õigused kaitstud.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Demo functionality for the interface
    document.addEventListener('DOMContentLoaded', function() {
        // Training card interaction
        const trainingCards = document.querySelectorAll('.training-card');
        trainingCards.forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('button')) {
                    const link = card.querySelector('a[href*="training_id"]');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });
        });
        
        // Registration buttons
        const registerBtns = document.querySelectorAll('.register-training');
        registerBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const trainingId = this.dataset.trainingId;
                if (confirm('Kas olete kindel, et soovite sellele treeningule registreeruda?')) {
                    window.location.href = `register_training.php?training_id=${trainingId}`;
                }
            });
        });
        
        // Cancel registration buttons
        const cancelBtns = document.querySelectorAll('.cancel-registration');
        cancelBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const trainingId = this.dataset.trainingId;
                const registrationId = this.dataset.registrationId;
                if (confirm('Kas olete kindel, et soovite registreeringu tühistada?')) {
                    window.location.href = `cancel_registration.php?registration_id=${registrationId || trainingId}`;
                }
            });
        });
    });
</script>
</body>
</html>