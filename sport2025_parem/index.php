<?php
require_once("config.php");
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HKHK sport 2025</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
</head>
<body>
  <div class="container">
    <h1>HKHK sport 2025</h1>
    <a href="login.php" class="btn btn-primary">Admin</a>

    <form method="get" action="index.php" class="py-4">
      <input type="text" name="otsi">
      <select name="cat">
        <option value="fullname">Nimi</option>
        <option value="category">Spordiala</option>
      </select>
      <input type="submit" value="Otsi...">
    </form>

    <table class="table table-striped">
      <thead>
        <tr>
          <th scope="col">id</th>
          <th scope="col">firstname</th>
          <th scope="col">email</th>
          <th scope="col">age</th>
          <th scope="col">gender</th>
          <th scope="col">category</th>
          <th scope="col">reg_time</th>
        </tr>
      </thead>
      <tbody>

<?php
  // Mitu kirjet lehel
  $kirjeidLehel = 50;

  // Loeme kokku kirjed
  $kogusParing = "SELECT COUNT(id) FROM sport2025";
  $kogusTulemus = mysqli_query($yhendus, $kogusParing);
  $kogusRida = mysqli_fetch_array($kogusTulemus);
  $kokkuKirjeid = $kogusRida[0];

  // Arvutame lehtede koguarv
  $lehti = ceil($kokkuKirjeid / $kirjeidLehel);
  echo "Lehek\xC3\xBClgi kokku: $lehti<br>";
  echo "Kirjeid lehel: $kirjeidLehel<br>";

  // Milline leht on valitud
  $aktiivneLeht = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $algus = ($aktiivneLeht - 1) * $kirjeidLehel;

  // Kui on otsing, siis koostame otsingu päringu
  if (!empty($_GET['otsi'])) {
    $otsing = mysqli_real_escape_string($yhendus, $_GET['otsi']);
    $veerg = mysqli_real_escape_string($yhendus, $_GET['cat']);
    echo "<tr><td colspan='7'>Otsing: $otsing</td></tr>";
    $sql = "SELECT * FROM sport2025 WHERE $veerg LIKE '%$otsing%'";
  } else {
    // Kui ei ole otsingut, siis loeme tavalise lehe andmed
    $sql = "SELECT * FROM sport2025 LIMIT $algus, $kirjeidLehel";
  }

  $andmed = mysqli_query($yhendus, $sql);

  while ($rida = mysqli_fetch_assoc($andmed)) {
    echo "<tr>";
    echo "<td>{$rida['id']}</td>";
    echo "<td>{$rida['fullname']}</td>";
    echo "<td>{$rida['email']}</td>";
    echo "<td>{$rida['age']}</td>";
    echo "<td>{$rida['gender']}</td>";
    echo "<td>{$rida['category']}</td>";
    echo "<td>{$rida['reg_time']}</td>";
    echo "</tr>";
  }

  // Lehe navigatsioon
  $eelmine = $aktiivneLeht - 1;
  $jargmine = $aktiivneLeht + 1;

  if ($aktiivneLeht > 1) {
    echo "<a class='btn btn-primary m-1' href='?page=$eelmine'>Eelmine</a> ";
  }

  for ($i = 1; $i <= $lehti; $i++) {
    if ($i == $aktiivneLeht) {
      echo "<b><a class='btn btn-primary m-1' href='?page=$i'>$i</a></b> ";
    } else {
      echo "<a class='btn btn-primary m-1' href='?page=$i'>$i</a> ";
    }
  }

  if ($aktiivneLeht < $lehti) {
    echo "<a class='btn btn-primary m-1' href='?page=$jargmine'>J\xC3\xA4rgmine</a> ";
  }
?>

      </tbody>
    </table>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>