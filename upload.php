<?php
include 'config.php';

$config = new Database();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? null;
    $programa = $_POST["programa"] ?? null;
    
    // Verificar que el correo y el programa sean válidos
    if (empty($email) || empty($programa)) {
        header("Location: index.php?error=Faltan datos obligatorios.");
        exit();
    }

    // Insertar datos en la base de datos
    $query = $config->conn->prepare("INSERT INTO archivos (email, programa) VALUES (?, ?)");
    $query->bind_param("ss", $email, $programa);
    
    if (!$query->execute()) {
        error_log("Error en INSERT: " . $query->error);
        header("Location: index.php?error=Error al guardar datos.");
        exit();
    }

    // Obtener el ID del usuario insertado
    $id_archivo = $config->conn->insert_id;

    // Manejo de archivos adjuntos
    $archivos = [
        "identificacion" => $_FILES["identificacion"],
        "acta_bachiller" => $_FILES["acta_bachiller"],
        "sisben" => $_FILES["sisben"],
        "abono" => $_FILES["abono"]
    ];

    foreach ($archivos as $campo => $archivo) {
        if (!empty($archivo["tmp_name"])) {
            $archivoTmp = $archivo["tmp_name"];
            $archivoMime = mime_content_type($archivoTmp);
            $archivoBinario = file_get_contents($archivoTmp);

            // Validar formatos permitidos
            $formatos_permitidos = ["application/pdf", "image/png", "image/jpeg"];
            if (!in_array($archivoMime, $formatos_permitidos)) {
                header("Location: index.php?error=Formato no permitido.");
                exit();
            }

            // Actualizar el archivo en la base de datos
            $query = $config->conn->prepare("UPDATE archivos SET $campo = ? WHERE id = ?");
            $query->bind_param("bi", $archivoBinario, $id_archivo);
            $query->send_long_data(0, $archivoBinario);

            if (!$query->execute()) {
                error_log("Error al subir $campo: " . $query->error);
                header("Location: index.php?error=Error al subir $campo.");
                exit();
            }
        }
    }

    // Redirigir con SweetAlert2
    header("Location: index.php?success=1");
    exit();
}
?>