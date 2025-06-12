<?php
// Andmebaasiühendus

// Andmebaasi parameetrid
$server = 'localhost';
$andmebaas = 'sport';
$kasutaja = 'sheinsaar';
$parool = 'Sport_1234';

// Püütakse ühendust luua
try {
	$yhendus = mysqli_connect($server, $kasutaja, $parool, $andmebaas);

	if (!$yhendus) {
		die("Ühendus ebaõnnestus: " . mysqli_connect_error());
	}

} catch (Exception $viga) {
	echo "Tekkis probleem andmebaasiga: " . $viga->getMessage();
	exit();
}
?>