<?php require_once '../views/layout/header.php'; ?>

<div class="container mt-4">
    <h2>Administración de Terceross</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'eliminado'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Registro eliminado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
        <a href="<?php echo BASE_URL; ?>Tercero/crear" class="btn btn-primary">Nuevo Tercero</a>
        <form method="get" action="<?php echo BASE_URL; ?>Tercero/index" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Buscar por nombre o ID..."
                value="<?php echo htmlspecialchars($data['search']); ?>">
            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
        </form>
    </div>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Identificación</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data['terceros'])): ?>
                <?php foreach ($data['terceros'] as $item): ?>
                    <tr>
                        <td>
                            <?php echo $item->identificacion; ?>
                        </td>
                        <td>
                            <?php echo $item->nombre; ?>
                        </td>
                        <td>
                            <?php echo $item->tipo; ?>
                        </td>
                        <td>
                            <?php echo $item->correo; ?>
                        </td>
                        <td>
                            <?php echo $item->telefono; ?>
                        </td>
                        <td>
                            <?php if ($item->estado == 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>Tercero/editar/<?php echo $item->id_tercero; ?>"
                                class="btn btn-warning btn-sm">Editar</a>
                            <!-- Formulario para método POST en eliminación -->
                            <form action="<?php echo BASE_URL; ?>Tercero/eliminar/<?php echo $item->id_tercero; ?>"
                                method="POST" class="d-inline"
                                onsubmit="return confirm('¿Realmente desea eliminar el elemento seleccionado?');">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No se encontraron registros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $data['total_paginas']; $i++): ?>
                <li class="page-item <?php echo ($i == $data['pagina_actual']) ? 'active' : ''; ?>">
                    <a class="page-link"
                        href="<?php echo BASE_URL; ?>Tercero/index/<?php echo $i; ?>?search=<?php echo urlencode($data['search']); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php require_once '../views/layout/footer.php'; ?>
