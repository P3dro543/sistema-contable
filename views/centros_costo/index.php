<?php
$tituloPagina = 'Centros de Costo';
require_once __DIR__ . '/../../views/layout/header.php';
?>

<div class="page-header">
    <h1>Centros de Costo</h1>
    <nav class="breadcrumb">
        <span>ContaFlow</span>
        <span style="margin:0 .5rem;color:var(--brand-border);">/</span>
        <span>Auxiliares</span>
        <span style="margin:0 .5rem;color:var(--brand-border);">/</span>
        <span>Centros de Costo</span>
    </nav>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert-cf alert-cf-<?= htmlspecialchars($mensaje['tipo']) ?>" role="alert">
        <?php
        $iconos = ['success' => 'bi-check-circle', 'danger' => 'bi-x-circle',
                   'warning' => 'bi-exclamation-triangle', 'info' => 'bi-info-circle'];
        ?>
        <i class="bi <?= $iconos[$mensaje['tipo']] ?? 'bi-info-circle' ?>"></i>
        <?= htmlspecialchars($mensaje['texto']) ?>
    </div>
<?php endif; ?>

<div class="card-cf">
    <div class="card-header-cf">
        <h5>
            <i class="bi bi-diagram-3 me-2" style="color:var(--brand-accent);"></i>
            Listado de Centros de Costo
        </h5>
        <div style="display:flex;gap:.5rem;align-items:center;">
            <a href="index.php?ruta=centros_costo_crear" class="btn-cf btn-cf-primary">
                <i class="bi bi-plus-lg"></i> Nuevo Centro de Costo
            </a>
        </div>
    </div>

    <?php if (empty($registros)): ?>
        <div style="padding:3rem;text-align:center;color:var(--brand-muted);">
            <i class="bi bi-diagram-3" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
            <p style="font-size:.9rem;">No hay centros de costo registrados.</p>
            <a href="index.php?ruta=centros_costo_crear"
               class="btn-cf btn-cf-primary" style="margin-top:1rem;display:inline-flex;">
                <i class="bi bi-plus-lg"></i> Agregar primer centro
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table-cf">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Descripcion</th>
                        <th>Estado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $cc): ?>
                        <tr>
                            <td style="font-weight:600;"><?= htmlspecialchars($cc['codigo']) ?></td>
                            <td><?= htmlspecialchars($cc['nombre']) ?></td>
                            <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                title="<?= htmlspecialchars($cc['descripcion'] ?? '') ?>">
                                <?= htmlspecialchars($cc['descripcion'] ?? '-') ?>
                            </td>
                            <td>
                                <?php if ((int) $cc['estado'] === 1): ?>
                                    <span class="badge-cf badge-success"><i class="bi bi-circle-fill" style="font-size:.45rem;"></i> Activo</span>
                                <?php else: ?>
                                    <span class="badge-cf badge-danger"><i class="bi bi-circle-fill" style="font-size:.45rem;"></i> Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;justify-content:flex-end;gap:.4rem;">
                                    <a href="index.php?ruta=centros_costo_editar&id=<?= (int) $cc['id_centro_costo'] ?>"
                                       class="btn-cf btn-cf-secondary btn-cf-icon" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn-cf btn-cf-danger btn-cf-icon" title="Eliminar"
                                        onclick="abrirModalEliminar({
                                            accion: 'index.php?ruta=centros_costo_eliminar',
                                            texto:  'Realmente desea eliminar el centro \"<?= addslashes(htmlspecialchars($cc['nombre'])) ?>\"?',
                                            campos: { id_registro: '<?= (int) $cc['id_centro_costo'] ?>' }
                                        })">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPags > 1): ?>
        <div class="pagination-cf" style="border-top:1px solid var(--brand-border);">
            <a href="index.php?ruta=centros_costo&pagina=<?= max(1, $pagina - 1) ?>"
               class="page-btn <?= $pagina <= 1 ? 'disabled' : '' ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
            <?php for ($i = 1; $i <= $totalPags; $i++): ?>
                <a href="index.php?ruta=centros_costo&pagina=<?= $i ?>"
                   class="page-btn <?= $i === $pagina ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <a href="index.php?ruta=centros_costo&pagina=<?= min($totalPags, $pagina + 1) ?>"
               class="page-btn <?= $pagina >= $totalPags ? 'disabled' : '' ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
            <span style="margin-left:auto;font-size:.78rem;color:var(--brand-muted);">
                Pagina <?= $pagina ?> de <?= $totalPags ?> (<?= $total ?> registros)
            </span>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>
