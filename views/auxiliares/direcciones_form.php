<?php
$tituloPagina = ($accion === 'editar') ? 'Editar Dirección' : 'Nueva Dirección';
require_once __DIR__ . '/../../views/layout/header.php';

$esEditar   = ($accion === 'editar');
$accionForm = $esEditar
    ? 'index.php?ruta=direcciones_actualizar'
    : 'index.php?ruta=direcciones_guardar';

$val = [
    'id_direccion'     => (int)    ($datos['id_direccion']    ?? 0),
    'id_tercero'       => (int)    ($datos['id_tercero']      ?? $idTercero ?? 0),
    'alias'            => (string) ($datos['alias']           ?? ''),
    'provincia'        => (string) ($datos['provincia']       ?? ''),
    'canton'           => (string) ($datos['canton']          ?? ''),
    'distrito'         => (string) ($datos['distrito']        ?? ''),
    'direccion_exacta' => (string) ($datos['direccion_exacta']?? ''),
    'estado'           => isset($datos['estado'])    ? (bool) $datos['estado']    : true,
    'principal'        => isset($datos['principal']) ? (bool) $datos['principal'] : false,
];

$err = $errores ?? [];

function fieldCls(string $campo, array $errores): string {
    return isset($errores[$campo]) ? 'form-cf-control is-invalid' : 'form-cf-control';
}
?>

<div class="page-header">
    <h1><?= $tituloPagina ?></h1>
    <nav class="breadcrumb">
        <a href="index.php?ruta=terceros">Terceros</a>
        <span style="margin:0 .5rem;color:var(--brand-border);">/</span>
        <a href="index.php?ruta=direcciones&id_tercero=<?= $val['id_tercero'] ?>">Direcciones</a>
        <span style="margin:0 .5rem;color:var(--brand-border);">/</span>
        <span><?= $tituloPagina ?></span>
    </nav>
</div>

<div class="card-cf" style="max-width:680px;">
    <div class="card-header-cf">
        <h5>
            <i class="bi bi-geo-alt me-2" style="color:var(--brand-accent);"></i>
            <?= $tituloPagina ?>
        </h5>
    </div>

    <div style="padding:1.75rem 1.5rem;">
        <form method="POST" action="<?= $accionForm ?>" novalidate>

            <input type="hidden" name="id_tercero" value="<?= $val['id_tercero'] ?>">
            <?php if ($esEditar): ?>
                <input type="hidden" name="id_direccion" value="<?= $val['id_direccion'] ?>">
            <?php endif; ?>

            <!-- ALIAS -->
            <div style="margin-bottom:1.25rem;">
                <label class="form-cf-label" for="alias">
                    Alias / Nombre <span style="color:var(--brand-danger);">*</span>
                </label>
                <input type="text" id="alias" name="alias"
                       class="<?= fieldCls('alias', $err) ?>"
                       placeholder="Ej. Casa, Oficina Principal, Bodega Norte"
                       maxlength="100" value="<?= htmlspecialchars($val['alias']) ?>" required>
                <?php if (!empty($err['alias'])): ?>
                    <div class="form-cf-error"><?= htmlspecialchars($err['alias']) ?></div>
                <?php endif; ?>
            </div>

            <!-- PROVINCIA / CANTÓN / DISTRITO -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.25rem;">
                <div>
                    <label class="form-cf-label" for="provincia">Provincia</label>
                    <input type="text" id="provincia" name="provincia"
                           class="<?= fieldCls('provincia', $err) ?>"
                           placeholder="Ej. Cartago" maxlength="100"
                           value="<?= htmlspecialchars($val['provincia']) ?>">
                </div>
                <div>
                    <label class="form-cf-label" for="canton">Cantón</label>
                    <input type="text" id="canton" name="canton"
                           class="<?= fieldCls('canton', $err) ?>"
                           placeholder="Ej. Cartago" maxlength="100"
                           value="<?= htmlspecialchars($val['canton']) ?>">
                </div>
                <div>
                    <label class="form-cf-label" for="distrito">Distrito</label>
                    <input type="text" id="distrito" name="distrito"
                           class="<?= fieldCls('distrito', $err) ?>"
                           placeholder="Ej. Oriental" maxlength="100"
                           value="<?= htmlspecialchars($val['distrito']) ?>">
                </div>
            </div>

            <!-- DIRECCIÓN EXACTA -->
            <div style="margin-bottom:1.25rem;">
                <label class="form-cf-label" for="direccion_exacta">
                    Dirección Exacta <span style="color:var(--brand-danger);">*</span>
                </label>
                <textarea id="direccion_exacta" name="direccion_exacta"
                          class="<?= fieldCls('direccion_exacta', $err) ?>"
                          placeholder="Ej. 200 metros norte del parque central, casa esquinera color azul"
                          maxlength="255" rows="3" style="resize:vertical;" required
                ><?= htmlspecialchars($val['direccion_exacta']) ?></textarea>
                <?php if (!empty($err['direccion_exacta'])): ?>
                    <div class="form-cf-error"><?= htmlspecialchars($err['direccion_exacta']) ?></div>
                <?php endif; ?>
                <div style="text-align:right;font-size:.72rem;color:var(--brand-muted);margin-top:.3rem;">
                    <span id="charCount">0</span>/255
                </div>
            </div>

            <!-- ESTADO y PRINCIPAL -->
            <div style="display:flex;gap:2rem;margin-bottom:1.75rem;flex-wrap:wrap;">
                <label class="form-cf-check">
                    <input type="checkbox" name="estado" <?= $val['estado'] ? 'checked' : '' ?>>
                    <span>Dirección activa</span>
                </label>
                <label class="form-cf-check">
                    <input type="checkbox" name="principal" id="chkPrincipal" <?= $val['principal'] ? 'checked' : '' ?>>
                    <span>
                        <i class="bi bi-star-fill" style="color:var(--brand-warning);font-size:.85rem;margin-right:.2rem;"></i>
                        Marcar como principal
                    </span>
                </label>
            </div>

            <div id="notaPrincipal"
                 style="display:<?= $val['principal'] ? 'flex' : 'none' ?>;margin-bottom:1.5rem;"
                 class="alert-cf alert-cf-warning">
                <i class="bi bi-info-circle"></i>
                Al marcar esta dirección como principal, la anterior será desmarcada automáticamente.
            </div>

            <!-- BOTONES -->
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <a href="index.php?ruta=direcciones&id_tercero=<?= $val['id_tercero'] ?>"
                   class="btn-cf btn-cf-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn-cf btn-cf-primary">
                    <i class="bi bi-floppy"></i>
                    <?= $esEditar ? 'Actualizar' : 'Guardar' ?>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
const textarea    = document.getElementById('direccion_exacta');
const charCountEl = document.getElementById('charCount');
function actualizarContador() { charCountEl.textContent = textarea.value.length; }
actualizarContador();
textarea.addEventListener('input', actualizarContador);

document.getElementById('chkPrincipal').addEventListener('change', function () {
    document.getElementById('notaPrincipal').style.display = this.checked ? 'flex' : 'none';
});
</script>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>