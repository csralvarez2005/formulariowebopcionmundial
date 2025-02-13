<?php
include 'config.php';

$config = new Database();
$conn = $config->conn;

if (isset($_GET["id"]) && isset($_GET["tipo"])) {
    $id = intval($_GET["id"]);
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
        // Intentar determinar el tipo de archivo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($contenido);

        // Mapear MIME types a extensiones
        $extensiones_permitidas = [
            "application/pdf" => "pdf",
            "image/png" => "png",
            "image/jpeg" => "jpg",
            "image/jpg" => "jpg"
        ];

        if (!array_key_exists($mime_type, $extensiones_permitidas)) {
            die("Formato de archivo no soportado.");
        }

        $extension = $extensiones_permitidas[$mime_type];

        // Configurar las cabeceras para la descarga
        header("Content-Type: $mime_type");
        header("Content-Disposition: attachment; filename={$tipo}_{$email}.$extension");
        
        // Enviar el contenido del archivo
        echo $contenido;
    } else {
        echo "Archivo no encontrado.";
    }
    
    $stmt->close();
}

$conn->close();
?>