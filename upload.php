<?php
include 'db.php';

$db = new Database();
$conn = $db->conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar correo electrónico
    if (!isset($_POST["email"]) || empty($_POST["email"])) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe ingresar un correo electrónico.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'index.html';
            });
        </script>";
        exit();
    }

    $email = $_POST["email"];
    $archivos = ["identificacion", "acta_bachiller", "sisben", "abono"];
    $datosArchivos = [];

    // Extensiones permitidas
    $extensionesPermitidas = ["pdf", "png", "jpg", "jpeg"];

    foreach ($archivos as $archivo) {
        if (!isset($_FILES[$archivo]) || $_FILES[$archivo]["error"] !== UPLOAD_ERR_OK) {
            if ($archivo == "abono") {
                $datosArchivos[$archivo] = null; // Permitir que "abono" sea opcional
                continue;
            }
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe subir todos los archivos requeridos.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'index.html';
                });
            </script>";
            exit();
        }

        // Validar extensión del archivo
        $extension = strtolower(pathinfo($_FILES[$archivo]["name"], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Formato no permitido en $archivo. Solo se permiten PDF, PNG, JPG o JPEG.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'index.html';
                });
            </script>";
            exit();
        }

        // Obtener contenido del archivo
        $datosArchivos[$archivo] = file_get_contents($_FILES[$archivo]["tmp_name"]);
    }

    // Preparar consulta para guardar archivos en la base de datos
    $stmt = $conn->prepare("INSERT INTO archivos (email, identificacion, acta_bachiller, sisben, abono) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $datosArchivos["identificacion"], $datosArchivos["acta_bachiller"], $datosArchivos["sisben"], $datosArchivos["abono"]);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '¡Registro exitoso!',
                text: 'Tus archivos han sido guardados correctamente.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'index.html?success=1';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al subir los archivos.',
                confirmButtonText: 'Intentar de nuevo'
            }).then(() => {
                window.location.href = 'index.html';
            });
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>