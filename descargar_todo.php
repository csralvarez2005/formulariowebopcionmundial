<?php
include 'db.php';
$db = new Database();
$email = $_GET['email'];
$result = $db->conn->query("SELECT identificacion, acta_bachiller, sisben, abono FROM archivos WHERE email = '$email'");

if ($row = $result->fetch_assoc()) {
    $zip = new ZipArchive();
    $zip_file = "archivos_$email.zip";

    if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
        foreach ($row as $tipo => $contenido) {
            if ($contenido) {
                $zip->addFromString("$tipo.pdf", $contenido);
            }
        }
        $zip->close();
        header("Content-Disposition: attachment; filename=$zip_file");
        readfile($zip_file);
        unlink($zip_file);
    }
}
?>