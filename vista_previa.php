<?php
include 'config.php';

$config = new Database();

if (isset($_GET['id']) && isset($_GET['tipo'])) {
    $id = intval($_GET['id']);
    $tipo = $_GET['tipo'];

    // Validar que el tipo de archivo existe en la base de datos
    $columnas_validas = [
        "Identificación" => "identificacion",
        "Acta de Bachiller" => "acta_bachiller",
        "Sisbén" => "sisben",
        "Abono" => "abono"
    ];

    if (!isset($columnas_validas[$tipo])) {
        die("Tipo de archivo no válido.");
    }

    $columna = $columnas_validas[$tipo];

    // Obtener el archivo
    $query = $config->conn->prepare("SELECT $columna FROM archivos WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $query->bind_result($contenido);
    $query->fetch();
    $query->close();

    if ($contenido) {
        header("Content-Type: application/pdf");
        echo $contenido;
    } else {
        echo "Archivo no encontrado.";
    }
}
?>