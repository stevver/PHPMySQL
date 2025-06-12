<?php
require_once("config.php");
session_start();

// Kui kasutaja on juba sisse loginud, suunatakse admin lehele
if (isset($_SESSION['tuvastamine'])) {
	header('Location: admin/');
	exit();
}
?>

<!DOCTYPE html>
<html lang="et">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sisselogimine</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
  <style>
    .btn-color {
      background-color: #123456;
      color: #f8f9fa;
      border-radius: 6px;
    }

    .cardbody-color {
      background-color: #e3eaf6;
      border-radius: 10px;
    }

    .profile-image-pic {
      height: 180px;
      width: 180px;
      object-fit: cover;
      border: 2px solid #123456;
    }

    a {
      text-decoration: underline;
      color: #0e1c36;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="row">
    <div class="col-md-6 offset-md-3">

      <?php
      // Kontrollitakse, kas vormi andmed on saadetud
      if (!empty($_POST['user']) && !empty($_POST['password'])) {
        $kasutajanimi = $_POST['user'];
        $parool = $_POST['password'];

        // Otsitakse admin kasutaja
        $kask = "SELECT * FROM users2 WHERE username='admin' LIMIT 1";
        $tulemus = mysqli_query($yhendus, $kask);

        if ($rida = mysqli_fetch_assoc($tulemus)) {
          // Kontrollitakse parooli vastavust
          if ($kasutajanimi === 'admin' && password_verify($parool, $rida['password'])) {
            $_SESSION['tuvastamine'] = 'jah';
            header('Location: admin/');
            exit();
          } else {
            echo '<div class="alert alert-danger">Vale kasutajanimi või parool</div>';
          }
        } else {
          echo '<div class="alert alert-danger">Kasutajat ei leitud</div>';
        }
      }
      ?>

      <h2 class="text-center text-dark mt-5">Logi sisse</h2>
      <div class="card my-5">
        <form class="card-body cardbody-color p-lg-5" method="post">
          <div class="mb-3">
            <input type="text" class="form-control" name="user" placeholder="Kasutajanimi">
          </div>
          <div class="mb-3">
            <input type="password" class="form-control" name="password" placeholder="Parool">
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-color px-5 mb-3 w-100">Logi sisse</button>
            <a href="index.php" class="btn btn-secondary px-5 mb-3 w-100">Tagasi avalehele</a>
          </div>
          <div class="form-group d-md-flex">
            <div class="w-50">
              <label class="checkbox-wrap checkbox-primary">Mäleta mind
                <input type="checkbox" name="remember" checked>
                <span class="checkmark"></span>
              </label>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>