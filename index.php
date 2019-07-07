<?php

echo "<h1>PHP Sample</h1>";
 
$connection = mysqli_connect("db", "db_user", "supersecret", "php_sample");
 
if (!$connection) {
    echo "- ERROR conectando a la base de datos";
    echo "<br /><br />";
    exit;
}
 
echo "- Conectado a la base de datos correctamente.";
echo "<br /><br />";

echo "- Información de la instalación: ";
echo "<br />";

echo phpinfo();

?>