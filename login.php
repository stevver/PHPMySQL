<?php
    include("config.php"); 
    session_start();
    if (isset($_SESSION['tuvastamine'])) {
        header('admin/');
        exit();
    }
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
<style>
   .btn-color{
  background-color: #0e1c36;
  color: #fff;
  
}

.profile-image-pic{
  height: 200px;
  width: 200px;
  object-fit: cover;
}



.cardbody-color{
  background-color: #ebf2fa;
}

a{
  text-decoration: none;
}

</style>  
</head>
  <body>
  <div class="container">
    <div class="row">
      <div class="col-md-6 offset-md-3">
      <?php
            //echo password_hash('admin', PASSWORD_DEFAULT);
            // Login
            if (!empty($_POST['user']) && !empty($_POST['password'])) {
                $login = $_POST['user'];
                $str = $_POST['password'];
                
                $paring = "SELECT * FROM users";
                $saada_paring = mysqli_query($yhendus, $paring);
                $rida = mysqli_fetch_assoc($saada_paring);
                $s = $rida["password"];
                
                if ($login == 'admin' && password_verify($str, $s)) {
                    echo "Tere admin";
                    $_SESSION['tuvastamine'] = 'misiganes';
                    header('Location: admin/');
                    exit();
                } else {
                    echo "Vale kasutajanimi või parool";
                }
            }
            ?>

        <h2 class="text-center text-dark mt-5">Logi sisse</h2>
        <div class="card my-5">
          <form class="card-body cardbody-color p-lg-5" method="post">
            <div class="mb-3">
              <input type="text" class="form-control" name="user" aria-describedby="emailHelp"
                placeholder="Kasutaja">
            </div>
            <div class="mb-3">
              <input type="password" class="form-control" name="password" placeholder="Parool">
            </div>
            <div class="text-center">
              <button type="submit" class="btn btn-color px-5 mb-3 w-100">Login</button>
              <a href="index.php" class="btn btn-secondary px-5 mb-3 w-100">Tagasi</a>
            </div>
            <div class="form-group d-md-flex">
                <div class="w-50">
                    <label class="checkbox-wrap checkbox-primary">Mäleta mind
                        <input type="checkbox" checked name="remember">
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