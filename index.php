<?php include 'programas.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Opción Mundial</title>
    <link rel="icon" type="image/png" href="fabicon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="container d-flex justify-content-center align-items-center">
        <div class="card p-4 shadow-lg w-100">
            <div class="text-center">
                <img src="fundacioimagen.png" alt="Opción Mundial Fundación Internacional" class="logo">
            </div>
            <div class="card-body">
                <h2 class="text-center">MATRÍCULA VIRTUAL</h2>
                <p class="text-center">Adjunta los documentos solicitados para completar tu matrícula.</p>
                <p class="note text-center">(Formatos permitidos: PDF, PNG, JPG o JPEG).</p>
            </div>     

            <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="mb-3">
                    <label for="email">CORREO ELECTRÓNICO <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Ingrese su correo electrónico" required autocomplete="off">
                </div>

                <!-- Campo Programa de Interés con búsqueda -->
                <div class="mb-3">
                    <label for="programa">Programa de Interés <span class="required">*</span></label>
                    <select id="programa" name="programa" class="form-control select2" required>
                        <option value="" disabled selected>Seleccione un programa</option>
                        <?php foreach ($programas as $programa): ?>
                            <option value="<?= htmlspecialchars($programa) ?>"><?= htmlspecialchars($programa) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>ADJUNTAR COPIA DE DOCUMENTO DE IDENTIDAD <span class="required">*</span></label>
                    <input type="file" class="form-control file-input" name="identificacion" accept=".pdf,.png,.jpg,.jpeg" required>
                </div>

                <div class="mb-3">
                    <label>ADJUNTAR COPIA DE ACTA DE BACHILLER <span class="required">*</span></label>
                    <input type="file" class="form-control file-input" name="acta_bachiller" accept=".pdf,.png,.jpg,.jpeg" required>
                </div>

                <div class="mb-3">
                    <label>ADJUNTAR COPIA DE SISBÉN O RECIBO PÚBLICO <span class="required">*</span></label>
                    <input type="file" class="form-control file-input" name="sisben" accept=".pdf,.png,.jpg,.jpeg" required>
                </div>

                <div class="mb-3">
                    <label>ADJUNTAR FACTURA O RECIBO DE PAGO DEL ABONO INICIAL</label>
                    <input type="file" class="form-control file-input" name="abono" accept=".pdf,.png,.jpg,.jpeg">
                </div>

                <button type="submit" class="btn btn-success w-100">Subir Archivos</button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inicializar Select2 con búsqueda
            $('#programa').select2({
                placeholder: "Seleccione un programa",
                allowClear: true
            });

            // Verificar parámetros en la URL para mostrar alertas con SweetAlert2
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.get("success") === "1") {
                Swal.fire({
                    icon: "success",
                    title: "¡Registro exitoso!",
                    text: "Tus archivos han sido guardados correctamente.",
                    confirmButtonText: "OK"
                });
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            if (urlParams.get("error")) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: urlParams.get("error"),
                    confirmButtonText: "OK"
                });
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>

</body>
</html>