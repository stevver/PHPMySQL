<?php
    include("../config.php"); 
    session_start();
    if (!isset($_SESSION['tuvastamine'])) {
        header('Location: ../login.php');
        exit();
    }
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
  </head>
  <body>
    <div class="container">
    <a class="btn btn-danger" href="../logout.php?logout">Logi välja</a>
    <h1>Admin leht</h1>
    <?php
      if(isset($_GET["muuda"]) && isset($_GET["id"])){
        $id = $_GET["id"];
        $kuvaparing = "SELECT * FROM sport2025 WHERE id=".$id."";
        $saada_paring = mysqli_query($yhendus, $kuvaparing);
        $rida = mysqli_fetch_assoc($saada_paring);

        // var_dump($rida);


        // $muuda_paring="UPDATE sport2025 SET fullname='Tommy Welbanddd', email='uus@sadf.ee',age='11',gender='apach',category='uisutamine' WHERE id = 3"; 
      }

      if(isset($_GET["salvesta_muudatus"]) && isset($_GET["id"])){
        $id = $_GET["id"];
        $fullname = $_GET["fullname"];
        $email = $_GET["email"];
        $age = $_GET["age"];
        $gender = $_GET["gender"];
        $category = $_GET["category"];

        $muuda_paring="UPDATE sport2025 SET fullname='".$fullname."', email='".$email."',age='".$age."',gender='".$gender."',category='".$category."' WHERE id = ".$id.""; 

        // var_dump($muuda_paring);

        $saada_paring = mysqli_query($yhendus, $muuda_paring);
        $tulemus = mysqli_affected_rows($yhendus);
        if($tulemus == 1){
              header('Location: index.php?msg=Andmed uuendatud');
            } else {
              echo "Andmeid ei uuendatud";
            }
      }


    ?>
    <form action="index.php" method="get">
      <input type="hidden" name="id" value="<?php !empty($rida['id']) ? print_r($rida['id']) : '' ?>"><br>
      Nimi: <input type="text" name="fullname" required 
      value="<?php !empty($rida['fullname']) ? print_r($rida['fullname']) : '' ?>" ><br>
      E-mail: <input type="email" name="email" required value="<?php !empty($rida['email']) ? print_r($rida['email']) : '' ?>"><br>
      Vanus: <input type="number" name="age" min="16" max="88" step="1" required value="<?php !empty($rida['age']) ? print_r($rida['age']) : '' ?>"><br>
      Sugu: <input type="text" name="gender" required value="<?php !empty($rida['gender']) ? print_r($rida['gender']) : '' ?>"><br>
      Spordiala: <input type="text" name="category" required value="<?php !empty($rida['category']) ? print_r($rida['category']) : '' ?>"><br>
      <?php  if(isset($_GET["muuda"]) && isset($_GET["id"])){ ?>
          <input type="submit" value="Salvesta_muudatus" name="salvesta_muudatus" class="btn btn-success"><br>
       <?php  } else { ?>
          <input type="submit" value="Salvesta" name="salvesta" class="btn btn-primary"><br>
       <?php  }  ?>
    </form>
    <?php
      if(isset($_GET['msg'])){
        echo "<div class='alert alert-success'>".$_GET['msg']."</div>";
      }
      if(isset($_GET["salvesta"]) && !empty($_GET["fullname"])){
       
        $fullname = $_GET["fullname"];
        $email = $_GET["email"];
        $age = $_GET["age"];
        $gender = $_GET["gender"];
        $category = $_GET["category"];

        $lisa_paring = "INSERT INTO sport2025 (fullname,email,age,gender,category) 
        VALUES ('".$fullname."', '".$email."', '".$age."', '".$gender."', '".$category."')";

        print_r($lisa_paring);

        $saada_paring = mysqli_query($yhendus, $lisa_paring);
        $tulemas = mysqli_affected_rows($yhendus);
        if($tulemas == 1){
          echo "Kirje edukalt lisatud";
        } else {
          echo "Kirjet ei lisatud";
        }
      }
    ?>

    <form action="index.php" method="get" class="py-4">
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
          <th scope="col">muuda</th>
          <th scope="col">kustuta</th>
        </tr>
      </thead>
      <tbody>

        <?php
           
            if(isset($_GET['kustuta']) && isset($_GET['id'])){
              $id = $_GET['id'];

              $kparing = "DELETE FROM sport2025 WHERE id=".$id."";
              $saada_paring = mysqli_query($yhendus, $kparing);
              $tulemus = mysqli_affected_rows($yhendus);
              if($tulemus == 1){
                header('Location: index.php?msg=Rida kustutatud');
              } else {
                echo "Kirjet ei kustutatud";
              }
            }

            // Leheküljenumber
            // Uudiseid ühel lehel
            $uudiseid_lehel = 50;

            $uudiseid_kokku_paring = "SELECT COUNT('id') FROM sport2025";
            $lehtede_vastus = mysqli_query($yhendus, $uudiseid_kokku_paring);
            $uudiseid_kokku = mysqli_fetch_array($lehtede_vastus);
            $lehti_kokku = $uudiseid_kokku[0];
            $lehti_kokku = ceil($lehti_kokku/$uudiseid_lehel);
            //var_dump($lehti_kokku);
            echo 'Lehekülgi kokku: '.$lehti_kokku.'<br>';
            echo 'Uudiseid lehel: '.$uudiseid_lehel.'<br>';

            // Kasutaja valik
            if (isset($_GET['page'])) {
              $leht = $_GET['page'];
            } else {
              $leht = 1;
            }
            // Millest näitamist alustatakse
            $start = ($leht-1)*$uudiseid_lehel;


            if(isset($_GET['otsi']) && !empty($_GET["otsi"])){
              $s = $_GET['otsi'];
              $cat = $_GET['cat'];
              echo "<tr><td span='6'>Otsing: ".$s."</td></tr>";
              $paring = 'SELECT * FROM sport2025 WHERE '.$cat.' LIKE "%'.$s.'%"';
              // var_dump($paring);
            } else {

              $paring = "SELECT * from sport2025 LIMIT $start, $uudiseid_lehel";
            }
            
            $saada_paring = mysqli_query($yhendus, $paring);
            // Võtab kõik read
            // assoc annab nimelised väljad
            while($rida = mysqli_fetch_assoc($saada_paring)){
                // print_r($rida); 
                // print_r($rida ['fullname']);

                echo "<tr>";
                echo "<td>".$rida['id']."</td>";
                echo "<td>".$rida['fullname']."</td>";
                echo "<td>".$rida['email']."</td>";
                echo "<td>".$rida['age']."</td>";
                echo "<td>".$rida['gender']."</td>";
                echo "<td>".$rida['category']."</td>";
                echo "<td>".$rida['reg_time']."</td>";
                echo "<td><a class='btn btn-success' href='?muuda&id=".$rida['id']."'>Muuda</a></td>";
                echo "<td><a class='btn btn-danger' href='?kustuta&id=".$rida['id']."'>Kustuta</a></td>";
                echo "</tr>";

            }

            // Kuvame lehekülje lingid
            $eelmine = $leht - 1;
            $jargmine = $leht + 1;
            if ($leht>1) {
              echo "<a  class='btn btn-primary m-1' href='?page=$eelmine'>Eelmine</a> ";
            }
            if ($lehti_kokku >= 1) {
              for ($i=1; $i<=$lehti_kokku ; $i++) { 
                if ($i==$leht) {
                  echo "<b><a  class='btn btn-primary m-1' href='?page=$i'>$i</a></b> ";
                } else {
                  echo "<a  class='btn btn-primary m-1' href='?page=$i'>$i</a> ";
                }
                
              }
            }
            if ($leht<$lehti_kokku) {
            echo "<a  class='btn btn-primary m-1' href='?page=$jargmine'>Järgmine</a> ";
            }
        ?>

      </tbody>
    </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
  </body>
</html>