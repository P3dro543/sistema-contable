<?php require_once '../views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>
        <?php echo empty($data['id_tercero']) ? 'Nuevo Tercero' : 'Editar Tercero'; ?>
    </h2>

    <?php if (!empty($data['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $data['error']; ?>
        </div>
    <?php endif; ?>

    <div class="card card-body bg-light mt-4">
        <form
            action="<?php echo BASE_URL; ?>Tercero/<?php echo empty($data['id_tercero']) ? 'crear' : 'editar/' . $data['id_tercero']; ?>"
            method="POST">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="identificacion">Identificación: *</label>
                    <input type="text" name="identificacion" class="form-control"
                        value="<?php echo htmlspecialchars($data['identificacion']); ?>" required maxlength="50">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nombre">Nombre / Razón Social: *</label>
                    <input type="text" name="nombre" class="form-control"
                        value="<?php echo htmlspecialchars($data['nombre']); ?>" required maxlength="100">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tipo">Tipo de Tercero: *</label>
                    <select name="tipo" class="form-control" required>
                        <option value="" disabled <?php echo empty($data['tipo']) ? 'selected' : ''; ?>>Seleccione un
                            tipo</option>
                        <option value="Cliente" <?php echo ($data['tipo'] == 'Cliente') ? 'selected' : ''; ?>>Cliente
                        </option>
                        <option value="Proveedor" <?php echo ($data['tipo'] == 'Proveedor') ? 'selected' : ''; ?>
                            >Proveedor</option>
                        <option value="Empleado" <?php echo ($data['tipo'] == 'Empleado') ? 'selected' : ''; ?>>Empleado
                        </option>
                        <option value="Otro" <?php echo ($data['tipo'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="estado">Estado:</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="estado" id="estado" <?php echo ($data['estado'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="estado">Activo</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" name="correo" class="form-control"
                        value="<?php echo htmlspecialchars($data['correo']); ?>" maxlength="100">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" name="telefono" class="form-control"
                        value="<?php echo htmlspecialchars($data['telefono']); ?>" maxlength="50">
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="<?php echo BASE_URL; ?>Tercero/index" class="btn btn-secondary">Regresar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../views/layout/footer.php'; ?>
