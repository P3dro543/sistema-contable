<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'ContaFlow') ?> | ContaFlow</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brand-dark:    #0d1117;
            --brand-surface: #161b22;
            --brand-border:  #21262d;
            --brand-accent:  #2ea84e;
            --brand-accent2: #1f7a38;
            --brand-text:    #e6edf3;
            --brand-muted:   #7d8590;
            --brand-danger:  #da3633;
            --brand-warning: #d29922;
            --sidebar-w:     240px;
            --topbar-h:      60px;
            --radius:        8px;
            --font-body:     'DM Sans', sans-serif;
            --font-display:  'Syne', sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-body); background: var(--brand-dark); color: var(--brand-text); min-height: 100vh; }

        .topbar {
            position: fixed; top: 0; left: 0; right: 0;
            height: var(--topbar-h);
            background: var(--brand-surface);
            border-bottom: 1px solid var(--brand-border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem 0 var(--sidebar-w);
            z-index: 1000; transition: padding-left .3s ease;
        }
        .topbar.sidebar-collapsed { padding-left: 64px; }
        .topbar-right { display: flex; align-items: center; gap: 1rem; }
        .topbar-user { display: flex; align-items: center; gap: .6rem; cursor: pointer; padding: .35rem .75rem; border-radius: var(--radius); transition: background .2s; }
        .topbar-user:hover { background: var(--brand-border); }
        .avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--brand-accent), #0ca678); display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; color: #fff; flex-shrink: 0; }
        .user-name { font-size: .875rem; font-weight: 500; color: var(--brand-text); max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .btn-logout { background: transparent; border: 1px solid var(--brand-border); color: var(--brand-muted); padding: .35rem .7rem; border-radius: var(--radius); font-size: .8rem; cursor: pointer; transition: all .2s; text-decoration: none; display: flex; align-items: center; gap: .3rem; }
        .btn-logout:hover { border-color: var(--brand-danger); color: var(--brand-danger); }

        .sidebar { position: fixed; top: 0; left: 0; width: var(--sidebar-w); height: 100vh; background: var(--brand-surface); border-right: 1px solid var(--brand-border); display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; z-index: 1001; transition: width .3s ease; }
        .sidebar.collapsed { width: 64px; }
        .sidebar-header { height: var(--topbar-h); display: flex; align-items: center; padding: 0 1rem; border-bottom: 1px solid var(--brand-border); flex-shrink: 0; gap: .6rem; }
        .sidebar-logo { width: 32px; height: 32px; background: var(--brand-accent); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: #fff; font-weight: 700; flex-shrink: 0; }
        .sidebar-title { font-family: var(--font-display); font-size: 1.05rem; font-weight: 800; color: var(--brand-text); letter-spacing: -.4px; white-space: nowrap; transition: opacity .2s; }
        .sidebar.collapsed .sidebar-title { opacity: 0; pointer-events: none; }
        .sidebar-toggle { margin-left: auto; background: transparent; border: none; color: var(--brand-muted); cursor: pointer; padding: .3rem; border-radius: var(--radius); transition: color .2s, background .2s; flex-shrink: 0; }
        .sidebar-toggle:hover { color: var(--brand-text); background: var(--brand-border); }
        .sidebar-nav { padding: .75rem 0; flex: 1; }
        .nav-section-label { font-size: .65rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--brand-muted); padding: .75rem 1.1rem .3rem; white-space: nowrap; overflow: hidden; transition: opacity .2s; }
        .sidebar.collapsed .nav-section-label { opacity: 0; }
        .nav-item-link { display: flex; align-items: center; gap: .75rem; padding: .5rem 1.1rem; text-decoration: none; color: var(--brand-muted); font-size: .875rem; font-weight: 400; transition: color .2s, background .2s; white-space: nowrap; overflow: hidden; }
        .nav-item-link i { font-size: 1rem; flex-shrink: 0; }
        .nav-item-link span { transition: opacity .2s; }
        .sidebar.collapsed .nav-item-link span { opacity: 0; }
        .nav-item-link:hover { color: var(--brand-text); background: var(--brand-border); }
        .nav-item-link.active { color: var(--brand-accent); background: rgba(46,168,78,.1); font-weight: 500; border-right: 2px solid var(--brand-accent); }

        .main-wrapper { margin-left: var(--sidebar-w); margin-top: var(--topbar-h); min-height: calc(100vh - var(--topbar-h)); padding: 2rem; transition: margin-left .3s ease; }
        .main-wrapper.sidebar-collapsed { margin-left: 64px; }

        .page-header { margin-bottom: 1.75rem; }
        .page-header h1 { font-family: var(--font-display); font-size: 1.6rem; font-weight: 700; color: var(--brand-text); letter-spacing: -.5px; }
        .page-header .breadcrumb { font-size: .8rem; color: var(--brand-muted); }
        .page-header .breadcrumb a { color: var(--brand-accent); text-decoration: none; }

        .card-cf { background: var(--brand-surface); border: 1px solid var(--brand-border); border-radius: var(--radius); overflow: hidden; }
        .card-cf .card-header-cf { padding: 1rem 1.25rem; border-bottom: 1px solid var(--brand-border); display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .card-cf .card-header-cf h5 { font-size: .95rem; font-weight: 600; color: var(--brand-text); margin: 0; }

        .table-cf { width: 100%; border-collapse: collapse; font-size: .875rem; }
        .table-cf thead th { background: var(--brand-dark); color: var(--brand-muted); font-size: .7rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; padding: .75rem 1rem; border-bottom: 1px solid var(--brand-border); white-space: nowrap; }
        .table-cf tbody td { padding: .7rem 1rem; border-bottom: 1px solid var(--brand-border); color: var(--brand-text); vertical-align: middle; }
        .table-cf tbody tr:last-child td { border-bottom: none; }
        .table-cf tbody tr:hover td { background: rgba(255,255,255,.03); }

        .badge-cf { display: inline-flex; align-items: center; gap: .25rem; padding: .2rem .55rem; border-radius: 20px; font-size: .7rem; font-weight: 600; }
        .badge-success { background: rgba(46,168,78,.15); color: #3fb950; border: 1px solid rgba(46,168,78,.3); }
        .badge-danger  { background: rgba(218,54,51,.15); color: #f85149; border: 1px solid rgba(218,54,51,.3); }
        .badge-warning { background: rgba(210,153,34,.15); color: #e3b341; border: 1px solid rgba(210,153,34,.3); }
        .badge-primary { background: rgba(56,139,253,.15); color: #58a6ff; border: 1px solid rgba(56,139,253,.3); }

        .btn-cf { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .9rem; border-radius: var(--radius); font-size: .8rem; font-weight: 500; cursor: pointer; border: none; transition: all .2s; text-decoration: none; }
        .btn-cf-primary { background: var(--brand-accent); color: #fff; }
        .btn-cf-primary:hover { background: var(--brand-accent2); color: #fff; }
        .btn-cf-secondary { background: var(--brand-border); color: var(--brand-text); }
        .btn-cf-secondary:hover { background: #2d333b; }
        .btn-cf-danger { background: transparent; border: 1px solid var(--brand-border); color: var(--brand-muted); }
        .btn-cf-danger:hover { border-color: var(--brand-danger); color: var(--brand-danger); background: rgba(218,54,51,.08); }
        .btn-cf-icon { padding: .35rem .5rem; border-radius: 6px; }

        .form-cf-label { font-size: .8rem; font-weight: 500; color: var(--brand-muted); margin-bottom: .35rem; display: block; }
        .form-cf-control { width: 100%; background: var(--brand-dark); border: 1px solid var(--brand-border); color: var(--brand-text); padding: .55rem .85rem; border-radius: var(--radius); font-size: .875rem; font-family: var(--font-body); transition: border-color .2s; }
        .form-cf-control:focus { outline: none; border-color: var(--brand-accent); box-shadow: 0 0 0 3px rgba(46,168,78,.15); }
        .form-cf-control.is-invalid { border-color: var(--brand-danger); }
        .form-cf-error { font-size: .75rem; color: #f85149; margin-top: .3rem; }
        .form-cf-check { display: flex; align-items: center; gap: .5rem; cursor: pointer; font-size: .875rem; color: var(--brand-text); }
        .form-cf-check input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--brand-accent); cursor: pointer; }

        .alert-cf { padding: .75rem 1rem; border-radius: var(--radius); font-size: .875rem; display: flex; align-items: center; gap: .6rem; margin-bottom: 1.25rem; }
        .alert-cf-success { background: rgba(46,168,78,.12); border: 1px solid rgba(46,168,78,.3); color: #3fb950; }
        .alert-cf-danger  { background: rgba(218,54,51,.12); border: 1px solid rgba(218,54,51,.3); color: #f85149; }
        .alert-cf-warning { background: rgba(210,153,34,.12); border: 1px solid rgba(210,153,34,.3); color: #e3b341; }
        .alert-cf-info    { background: rgba(56,139,253,.12); border: 1px solid rgba(56,139,253,.3); color: #58a6ff; }

        .pagination-cf { display: flex; align-items: center; gap: .25rem; padding: .75rem 1rem; }
        .page-btn { min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; font-size: .8rem; font-weight: 500; text-decoration: none; color: var(--brand-muted); border: 1px solid transparent; transition: all .2s; }
        .page-btn:hover { border-color: var(--brand-border); color: var(--brand-text); }
        .page-btn.active { background: var(--brand-accent); color: #fff; border-color: var(--brand-accent); }
        .page-btn.disabled { pointer-events: none; opacity: .35; }

        .modal-cf-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.7); z-index: 2000; align-items: center; justify-content: center; }
        .modal-cf-overlay.show { display: flex; }
        .modal-cf-box { background: var(--brand-surface); border: 1px solid var(--brand-border); border-radius: 12px; padding: 2rem; max-width: 420px; width: 90%; animation: modalIn .2s ease; }
        @keyframes modalIn { from { transform: scale(.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-cf-box h5 { font-family: var(--font-display); font-size: 1.1rem; margin-bottom: .75rem; }
        .modal-cf-box p { font-size: .9rem; color: var(--brand-muted); margin-bottom: 1.5rem; }
        .modal-cf-actions { display: flex; gap: .75rem; justify-content: flex-end; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--brand-dark); }
        ::-webkit-scrollbar-thumb { background: var(--brand-border); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--brand-muted); }

        @media (max-width: 768px) {
            .sidebar { width: 64px; }
            .sidebar .sidebar-title, .sidebar .nav-section-label, .sidebar .nav-item-link span { opacity: 0; pointer-events: none; }
            .main-wrapper { margin-left: 64px; padding: 1.25rem; }
            .topbar { padding-left: 80px; }
        }
    </style>
</head>
<body>

<?php
$menuItems = [
    ['seccion' => 'INICIO', 'items' => [
        ['ruta' => 'bienvenida', 'label' => 'Inicio', 'icon' => 'bi-house'],
    ]],
    ['seccion' => 'AUXILIARES', 'items' => [
        ['ruta' => 'terceros',      'label' => 'Terceros',         'icon' => 'bi-people'],
        ['ruta' => 'centros_costo', 'label' => 'Centros de Costo', 'icon' => 'bi-diagram-3'],
    ]],
    ['seccion' => 'CONTABILIDAD', 'items' => [
        ['ruta' => 'asientos', 'label' => 'Asientos', 'icon' => 'bi-journal-text'],
        ['ruta' => 'cuentas',  'label' => 'Cuentas',  'icon' => 'bi-list-nested'],
        ['ruta' => 'periodos', 'label' => 'Períodos', 'icon' => 'bi-calendar3'],
    ]],
  // Cambiá esta parte en tu array $menuItems:
['seccion' => 'REPORTES', 'items' => [
    ['ruta' => 'rep_terceros', 'label' => 'Por Tercero', 'icon' => 'bi-bar-chart'],
    ['ruta' => 'mov_centro_costo', 'label' => 'Por Centro Costo', 'icon' => 'bi-bar-chart-line'],
]],
    ['seccion' => 'ADMINISTRACIÓN', 'items' => [
        ['ruta' => 'usuarios',  'label' => 'Usuarios',  'icon' => 'bi-person-gear'],
        ['ruta' => 'roles',     'label' => 'Roles',     'icon' => 'bi-shield'],
        ['ruta' => 'pantallas', 'label' => 'Pantallas', 'icon' => 'bi-grid'],
    ]],
];

$rutaActual     = $_GET['ruta'] ?? '';
$nombreCompleto = ($_SESSION['usuario']['nombre'] ?? '') . ' ' . ($_SESSION['usuario']['apellido'] ?? '');
$iniciales      = '';
foreach (explode(' ', trim($nombreCompleto)) as $palabra) {
    $iniciales .= strtoupper(mb_substr($palabra, 0, 1));
    if (strlen($iniciales) >= 2) break;
}

// ── BASE URL: ajusta aquí si tu carpeta tiene otro nombre ──
$base = 'index.php';
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">C</div>
        <span class="sidebar-title">ContaFlow</span>
        <button class="sidebar-toggle" onclick="toggleSidebar()" title="Colapsar menú">
            <i class="bi bi-layout-sidebar"></i>
        </button>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($menuItems as $seccion): ?>
            <div class="nav-section-label"><?= htmlspecialchars($seccion['seccion']) ?></div>
            <?php foreach ($seccion['items'] as $item):
                $esActivo = ($rutaActual === $item['ruta']) ? 'active' : '';
            ?>
                <a href="<?= $base ?>?ruta=<?= $item['ruta'] ?>" class="nav-item-link <?= $esActivo ?>">
                    <i class="bi <?= $item['icon'] ?>"></i>
                    <span><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
</aside>

<header class="topbar" id="topbar">
    <div></div>
    <div class="topbar-right">
        <div class="topbar-user" title="<?= htmlspecialchars($nombreCompleto) ?>">
            <div class="avatar"><?= htmlspecialchars($iniciales) ?></div>
            <span class="user-name"><?= htmlspecialchars($nombreCompleto) ?></span>
        </div>
        <a href="<?= $base ?>?ruta=logout" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Salir</span>
        </a>
    </div>
</header>

<div class="main-wrapper" id="mainWrapper">