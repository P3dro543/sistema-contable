<?php require_once '../views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>
        <?php echo empty($data['id_centro_costo']) ? 'Nuevo Centro de Costo' : 'Editar Centro de Costo'; ?>
    </h2>

    <?php if (!empty($data['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $data['error']; ?>
        </div>
    <?php endif; ?>

    <div class="card card-body bg-light mt-4">
        <form
            action="<?php echo BASE_URL; ?>CentroCosto/<?php echo empty($data['id_centro_costo']) ? 'crear' : 'editar/' . $data['id_centro_costo']; ?>"
            method="POST">

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="codigo">Código: *</label>
                    <input type="text" name="codigo" class="form-control"
                        value="<?php echo htmlspecialchars($data['codigo']); ?>" required maxlength="50">
                </div>
                <div class="col-md-8 mb-3">
                    <label for="nombre">Nombre: *</label>
                    <input type="text" name="nombre" class="form-control"
                        value="<?php echo htmlspecialchars($data['nombre']); ?>" required maxlength="100">
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="descripcion">Descripción (opcional):</label>
                    <input type="text" name="descripcion" class="form-control"
                        value="<?php echo htmlspecialchars($data['descripcion']); ?>" maxlength="255">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="estado">Estado:</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="estado" id="estado" <?php echo (($data['estado'] ?? 0) == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="estado">Activo</label>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="<?php echo BASE_URL; ?>CentroCosto/index" class="btn btn-secondary">Regresar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../views/layout/footer.php'; ?>

