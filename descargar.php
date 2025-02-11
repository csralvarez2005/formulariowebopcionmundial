<?php
include 'db.php';

if (isset($_GET["id"]) && isset($_GET["tipo"])) {
    $id = $_GET["id"];
    $tipo = $_GET["tipo"];

    // Validar que el tipo de archivo existe en la base de datos
    $columnas_validas = ["identificacion", "acta_bachiller", "sisben", "abono"];
    if (!in_array($tipo, $columnas_validas)) {
        die("Tipo de archivo no válido.");
    }

    // Obtener el archivo desde la BD
    $stmt = $conn->prepare("SELECT email, $tipo FROM archivos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($email, $contenido);
    $stmt->fetch();

    if ($contenido) {
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename={$tipo}_{$email}.pdf");
        echo $contenido;
    } else {
        echo "Archivo no encontrado.";
    }
}

$stmt->close();
$conn->close();
?>