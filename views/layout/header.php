<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo SITENAME ?? 'Sistema Contable'; ?>
    </title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <?php echo SITENAME ?? 'Sistema Contable v2'; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>Tercero/index">Terceros</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link text-white">Usuario:
                            <?php echo $_SESSION['usuario'] ?? 'Admin'; ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- JavaScript para vencimiento de sesión (HU3) -->
    <?php if (isset($_SESSION['usuario_id'])): ?>
    <script>
    // Tiempo de inactividad
    const INACTIVITY_TIME = 300000; // 5 minutos en milisegundos
    let inactivityTimer;
    let warningTimer;

    // Función para cerrar sesión por inactividad
    function logoutDueToInactivity() {
        clearTimeout(inactivityTimer);
        clearTimeout(warningTimer);
        
        // Mostrar mensaje
        alert('Su sesión ha expirado por inactividad');
        
        // Redirigir al logout con parámetro de expirado
        window.location.href = '<?php echo BASE_URL; ?>auth/logout?expired=1';
    }

    // Función para mostrar advertencia (1 minuto antes)
    function showInactivityWarning() {
        // Crear elemento de advertencia
        const warning = document.createElement('div');
        warning.id = 'sessionWarning';
        warning.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #ffc107;
            color: #000;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 9999;
            font-weight: bold;
        `;
        warning.innerHTML = '⚠️ Su sesión expirará en 1 minuto por inactividad';
        document.body.appendChild(warning);
        
        // Auto-cerrar advertencia después de 10 segundos
        setTimeout(() => {
            const warn = document.getElementById('sessionWarning');
            if (warn) warn.remove();
        }, 10000);
    }

    // Función para reiniciar el temporizador
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        clearTimeout(warningTimer);
        
        // Remover warning si existe
        const warning = document.getElementById('sessionWarning');
        if (warning) warning.remove();
        
        // Mostrar advertencia a los 4 minutos
        warningTimer = setTimeout(showInactivityWarning, 240000); // 4 minutos
        
        // Cerrar sesión a los 5 minutos
        inactivityTimer = setTimeout(logoutDueToInactivity, INACTIVITY_TIME);
    }

    // Eventos que reinician el timer
    document.addEventListener('DOMContentLoaded', resetInactivityTimer);
    document.addEventListener('mousemove', resetInactivityTimer);
    document.addEventListener('keypress', resetInactivityTimer);
    document.addEventListener('click', resetInactivityTimer);
    document.addEventListener('scroll', resetInactivityTimer);
    document.addEventListener('touchstart', resetInactivityTimer);
    document.addEventListener('submit', resetInactivityTimer);
    </script>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>