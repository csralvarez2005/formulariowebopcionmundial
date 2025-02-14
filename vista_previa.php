<?php
include 'config.php';

$config = new Database();

if (isset($_GET["id"]) && isset($_GET["tipo"])) {
    $id = intval($_GET["id"]);
    $tipo = $_GET["tipo"];

    $columnas_validas = ["identificacion", "acta_bachiller", "sisben", "abono"];
    if (!in_array($tipo, $columnas_validas)) {
        die("Tipo de archivo no válido.");
    }

    $query = $config->conn->prepare("SELECT $tipo FROM archivos WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $query->store_result();

    if ($query->num_rows > 0) {
        $query->bind_result($archivo);
        $query->fetch();
        
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipo_mime = $finfo->buffer($archivo);

        header("Content-Type: $tipo_mime");
        echo $archivo;
    } else {
        echo "Archivo no encontrado.";
    }

    $query->close();
}
?>