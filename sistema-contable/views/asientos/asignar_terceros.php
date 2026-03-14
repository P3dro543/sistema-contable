<?php require_once '../views/layout/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Asignar terceros a asientos</h2>
    </div>

    <?php if (empty($data['periodos'])): ?>
        <div class="alert alert-warning">
            No se encontraron periodos configurados. Se mostrarán asientos sin filtro por periodo.
        </div>
    <?php else: ?>
        <form method="get" action="<?php echo BASE_URL; ?>AsignarTerceros/index" class="row g-2 mb-3">
            <div class="col-sm-8 col-md-6">
                <label class="form-label">Periodo</label>
                <select name="id_periodo" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($data['periodos'] as $p): ?>
                        <option value="<?php echo (int) $p->id_periodo; ?>" <?php echo ((int) $p->id_periodo === (int) $data['periodo_seleccionado_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p->nombre ?? ('Periodo ' . $p->id_periodo)); ?>
                            <?php if (!empty($data['periodo_abierto_id']) && (int) $p->id_periodo === (int) $data['periodo_abierto_id']): ?>
                                (Abierto)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <noscript>
                <div class="col-auto align-self-end">
                    <button type="submit" class="btn btn-primary">Aplicar</button>
                </div>
            </noscript>
        </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Consecutivo</th>
                    <th>Fecha</th>
                    <th>Referencia</th>
                    <th>Estado</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['asientos'])): ?>
                    <?php foreach ($data['asientos'] as $a): ?>
                        <?php
                        $estadoNombre = $a->estado_nombre ?? '';
                        $estadoLower = strtolower(trim((string) $estadoNombre));
                        $puedeAsignar = ($estadoLower === 'borrador' || $estadoLower === 'pendiente de aprobacion' || $estadoLower === 'pendiente de aprobar');
                        $collapseId = 'asiento_' . (int) $a->id_asiento;
                        ?>
                        <tr>
                            <td><?php echo (int) $a->id_asiento; ?></td>
                            <td><?php echo htmlspecialchars($a->consecutivo ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($a->fecha ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($a->referencia ?? ''); ?></td>
                            <td>
                                <?php if ($estadoNombre): ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($estadoNombre); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">N/D</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#<?php echo $collapseId; ?>">
                                    Ver movimientos
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="<?php echo $collapseId; ?>">
                            <td colspan="6">
                                <div class="p-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Línea</th>
                                                    <th>Cuenta</th>
                                                    <th>Tipo</th>
                                                    <th class="text-end">Monto</th>
                                                    <th class="text-end">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $detalles = $data['detalles_por_asiento'][$a->id_asiento] ?? []; ?>
                                                <?php if (!empty($detalles)): ?>
                                                    <?php foreach ($detalles as $d): ?>
                                                        <tr>
                                                            <td><?php echo (int) ($d->id_detalle ?? 0); ?></td>
                                                            <td><?php echo htmlspecialchars(($d->cuenta_codigo ?? '') . ' - ' . ($d->cuenta_nombre ?? '')); ?></td>
                                                            <td><?php echo htmlspecialchars($d->tipo_movimiento ?? ''); ?></td>
                                                            <td class="text-end"><?php echo number_format((float) ($d->monto ?? 0), 2); ?></td>
                                                            <td class="text-end">
                                                                <button type="button" class="btn btn-success btn-sm js-asignar-terceros"
                                                                    data-id-detalle="<?php echo (int) ($d->id_detalle ?? 0); ?>"
                                                                    data-monto="<?php echo htmlspecialchars((string) ($d->monto ?? 0)); ?>"
                                                                    data-puede="<?php echo $puedeAsignar ? '1' : '0'; ?>"
                                                                    <?php echo $puedeAsignar ? '' : 'disabled'; ?>>
                                                                    Asignar terceros
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">No hay movimientos para este asiento.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php if (!$puedeAsignar): ?>
                                        <div class="alert alert-light border mt-2 mb-0">
                                            Este asiento no está en estado Borrador o Pendiente de Aprobar, por lo tanto no permite asignación de terceros.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No se encontraron asientos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $data['total_paginas']; $i++): ?>
                <li class="page-item <?php echo ($i == $data['pagina_actual']) ? 'active' : ''; ?>">
                    <a class="page-link"
                        href="<?php echo BASE_URL; ?>AsignarTerceros/index/<?php echo $i; ?><?php echo !empty($data['periodo_seleccionado_id']) ? ('?id_periodo=' . urlencode((string) $data['periodo_seleccionado_id'])) : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- Modal Asignación -->
<div class="modal fade" id="modalAsignarTerceros" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar terceros</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="asignarError"></div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="text-muted">Línea</div>
                        <div class="fw-semibold" id="modalLineaId">-</div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-muted">Monto de la línea</div>
                        <div class="fw-semibold" id="modalMontoLinea">0.00</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold">Prorrateo por terceros</div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAgregarFila">Agregar línea</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle" id="tablaDistribucion">
                        <thead>
                            <tr>
                                <th style="width: 60%;">Tercero</th>
                                <th style="width: 30%;" class="text-end">Monto</th>
                                <th style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    <div>
                        <div class="text-muted text-end">Sumatoria</div>
                        <div class="fw-semibold text-end" id="modalSumatoria">0.00</div>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    La sumatoria del prorrateo debe ser igual al monto de la línea para permitir guardar.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarAsignacion" disabled>Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.BASE_URL = <?php echo json_encode(BASE_URL); ?>;
    window.TERCEROS_ACTIVOS = <?php echo json_encode($data['terceros_activos']); ?>;
</script>
<?php $pageScripts = [BASE_URL . 'assets/js/asignar_terceros.js']; ?>

<?php require_once '../views/layout/footer.php'; ?>
