</div><!-- /.main-wrapper -->

<!-- Modal de confirmación -->
<div class="modal-cf-overlay" id="modalEliminar">
    <div class="modal-cf-box">
        <h5><i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirmar eliminación</h5>
        <p id="modalEliminarTexto">¿Realmente desea eliminar el elemento seleccionado?</p>
        <div class="modal-cf-actions">
            <button class="btn-cf btn-cf-secondary" onclick="cerrarModal('modalEliminar')">No</button>
            <form id="formEliminar" method="POST" style="display:inline">
                <input type="hidden" name="id_direccion" id="modal_id_direccion">
                <input type="hidden" name="id_tercero"   id="modal_id_tercero">
                <input type="hidden" name="id_registro"  id="modal_id_registro">
                <button type="submit" class="btn-cf btn-cf-danger"
                        style="background:var(--brand-danger);color:#fff;border:none;">
                    Sí, eliminar
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ── SIDEBAR ── */
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
    document.getElementById('topbar').classList.toggle('sidebar-collapsed');
    document.getElementById('mainWrapper').classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebarCollapsed',
        document.getElementById('sidebar').classList.contains('collapsed') ? '1' : '0');
}

(function () {
    if (localStorage.getItem('sidebarCollapsed') === '1') {
        document.getElementById('sidebar').classList.add('collapsed');
        document.getElementById('topbar').classList.add('sidebar-collapsed');
        document.getElementById('mainWrapper').classList.add('sidebar-collapsed');
    }
})();

/* ── MODAL ── */
function abrirModalEliminar(config) {
    const modal   = document.getElementById('modalEliminar');
    const formEl  = document.getElementById('formEliminar');
    const textoEl = document.getElementById('modalEliminarTexto');

    formEl.action       = config.accion || '';
    textoEl.textContent = config.texto  || '¿Realmente desea eliminar el elemento seleccionado?';

    if (config.campos) {
        Object.entries(config.campos).forEach(([nombre, valor]) => {
            const input = document.getElementById('modal_' + nombre);
            if (input) input.value = valor;
        });
    }
    modal.classList.add('show');
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('show');
}

document.getElementById('modalEliminar').addEventListener('click', function (e) {
    if (e.target === this) cerrarModal('modalEliminar');
});

/* ── AUX4: VENCIMIENTO DE SESIÓN (5 min) ── */
(function () {
    const TIMEOUT_MS = 5 * 60 * 1000;
    let timer;

    function resetTimer() {
        clearTimeout(timer);
        timer = setTimeout(mostrarAviso, TIMEOUT_MS);
    }

    function mostrarAviso() {
        const aviso = document.createElement('div');
        aviso.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.8);display:flex;align-items:center;justify-content:center;z-index:9999;';
        aviso.innerHTML = `
            <div style="background:#161b22;border:1px solid #21262d;border-radius:12px;padding:2rem;max-width:360px;text-align:center;">
                <i class="bi bi-clock-history" style="font-size:2.5rem;color:#e3b341;"></i>
                <h5 style="margin:1rem 0 .5rem;font-family:'Syne',sans-serif;">Sesión expirada</h5>
                <p style="color:#7d8590;font-size:.9rem;margin-bottom:1.5rem;">
                    Su sesión ha expirado por inactividad. Será redirigido al inicio de sesión.
                </p>
                <div style="width:100%;height:4px;background:#21262d;border-radius:4px;overflow:hidden;">
                    <div id="progressBar" style="height:100%;width:100%;background:#2ea84e;transition:width 3s linear;"></div>
                </div>
            </div>`;
        document.body.appendChild(aviso);
        setTimeout(() => { document.getElementById('progressBar').style.width = '0%'; }, 50);
        setTimeout(() => { window.location.href = 'index.php?ruta=logout'; }, 3000);
    }

    ['mousemove', 'keydown', 'scroll', 'click', 'touchstart'].forEach(ev => {
        document.addEventListener(ev, resetTimer, { passive: true });
    });

    resetTimer();
})();
</script>
</body>
</html>