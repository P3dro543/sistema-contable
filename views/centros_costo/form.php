<?php
$tituloPagina = ($accion === 'editar') ? 'Editar Centro de Costo' : 'Nuevo Centro de Costo';
require_once __DIR__ . '/../../views/layout/header.php';

$esEditar   = ($accion === 'editar');
$accionForm = $esEditar
    ? 'index.php?ruta=centros_costo_actualizar'
    : 'index.php?ruta=centros_costo_guardar';

$val = [
    'id_centro_costo' => (int)    ($datos['id_centro_costo'] ?? 0),
    'codigo'          => (string) ($datos['codigo'] ?? ''),
    'nombre'          => (string) ($datos['nombre'] ?? ''),
    'descripcion'     => (string) ($datos['descripcion'] ?? ''),
    'estado'          => isset($datos['estado']) ? (bool) $datos['estado'] : true,
];

$err = $errores ?? [];

function fieldCls(string $campo, array $errores): string {
    return isset($errores[$campo]) ? 'form-cf-control is-invalid' : 'form-cf-control';
}
?>

<div class="page-header">
    <h1><?= $tituloPagina ?></h1>
    <nav class="breadcrumb">
        <a href="index.php?ruta=centros_costo">Centros de Costo</a>
        <span style="margin:0 .5rem;color:var(--brand-border);">/</span>
        <span><?= $tituloPagina ?></span>
    </nav>
</div>

<div class="card-cf" style="max-width:680px;">
    <div class="card-header-cf">
        <h5>
            <i class="bi bi-diagram-3 me-2" style="color:var(--brand-accent);"></i>
            <?= $tituloPagina ?>
        </h5>
    </div>

    <div style="padding:1.75rem 1.5rem;">
        <form method="POST" action="<?= $accionForm ?>" novalidate>

            <?php if ($esEditar): ?>
                <input type="hidden" name="id_centro_costo" value="<?= $val['id_centro_costo'] ?>">
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:1.25rem;">
                <div>
                    <label class="form-cf-label" for="codigo">
                        Codigo <span style="color:var(--brand-danger);">*</span>
                    </label>
                    <input type="text" id="codigo" name="codigo"
                           class="<?= fieldCls('codigo', $err) ?>"
                           placeholder="Ej. CC-001" maxlength="20"
                           value="<?= htmlspecialchars($val['codigo']) ?>" required>
                    <?php if (!empty($err['codigo'])): ?>
                        <div class="form-cf-error"><?= htmlspecialchars($err['codigo']) ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="form-cf-label" for="nombre">
                        Nombre <span style="color:var(--brand-danger);">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre"
                           class="<?= fieldCls('nombre', $err) ?>"
                           placeholder="Ej. Administracion" maxlength="120"
                           value="<?= htmlspecialchars($val['nombre']) ?>" required>
                    <?php if (!empty($err['nombre'])): ?>
                        <div class="form-cf-error"><?= htmlspecialchars($err['nombre']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-bottom:1.25rem;">
                <label class="form-cf-label" for="descripcion">Descripcion</label>
                <textarea id="descripcion" name="descripcion"
                          class="<?= fieldCls('descripcion', $err) ?>"
                          placeholder="Detalle opcional del centro de costo" maxlength="255" rows="3"
                          style="resize:vertical;"
                ><?= htmlspecialchars($val['descripcion']) ?></textarea>
                <?php if (!empty($err['descripcion'])): ?>
                    <div class="form-cf-error"><?= htmlspecialchars($err['descripcion']) ?></div>
                <?php endif; ?>
            </div>

            <div style="display:flex;gap:2rem;margin-bottom:1.75rem;flex-wrap:wrap;">
                <label class="form-cf-check">
                    <input type="checkbox" name="estado" <?= $val['estado'] ? 'checked' : '' ?>>
                    <span>Centro activo</span>
                </label>
            </div>

            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <a href="index.php?ruta=centros_costo" class="btn-cf btn-cf-secondary">
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

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>
