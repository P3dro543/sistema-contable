<?php
$tituloPagina = 'Direcciones del Tercero';
require_once __DIR__ . '/../../views/layout/header.php';
?>

<div class="page-header">
    <h1>Direcciones del Tercero</h1>
    <nav class="breadcrumb">
        <a href="index.php?ruta=terceros">Terceros</a>
        <span style="margin:0 .5rem;color:var(--brand-border);">/</span>
        <span>Direcciones</span>
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
            <i class="bi bi-geo-alt me-2" style="color:var(--brand-accent);"></i>
            Listado de Direcciones
        </h5>
        <div style="display:flex;gap:.5rem;align-items:center;">
            <a href="index.php?ruta=terceros" class="btn-cf btn-cf-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <a href="index.php?ruta=direcciones_crear&id_tercero=<?= (int) $idTercero ?>" class="btn-cf btn-cf-primary">
                <i class="bi bi-plus-lg"></i> Nueva Dirección
            </a>
        </div>
    </div>

    <?php if (empty($registros)): ?>
        <div style="padding:3rem;text-align:center;color:var(--brand-muted);">
            <i class="bi bi-geo-alt" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
            <p style="font-size:.9rem;">No hay direcciones registradas para este tercero.</p>
            <a href="index.php?ruta=direcciones_crear&id_tercero=<?= (int) $idTercero ?>"
               class="btn-cf btn-cf-primary" style="margin-top:1rem;display:inline-flex;">
                <i class="bi bi-plus-lg"></i> Agregar primera dirección
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table-cf">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Alias</th>
                        <th>Provincia</th>
                        <th>Cantón</th>
                        <th>Distrito</th>
                        <th>Dirección Exacta</th>
                        <th>Principal</th>
                        <th>Estado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $dir): ?>
                    <tr>
                        <td style="color:var(--brand-muted);font-size:.8rem;"><?= (int) $dir['id_direccion'] ?></td>
                        <td style="font-weight:500;"><?= htmlspecialchars($dir['alias'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($dir['provincia'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($dir['canton']    ?? '—') ?></td>
                        <td><?= htmlspecialchars($dir['distrito']  ?? '—') ?></td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                            title="<?= htmlspecialchars($dir['direccion_exacta'] ?? '') ?>">
                            <?= htmlspecialchars($dir['direccion_exacta'] ?? '—') ?>
                        </td>
                        <td>
                            <?php if ($dir['principal']): ?>
                                <span class="badge-cf badge-primary"><i class="bi bi-star-fill"></i> Principal</span>
                            <?php else: ?>
                                <span style="color:var(--brand-muted);font-size:.8rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($dir['estado']): ?>
                                <span class="badge-cf badge-success"><i class="bi bi-circle-fill" style="font-size:.45rem;"></i> Activa</span>
                            <?php else: ?>
                                <span class="badge-cf badge-danger"><i class="bi bi-circle-fill" style="font-size:.45rem;"></i> Inactiva</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;justify-content:flex-end;gap:.4rem;">
                                <a href="index.php?ruta=direcciones_editar&id=<?= (int) $dir['id_direccion'] ?>"
                                   class="btn-cf btn-cf-secondary btn-cf-icon" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn-cf btn-cf-danger btn-cf-icon" title="Eliminar"
                                    onclick="abrirModalEliminar({
                                        accion: 'index.php?ruta=direcciones_eliminar',
                                        texto:  '¿Realmente desea eliminar la dirección «<?= addslashes(htmlspecialchars($dir['alias'] ?? 'seleccionada')) ?>»?',
                                        campos: {
                                            id_direccion: '<?= (int) $dir['id_direccion'] ?>',
                                            id_tercero:   '<?= (int) $idTercero ?>'
                                        }
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
            <a href="index.php?ruta=direcciones&id_tercero=<?= $idTercero ?>&pagina=<?= max(1, $pagina - 1) ?>"
               class="page-btn <?= $pagina <= 1 ? 'disabled' : '' ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
            <?php for ($i = 1; $i <= $totalPags; $i++): ?>
                <a href="index.php?ruta=direcciones&id_tercero=<?= $idTercero ?>&pagina=<?= $i ?>"
                   class="page-btn <?= $i === $pagina ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            <a href="index.php?ruta=direcciones&id_tercero=<?= $idTercero ?>&pagina=<?= min($totalPags, $pagina + 1) ?>"
               class="page-btn <?= $pagina >= $totalPags ? 'disabled' : '' ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
            <span style="margin-left:auto;font-size:.78rem;color:var(--brand-muted);">
                Página <?= $pagina ?> de <?= $totalPags ?> (<?= $total ?> registros)
            </span>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>