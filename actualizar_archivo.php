<?php
include 'db.php';

$db = new Database();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"]) && isset($_POST["archivoTipo"]) && isset($_FILES["nuevoArchivo"])) {
    $id = intval($_POST["id"]);
    $archivoTipo = $_POST["archivoTipo"];
    $nuevoArchivo = file_get_contents($_FILES["nuevoArchivo"]["tmp_name"]);

    // Verificar si el tipo de archivo es válido
    $columnas_validas = ["identificacion", "acta_bachiller", "sisben", "abono"];

    if (!in_array($archivoTipo, $columnas_validas)) {
        die("Tipo de archivo no válido.");
    }

    // Actualizar el archivo en la base de datos
    $query = $db->conn->prepare("UPDATE archivos SET $archivoTipo = ? WHERE id = ?");
    $query->bind_param("si", $nuevoArchivo, $id);

    if ($query->execute()) {
        echo "<script>alert('Archivo actualizado correctamente.'); window.location.href='listar.php';</script>";
    } else {
        echo "Error al actualizar el archivo.";
    }

    $query->close();
}
?>