<?php
include 'config.php';

$config = new Database();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"]) && isset($_POST["archivoTipo"]) && isset($_FILES["nuevoArchivo"])) {
    $id = intval($_POST["id"]);
    $archivoTipo = $_POST["archivoTipo"];
    $archivoTmp = $_FILES["nuevoArchivo"]["tmp_name"];
    $archivoNombre = $_FILES["nuevoArchivo"]["name"];
    $archivoTamaño = $_FILES["nuevoArchivo"]["size"];
    $archivoMime = mime_content_type($archivoTmp);
    
    // Extensiones y tipos MIME permitidos
    $extensiones_permitidas = [
        "application/pdf" => "pdf",
        "image/png" => "png",
        "image/jpeg" => "jpg",
        "image/jpg" => "jpg"
    ];

    // Validar tipo de archivo
    if (!array_key_exists($archivoMime, $extensiones_permitidas)) {
        echo "<script>
            alert('Error: Tipo de archivo no permitido. Solo se permiten PDF, PNG, JPG y JPEG.');
            window.location.href='listar.php';
        </script>";
        exit();
    }

    // Validar que el tipo de archivo pertenece a una columna válida
    $columnas_validas = ["identificacion", "acta_bachiller", "sisben", "abono"];
    if (!in_array($archivoTipo, $columnas_validas)) {
        echo "<script>
            alert('Error: Tipo de documento no válido.');
            window.location.href='listar.php';
        </script>";
        exit();
    }

    // Limitar el tamaño del archivo (5MB)
    if ($archivoTamaño > 5 * 1024 * 1024) { // 5MB
        echo "<script>
            alert('Error: El archivo es demasiado grande. Máximo permitido: 5MB.');
            window.location.href='listar.php';
        </script>";
        exit();
    }

    // Convertir el archivo a binario para la base de datos
    $nuevoArchivo = file_get_contents($archivoTmp);

    // Actualizar el archivo en la base de datos
    $query = $config->conn->prepare("UPDATE archivos SET $archivoTipo = ? WHERE id = ?");
    $query->bind_param("si", $nuevoArchivo, $id);

    if ($query->execute()) {
        echo "<script>
            alert('Archivo actualizado correctamente.');
            window.location.href='listar.php';
        </script>";
    } else {
        echo "<script>
            alert('Error al actualizar el archivo.');
            window.location.href='listar.php';
        </script>";
    }

    $query->close();
}
?>