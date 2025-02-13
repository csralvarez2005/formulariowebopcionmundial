<?php
include 'config.php';
include 'programas.php';

$config = new Database();
$conn = $config->conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["email"]) || empty($_POST["email"])) {
        header("Location: index.php?error=Debe ingresar un correo electrónico");
        exit();
    }

    if (!isset($_POST["programa"]) || empty($_POST["programa"])) {
        header("Location: index.php?error=Debe seleccionar un programa de interés");
        exit();
    }

    $email = $_POST["email"];
    $programa = $_POST["programa"];

    // Validar que el programa seleccionado esté en la lista permitida
    if (!in_array($programa, $programas)) {
        header("Location: index.php?error=Programa seleccionado no válido");
        exit();
    }

    $archivos = ["identificacion", "acta_bachiller", "sisben", "abono"];
    $datosArchivos = [];
    $extensionesPermitidas = ["pdf", "png", "jpg", "jpeg"];

    foreach ($archivos as $archivo) {
        if (!isset($_FILES[$archivo]) || $_FILES[$archivo]["error"] !== UPLOAD_ERR_OK) {
            if ($archivo == "abono") {
                $datosArchivos[$archivo] = null;
                continue;
            }
            header("Location: index.php?error=Debe subir todos los archivos requeridos");
            exit();
        }

        $extension = strtolower(pathinfo($_FILES[$archivo]["name"], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            header("Location: index.php?error=Formato no permitido en $archivo");
            exit();
        }

        $datosArchivos[$archivo] = file_get_contents($_FILES[$archivo]["tmp_name"]);
    }

    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO archivos (email, programa, identificacion, acta_bachiller, sisben, abono) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $email, $programa, $datosArchivos["identificacion"], $datosArchivos["acta_bachiller"], $datosArchivos["sisben"], $datosArchivos["abono"]);

    if ($stmt->execute()) {
        header("Location: index.php?success=1");
    } else {
        header("Location: index.php?error=Hubo un problema al subir los archivos");
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>