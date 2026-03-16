<?php
$tituloPagina = 'Reporte Centros de Costo';
require_once __DIR__ . '/../layout/header.php'; 
?>
<?php
// 1. Conexión a la base de datos
$host = "localhost";
$user = "root";
$pass = "1234";
$db   = "sistema_contable"; 
$conn = new mysqli($host, $user, $pass, $db);

// 2. Captura de variables del formulario (Filtros)
$fecha_inicio = $_GET['desde'] ?? date('Y-m-01');
$fecha_fin    = $_GET['hasta'] ?? date('Y-m-d');
$id_centro    = $_GET['centro'] ?? 0;

// 3. Consulta SQL corregida con parámetros seguros
$sql = "SELECT 
            cc.nombre AS 'centro', 
            a.fecha AS 'fecha', 
            ad.id_detalle, 
            ad.tipo_movimiento, 
            pcc.monto AS 'monto_p'
        FROM asiento_detalle ad
        INNER JOIN prorrateo_centro_costo pcc ON ad.id_detalle = pcc.id_detalle
        INNER JOIN centros_costo cc ON pcc.id_centro_costo = cc.id_centro_costo
        INNER JOIN asientos a ON ad.id_asiento = a.id_asiento
        WHERE (a.fecha BETWEEN ? AND ?)
          AND (cc.id_centro_costo = ? OR ? = 0)
        ORDER BY a.fecha ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $fecha_inicio, $fecha_fin, $id_centro, $id_centro);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="page-header">
    <h1>Reporte de Movimientos</h1>
    <nav class="breadcrumb">
        <span>ContaFlow</span> / <span>Centros de Costo</span>
    </nav>
</div>

<div class="card-cf" style="margin-bottom: 20px; padding: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="font-size: 1rem; color: var(--brand-text); margin: 0;">Filtros de Reporte</h3>
        <a href="index.php?ruta=bienvenida" class="btn-cf btn-cf-secondary" style="text-decoration: none; font-size: 0.8rem;">
            <i class="bi bi-house"></i> Menú Principal
        </a>
    </div>

    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <input type="hidden" name="ruta" value="mov_centro_costo">
        
        <div>
            <label class="form-cf-label">Desde:</label>
            <input type="date" name="desde" class="form-cf-control" value="<?php echo $fecha_inicio; ?>">
        </div>
        
        <div>
            <label class="form-cf-label">Hasta:</label>
            <input type="date" name="hasta" class="form-cf-control" value="<?php echo $fecha_fin; ?>">
        </div>

        <div style="flex-grow: 1;">
            <label class="form-cf-label">Centro de Costo:</label>
            <select name="centro" class="form-cf-control">
                <option value="0">-- Todos los Centros --</option>
                <?php
                $centros = $conn->query("SELECT id_centro_costo, nombre FROM centros_costo");
                while($c = $centros->fetch_assoc()){
                    $selected = ($id_centro == $c['id_centro_costo']) ? 'selected' : '';
                    echo "<option value='{$c['id_centro_costo']}' $selected>{$c['nombre']}</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn-cf btn-cf-primary">
            <i class="bi bi-search"></i> Generar
        </button>
    </form>
</div>

<div class="card-cf">
    <table class="table-cf">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Centro de Costo</th>
                <th>ID Detalle</th>
                <th>Tipo</th>
                <th style="text-align: right;">Monto Prorrateado</th>
            </tr>
        </thead>
        <tbody>
            <?php if($resultado->num_rows > 0): ?>
                <?php while($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($fila['fecha'])); ?></td>
                        <td style="color: var(--brand-text); font-weight: 500;"><?php echo $fila['centro']; ?></td>
                        <td style="color: var(--brand-muted);">#<?php echo $fila['id_detalle']; ?></td>
                        <td>
                            <?php 
                                $tipo = $fila['tipo_movimiento'];
                                $claseBadge = (strtolower($tipo) == 'debe') ? 'badge-success' : 'badge-danger';
                            ?>
                            <span class="badge-cf <?php echo $claseBadge; ?>">
                                <?php echo strtoupper($tipo); ?>
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: bold; color: var(--brand-text);">
                            ₡<?php echo number_format($fila['monto_p'], 2); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: var(--brand-muted);">
                        No hay movimientos para este periodo.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
require_once __DIR__ . '/../layout/footer.php'; 
?>