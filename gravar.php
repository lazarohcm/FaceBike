<?php
$nome = $_POST['nome'];
$criador = $_POST['criador'];
$dias = $_POST['dias'];
$hora = $_POST['hora'];
$linestring = $_POST['linestring'];
$linestring = str_replace(',',' ', $linestring);
$linestring = str_replace('] [',',', $linestring);
$linestring = str_replace('[','', $linestring);
$linestring = str_replace(']','', $linestring);


// Connecting, selecting database
$dbconn = pg_connect("host=localhost dbname=FACEBIKE user=postgres password=123456")
        or die('Could not connect: ' . pg_last_error());

// Performing SQL query
//$query = 'SELECT ST_AsText(caminho) as caminho FROM rotas';
$query = "INSERT INTO rotas (nome, dias, horario, criador, caminho) VALUES('$nome', '$dias', '$hora', '$criador', ST_GeomFromText('LINESTRING($linestring)', 3857))";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());
echo 'success';

//// Closing connection
pg_close($dbconn);
?>
