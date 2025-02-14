<?php
include 'config.php';

$config = new Database();

// Obtener la cantidad total de registros
$total_query = "SELECT COUNT(*) AS total FROM archivos";
$total_stmt = $config->conn->prepare($total_query);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_registros = $total_row['total'];

// Configuración de paginación
$registros_por_pagina = 2;
$total_paginas = ceil($total_registros / $registros_por_pagina);
$pagina_actual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && $_GET['pagina'] > 0 ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener registros con límite
$query = "SELECT id, email, programa, fecha_subida, identificacion, acta_bachiller, sisben, abono 
          FROM archivos ORDER BY fecha_subida DESC LIMIT ?, ?";
$stmt = $config->conn->prepare($query);
$stmt->bind_param("ii", $inicio, $registros_por_pagina);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Archivos</title>
    <link rel="icon" type="image/png" href="fabicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5 border border-success p-4 rounded">
        <h2 class="text-center mb-4">Lista de Archivos</h2>

        <table class="table table-bordered table-striped text-center">
            <thead class="table-dark">
                <tr>
                    <th>Email</th>
                    <th>Programa</th>
                    <th>Fecha de Subida</th>                  
                    <th>Archivos Adjuntos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaUsuarios">
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['programa']); ?></td>
                        <td><?php echo date("d/m/Y H:i", strtotime($row['fecha_subida'])); ?></td>
                        <td>
                            <div class="d-flex flex-column align-items-center">
                                <?php 
                                $archivos = [
                                    "Identificación" => "identificacion",
                                    "Acta de Bachiller" => "acta_bachiller",
                                    "Sisbén" => "sisben",
                                    "Abono" => "abono"
                                ];
                                foreach ($archivos as $nombre => $columna) {
                                    if (!empty($row[$columna])) {
                                        $file_url = "vista_previa.php?id=" . $row['id'] . "&tipo=" . urlencode($columna);
                                        echo "<label class='fw-bold text-primary' style='cursor:pointer;' onclick=\"verPdf('$file_url')\">
                                                <i class='bi bi-file-earmark-pdf'></i> $nombre
                                              </label>";
                                    }
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning btn-sm" 
                                    onclick="abrirModal(<?php echo $row['id']; ?>)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="descargar_todo.php?email=<?php echo urlencode($row['email']); ?>" class="btn btn-success btn-sm">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Mostrar cantidad de registros -->
        <div class="text-end mt-3">
            <label class="fw-bold text-success">Total de registros: <?php echo $total_registros; ?></label>
        </div>

        <!-- Paginación -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $total_paginas; $i++) : ?>
                    <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Modal para actualizar un archivo -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Actualizar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm" action="actualizar_archivo.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="archivoId" name="id">

                        <!-- Seleccionar tipo de archivo a actualizar -->
                        <div class="mb-3">
                            <label for="archivoTipo" class="form-label">Selecciona el documento a actualizar:</label>
                            <select class="form-select" id="archivoTipo" name="archivoTipo" required>
                                <option value="" selected disabled>Seleccione un documento</option>
                                <option value="identificacion">Identificación</option>
                                <option value="acta_bachiller">Acta de Bachiller</option>
                                <option value="sisben">Sisbén</option>
                                <option value="abono">Abono</option>
                            </select>
                        </div>

                        <!-- Subir nuevo archivo PDF -->
                        <div class="mb-3">
                            <label for="nuevoArchivo" class="form-label">Subir nuevo archivo (PDF)</label>
                            <input type="file" class="form-control" id="nuevoArchivo" name="nuevoArchivo" accept=".pdf" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function abrirModal(id) {
            document.getElementById("archivoId").value = id;
            new bootstrap.Modal(document.getElementById("updateModal")).show();
        }

        function verPdf(url) {
            window.open(url, '_blank');
        }
    </script>
</body>
</html>