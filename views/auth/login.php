<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | ContaFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
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
            --font-body:     'DM Sans', sans-serif;
            --font-display:  'Syne', sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-body); background: var(--brand-dark); color: var(--brand-text); min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        body::before { content: ''; position: fixed; inset: 0; background-image: linear-gradient(var(--brand-border) 1px, transparent 1px), linear-gradient(90deg, var(--brand-border) 1px, transparent 1px); background-size: 40px 40px; opacity: .4; pointer-events: none; }
        body::after { content: ''; position: fixed; width: 600px; height: 600px; background: radial-gradient(circle, rgba(46,168,78,.12) 0%, transparent 70%); top: -100px; left: -100px; pointer-events: none; }

        .login-wrapper { position: relative; z-index: 10; width: 100%; max-width: 420px; padding: 1rem; }
        .login-card { background: var(--brand-surface); border: 1px solid var(--brand-border); border-radius: 16px; padding: 2.5rem 2.25rem; box-shadow: 0 24px 64px rgba(0,0,0,.5); animation: fadeUp .4s ease both; }
        @keyframes fadeUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .brand-block { text-align: center; margin-bottom: 2rem; }
        .brand-icon { width: 56px; height: 56px; background: linear-gradient(135deg, var(--brand-accent), #0ca678); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; box-shadow: 0 8px 24px rgba(46,168,78,.3); }
        .brand-icon svg { width: 28px; height: 28px; fill: #fff; }
        .brand-name { font-family: var(--font-display); font-size: 1.5rem; font-weight: 800; letter-spacing: -.5px; color: var(--brand-text); }
        .brand-tagline { font-size: .8rem; color: var(--brand-muted); margin-top: .25rem; }

        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: .78rem; font-weight: 500; color: var(--brand-muted); margin-bottom: .4rem; letter-spacing: .04em; text-transform: uppercase; }
        .input-wrapper { position: relative; }
        .input-icon { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: var(--brand-muted); font-size: 1rem; pointer-events: none; transition: color .2s; }
        .form-control-cf { width: 100%; background: var(--brand-dark); border: 1px solid var(--brand-border); color: var(--brand-text); padding: .65rem .85rem .65rem 2.5rem; border-radius: 8px; font-size: .9rem; font-family: var(--font-body); transition: border-color .2s, box-shadow .2s; }
        .form-control-cf:focus { outline: none; border-color: var(--brand-accent); box-shadow: 0 0 0 3px rgba(46,168,78,.15); }
        .input-wrapper:focus-within .input-icon { color: var(--brand-accent); }
        .btn-toggle-pass { position: absolute; right: .85rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--brand-muted); cursor: pointer; padding: 0; font-size: 1rem; transition: color .2s; }
        .btn-toggle-pass:hover { color: var(--brand-text); }

        .alert-login { display: flex; align-items: flex-start; gap: .6rem; padding: .75rem 1rem; border-radius: 8px; font-size: .85rem; margin-bottom: 1.5rem; animation: fadeUp .3s ease; }
        .alert-danger  { background: rgba(218,54,51,.1); border: 1px solid rgba(218,54,51,.25); color: #f85149; }
        .alert-warning { background: rgba(210,153,34,.1); border: 1px solid rgba(210,153,34,.25); color: #e3b341; }
        .alert-info    { background: rgba(56,139,253,.1); border: 1px solid rgba(56,139,253,.25); color: #58a6ff; }
        .alert-success { background: rgba(46,168,78,.1); border: 1px solid rgba(46,168,78,.25); color: #3fb950; }

        .btn-login { width: 100%; padding: .75rem; background: var(--brand-accent); color: #fff; border: none; border-radius: 8px; font-size: .9rem; font-weight: 600; font-family: var(--font-body); cursor: pointer; transition: background .2s, transform .1s, box-shadow .2s; margin-top: .5rem; display: flex; align-items: center; justify-content: center; gap: .5rem; }
        .btn-login:hover { background: var(--brand-accent2); box-shadow: 0 4px 16px rgba(46,168,78,.3); }
        .btn-login:active { transform: scale(.98); }

        .login-footer { text-align: center; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--brand-border); font-size: .75rem; color: var(--brand-muted); }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="brand-block">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 4h11v2H4V4zm0 4h11v2H4V8zm0 4h7v2H4v-2zm9 2.5v1.8l3 2.7 3-2.7v-1.8h-6zm3 7l-4-3.6V13h8v4.9L16 21z"/>
                </svg>
            </div>
            <div class="brand-name">ContaFlow</div>
            <div class="brand-tagline">Sistema Contable · Desarrollos Ordenados S.A.</div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert-login alert-<?= htmlspecialchars($mensaje['tipo']) ?>">
                <?php
                $iconos = ['danger' => 'bi-x-circle', 'warning' => 'bi-exclamation-triangle',
                           'info'   => 'bi-info-circle', 'success' => 'bi-check-circle'];
                $icono  = $iconos[$mensaje['tipo']] ?? 'bi-info-circle';
                ?>
                <i class="bi <?= $icono ?>"></i>
                <span><?= htmlspecialchars($mensaje['texto']) ?></span>
            </div>
        <?php endif; ?>

        <!-- CORRECCIÓN: action sin barra inicial -->
        <form method="POST" action="index.php?ruta=login_post" novalidate>

            <div class="form-group">
                <label class="form-label" for="username">Usuario</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" class="form-control-cf"
                           placeholder="Ingrese su usuario" autocomplete="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    <i class="bi bi-person input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-control-cf"
                           placeholder="Ingrese su contraseña" autocomplete="current-password" required>
                    <i class="bi bi-lock input-icon"></i>
                    <button type="button" class="btn-toggle-pass" onclick="togglePass()">
                        <i class="bi bi-eye" id="iconoPass"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i>
                Ingresar al sistema
            </button>
        </form>

        <div class="login-footer">
            ContaFlow v2.0 &nbsp;·&nbsp; Colegio Universitario de Cartago
        </div>
    </div>
</div>

<script>
function togglePass() {
    const input = document.getElementById('password');
    const icono = document.getElementById('iconoPass');
    if (input.type === 'password') {
        input.type = 'text';
        icono.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icono.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>