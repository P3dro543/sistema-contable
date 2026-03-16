<?php
$tituloPagina = 'Bienvenida';
require_once __DIR__ . '/../../views/layout/header.php';

$nombre   = htmlspecialchars($_SESSION['usuario']['nombre']   ?? '');
$apellido = htmlspecialchars($_SESSION['usuario']['apellido'] ?? '');
$username = htmlspecialchars($_SESSION['usuario']['username'] ?? '');
?>

<div class="page-header">
    <h1>Inicio</h1>
    <nav class="breadcrumb" aria-label="breadcrumb">
        <span>ContaFlow</span>
    </nav>
</div>

<div class="card-cf" style="max-width:640px;padding:2.5rem 2rem;">
    <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#2ea84e,#0ca678);display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:700;color:#fff;flex-shrink:0;">
            <?= strtoupper(mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1)) ?>
        </div>
        <div>
            <p style="color:var(--brand-muted);font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.3rem;">Bienvenido de vuelta</p>
            <h2 style="font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;letter-spacing:-.5px;color:var(--brand-text);margin:0;">
                <?= $nombre . ' ' . $apellido ?>
            </h2>
            <p style="color:var(--brand-muted);font-size:.85rem;margin-top:.25rem;">
                <i class="bi bi-person-circle me-1"></i><?= $username ?>
            </p>
        </div>
    </div>

    <hr style="border-color:var(--brand-border);margin:2rem 0;">

    <p style="font-size:.78rem;color:var(--brand-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:1rem;font-weight:600;">
        Accesos rápidos
    </p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;">
        <?php
        $accesos = [
            ['ruta' => 'terceros',      'label' => 'Terceros',      'icon' => 'bi-people',       'color' => '#58a6ff'],
            ['ruta' => 'centros_costo', 'label' => 'Centros Costo', 'icon' => 'bi-diagram-3',    'color' => '#3fb950'],
            ['ruta' => 'asientos',      'label' => 'Asientos',      'icon' => 'bi-journal-text', 'color' => '#e3b341'],
            ['ruta' => 'reportes',      'label' => 'Reportes',      'icon' => 'bi-bar-chart',    'color' => '#a5d6ff'],
        ];
        foreach ($accesos as $a): ?>
            <a href="index.php?ruta=<?= $a['ruta'] ?>"
               style="background:var(--brand-dark);border:1px solid var(--brand-border);border-radius:8px;padding:.85rem;text-decoration:none;display:flex;flex-direction:column;gap:.4rem;transition:border-color .2s,background .2s;"
               onmouseover="this.style.borderColor='<?= $a['color'] ?>'"
               onmouseout="this.style.borderColor='var(--brand-border)'">
                <i class="bi <?= $a['icon'] ?>" style="font-size:1.3rem;color:<?= $a['color'] ?>"></i>
                <span style="font-size:.82rem;font-weight:500;color:var(--brand-text);"><?= $a['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../views/layout/footer.php'; ?>