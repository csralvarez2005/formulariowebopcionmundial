<?php
include 'db.php';

$db = new Database();

// Obtener la cantidad total de registros
$total_query = "SELECT COUNT(*) as total FROM archivos";
$total_result = $db->conn->query($total_query);
$total_registros = $total_result->fetch_assoc()['total'];

// Configuración de paginación
$registros_por_pagina = 2;
$total_paginas = ceil($total_registros / $registros_por_pagina);
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener registros con límite
$query = "SELECT id, email, fecha_subida, identificacion, acta_bachiller, sisben, abono FROM archivos ORDER BY fecha_subida DESC LIMIT $inicio, $registros_por_pagina";
$result = $db->conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario opción mundial</title>
    <link rel="icon" type="image/png" href="fabicon.png">    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5 border border-success p-4 rounded">
        <h2 class="text-center mb-4">Lista de Archivos</h2>

        <!-- Input de búsqueda -->
        <div class="mb-3">
            <input type="text" id="buscarEmail" class="form-control" placeholder="Buscar por email..." onkeyup="buscarUsuario()">
        </div>

        <table class="table table-bordered table-striped text-center">
            <thead class="table-dark">
                <tr>
                    <th>Email</th>
                    <th>Fecha de Subida</th>
                    <th>Archivos Adjuntos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaUsuarios">
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
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
                                        $file_url = "vista_previa.php?id={$row['id']}&tipo=" . urlencode($nombre);
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
                                <button class="btn btn-warning btn-sm" onclick="abrirModal(<?php echo $row['id']; ?>)">
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
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($pagina_actual > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_paginas; $i++) : ?>
                    <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Modal para vista previa de PDFs -->
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Vista Previa del Archivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="pdfViewer" src="" width="100%" height="500px" style="border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        function verPdf(url) {
            let pdfViewer = document.getElementById("pdfViewer");
            if (pdfViewer) {
                pdfViewer.src = url;
                var modal = new bootstrap.Modal(document.getElementById("pdfModal"));
                modal.show();
            } else {
                console.error("No se encontró el iframe con id='pdfViewer'.");
            }
        }

        function abrirModal(id) {
            let inputId = document.getElementById("archivoId");
            if (inputId) {
                inputId.value = id;
                var modal = new bootstrap.Modal(document.getElementById("updateModal"));
                modal.show();
            } else {
                console.error("No se encontró el campo archivoId.");
            }
        }

        function buscarUsuario() {
            let input = document.getElementById('buscarEmail').value.toLowerCase();
            let filas = document.querySelectorAll('#tablaUsuarios tr');

            filas.forEach(fila => {
                let email = fila.cells[0].textContent.toLowerCase();
                fila.style.display = email.includes(input) ? "" : "none";
            });
        }
    </script>
</body>
</html>