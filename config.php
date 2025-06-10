<?php
	try {
		// Andmebaasi ja kasutaja andmed
		$db_server = 'localhost';
		$db_andmebaas = 'sport';
		$db_kasutaja = 'sheinsaar';
		$db_salasona = 'Sport_1234';

		// Ühendus
		$yhendus = mysqli_connect($db_server, $db_kasutaja, $db_salasona, $db_andmebaas);
	} catch (mysqli_sql_exception $e) {
		die('Probleem andmebaasiga: ' . $e->getMessage());
	}
?>