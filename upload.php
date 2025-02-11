<?php
include 'db.php';

$db = new Database();
$conn = $db->conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    $archivos = ["identificacion", "acta_bachiller", "sisben"];
    $datosArchivos = [];

    // Validar y obtener archivos obligatorios
    foreach ($archivos as $archivo) {
        if (!isset($_FILES[$archivo]) || $_FILES[$archivo]["error"] !== UPLOAD_ERR_OK) {
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
        $datosArchivos[$archivo] = file_get_contents($_FILES[$archivo]["tmp_name"]);
    }

    // Verificar si se subió el archivo "abono"
    $abono = null;
    if (isset($_FILES["abono"]) && $_FILES["abono"]["error"] === UPLOAD_ERR_OK) {
        $abono = file_get_contents($_FILES["abono"]["tmp_name"]);
    }

    // Preparar consulta (permitiendo null en "abono")
    $stmt = $conn->prepare("INSERT INTO archivos (email, identificacion, acta_bachiller, sisben, abono) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $datosArchivos["identificacion"], $datosArchivos["acta_bachiller"], $datosArchivos["sisben"], $abono);

    if ($stmt->execute()) {
        header("Location: index.html?success=1");
        exit();
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