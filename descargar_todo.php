<?php
include 'db.php';
$db = new Database();

if (!isset($_GET['email']) || empty($_GET['email'])) {
    die("Debe proporcionar un correo electrónico válido.");
}

$email = $_GET['email'];
$result = $db->conn->query("SELECT identificacion, acta_bachiller, sisben, abono FROM archivos WHERE email = '$email'");

if ($row = $result->fetch_assoc()) {
    $zip = new ZipArchive();
    $zip_file = "archivos_$email.zip";

    if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
        foreach ($row as $tipo => $contenido) {
            if ($contenido) {
                // Detectar el tipo de archivo
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->buffer($contenido);

                // Mapear MIME types a extensiones
                $extensiones_permitidas = [
                    "application/pdf" => "pdf",
                    "image/png" => "png",
                    "image/jpeg" => "jpg",
                    "image/jpg" => "jpg"
                ];

                // Validar si el archivo tiene un tipo permitido
                if (array_key_exists($mime_type, $extensiones_permitidas)) {
                    $extension = $extensiones_permitidas[$mime_type];
                    $zip->addFromString("$tipo.$extension", $contenido);
                }
            }
        }
        
        $zip->close();
        
        // Forzar la descarga del archivo ZIP
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=$zip_file");
        header("Content-Length: " . filesize($zip_file));
        readfile($zip_file);

        // Eliminar el archivo ZIP después de la descarga
        unlink($zip_file);
    } else {
        echo "Error al crear el archivo ZIP.";
    }
} else {
    echo "No se encontraron archivos para el usuario.";
}
?>