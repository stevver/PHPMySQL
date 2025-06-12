<?php
  include("../config.php");
  session_start();
  if (!isset($_SESSION['tuvastamine'])) {
    header("Location: ../login.php");
    exit();
  }
?>
<!DOCTYPE html>
<html lang="et">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Leht</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>
<body>
<div class="container mt-3">
  <a href="../logout.php?logout" class="btn btn-danger">Logi välja</a>
  <h1 class="mt-3">Admin paneel</h1>

  <?php
    // Kui "muuda" on saadetud koos id-ga, toome andmed
    if (isset($_GET['muuda']) && isset($_GET['id'])) {
      $id = intval($_GET['id']);
      $result = mysqli_query($yhendus, "SELECT * FROM sport2025 WHERE id=$id");
      $muudetav = mysqli_fetch_assoc($result);
    }

    // Salvestame muudatused, kui "salvesta_muudatus" on saadetud
    if (isset($_GET['salvesta_muudatus']) && isset($_GET['id'])) {
      $id = intval($_GET['id']);
      $fullname = $_GET['fullname'];
      $email = $_GET['email'];
      $age = intval($_GET['age']);
      $gender = $_GET['gender'];
      $category = $_GET['category'];

      $uuenda_sql = "UPDATE sport2025 SET fullname='$fullname', email='$email', age=$age, gender='$gender', category='$category' WHERE id=$id";
      mysqli_query($yhendus, $uuenda_sql);

      if (mysqli_affected_rows($yhendus) == 1) {
        header("Location: index.php?msg=Andmed on uuendatud");
        exit();
      } else {
        echo "<div class='alert alert-warning'>Midagi läks valesti andmete uuendamisel</div>";
      }
    }
  ?>

  <form method="get" action="index.php" class="mb-4">
    <input type="hidden" name="id" value="<?php echo $muudetav['id'] ?? ''; ?>">
    <div class="mb-2">
      <label>Nimi:</label>
      <input type="text" name="fullname" class="form-control" required value="<?php echo $muudetav['fullname'] ?? ''; ?>">
    </div>
    <div class="mb-2">
      <label>Email:</label>
      <input type="email" name="email" class="form-control" required value="<?php echo $muudetav['email'] ?? ''; ?>">
    </div>
    <div class="mb-2">
      <label>Vanus:</label>
      <input type="number" name="age" class="form-control" min="16" max="88" required value="<?php echo $muudetav['age'] ?? ''; ?>">
    </div>
    <div class="mb-2">
      <label>Sugu:</label>
      <input type="text" name="gender" class="form-control" required value="<?php echo $muudetav['gender'] ?? ''; ?>">
    </div>
    <div class="mb-2">
      <label>Spordiala:</label>
      <input type="text" name="category" class="form-control" required value="<?php echo $muudetav['category'] ?? ''; ?>">
    </div>
    <input type="submit" name="<?php echo isset($muudetav) ? 'salvesta_muudatus' : 'salvesta'; ?>" value="<?php echo isset($muudetav) ? 'Salvesta muudatus' : 'Salvesta'; ?>" class="btn btn-<?php echo isset($muudetav) ? 'success' : 'primary'; ?>">
  </form>

  <?php
    // Kuvame teate, kui msg olemas
    if (isset($_GET['msg'])) {
      echo "<div class='alert alert-success'>".htmlspecialchars($_GET['msg'])."</div>";
    }

    // Uue kirje lisamine
    if (isset($_GET['salvesta']) && !empty($_GET['fullname'])) {
      $fullname = $_GET['fullname'];
      $email = $_GET['email'];
      $age = intval($_GET['age']);
      $gender = $_GET['gender'];
      $category = $_GET['category'];

      $insert_sql = "INSERT INTO sport2025 (fullname, email, age, gender, category) VALUES ('$fullname', '$email', $age, '$gender', '$category')";
      mysqli_query($yhendus, $insert_sql);

      if (mysqli_affected_rows($yhendus) == 1) {
        echo "<div class='alert alert-info'>Kirje lisatud edukalt</div>";
      } else {
        echo "<div class='alert alert-danger'>Lisamine ebaõnnestus</div>";
      }
    }
  ?>

  <!-- Otsingu vorm -->
  <form method="get" class="my-4">
    <input type="text" name="otsi" placeholder="Otsi" class="form-control d-inline w-25">
    <select name="cat" class="form-select d-inline w-25">
      <option value="fullname">Nimi</option>
      <option value="category">Spordiala</option>
    </select>
    <input type="submit" value="Otsi..." class="btn btn-secondary">
  </form>

  <?php
    // Kirje kustutamine
    if (isset($_GET['kustuta']) && isset($_GET['id'])) {
      $id = intval($_GET['id']);
      mysqli_query($yhendus, "DELETE FROM sport2025 WHERE id=$id");

      if (mysqli_affected_rows($yhendus) == 1) {
        header("Location: index.php?msg=Rida kustutatud");
        exit();
      } else {
        echo "<div class='alert alert-danger'>Kustutamine ebaõnnestus</div>";
      }
    }

    // Lehe jaotamine (pagination)
    $perPage = 50;
    $kokku_sql = mysqli_query($yhendus, "SELECT COUNT(id) FROM sport2025");
    $kokku_arv = mysqli_fetch_row($kokku_sql)[0];
    $lehti = ceil($kokku_arv / $perPage);

    $leht = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $algus = ($leht - 1) * $perPage;

    if (isset($_GET['otsi']) && !empty($_GET['otsi'])) {
      $otsi = $_GET['otsi'];
      $cat = $_GET['cat'];
      echo "<div>Otsingutulemus: <strong>$otsi</strong></div>";
      $valik = "SELECT * FROM sport2025 WHERE $cat LIKE '%$otsi%'";
    } else {
      $valik = "SELECT * FROM sport2025 LIMIT $algus, $perPage";
    }

    $vastus = mysqli_query($yhendus, $valik);
  ?>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nimi</th>
        <th>Email</th>
        <th>Vanus</th>
        <th>Sugu</th>
        <th>Spordiala</th>
        <th>Registreeritud</th>
        <th>Muuda</th>
        <th>Kustuta</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($rida = mysqli_fetch_assoc($vastus)) : ?>
        <tr>
          <td><?= $rida['id'] ?></td>
          <td><?= $rida['fullname'] ?></td>
          <td><?= $rida['email'] ?></td>
          <td><?= $rida['age'] ?></td>
          <td><?= $rida['gender'] ?></td>
          <td><?= $rida['category'] ?></td>
          <td><?= $rida['reg_time'] ?></td>
          <td><a href="?muuda&id=<?= $rida['id'] ?>" class="btn btn-success">Muuda</a></td>
          <td><a href="?kustuta&id=<?= $rida['id'] ?>" class="btn btn-danger">Kustuta</a></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <!-- Lehe nupud -->
  <div>
    <?php if ($leht > 1): ?>
      <a href="?page=<?= $leht - 1 ?>" class="btn btn-outline-primary">Eelmine</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $lehti; $i++): ?>
      <a href="?page=<?= $i ?>" class="btn btn<?= ($i == $leht) ? '' : '-outline' ?>-primary"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($leht < $lehti): ?>
      <a href="?page=<?= $leht + 1 ?>" class="btn btn-outline-primary">Järgmine</a>
    <?php endif; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>