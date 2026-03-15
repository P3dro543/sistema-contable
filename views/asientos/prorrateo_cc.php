<?php include VIEWS_PATH . 'layout/header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Prorrateo por Centros de Costo</h2>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text">Periodo</span>
                <select id="selectorPeriodo" class="form-select">
                    <!-- Se llena con JS desde periodos_contables -->
                </select>
            </div>
        </div>
    </div>

    <!-- Tabla de asientos -->
    <div class="card">
        <div class="card-body">
            <table class="table table-hover" id="tablaAsientos">
                <thead class="table-dark">
                    <tr>
                        <th>Consecutivo</th>
                        <th>Fecha</th>
                        <th>Código</th>
                        <th>Referencia</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="asientosBody">
                    <!-- Se llena con JS -->
                </tbody>
            </table>

            <!-- Paginación -->
            <nav id="paginacion"></nav>
        </div>
    </div>
</div>

<!-- Modal para asignar centros de costo -->
<div class="modal fade" id="modalProrrateo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Asignar Centros de Costo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Línea:</strong> <span id="lineaInfo"></span><br>
                    <strong>Monto total:</strong> ₡<span id="montoTotalLinea">0.00</span>
                </div>

                <div id="distribucionContainer">
                    <!-- Las filas se agregan dinámicamente -->
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-success btn-sm" id="agregarFila">
                        + Agregar centro de costo
                    </button>
                </div>

                <div class="alert alert-warning mt-3">
                    <strong>Total distribuido:</strong> ₡<span id="totalDistribuido">0.00</span>
                    <span id="validacionSuma" class="ms-3 text-danger"></span>
                </div>

                <input type="hidden" id="idDetalleActual">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardar" disabled>Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
// ===== CONFIGURACIÓN =====
const BASE_URL = '<?php echo BASE_URL; ?>';
let paginaActual = 1;
let periodos = [];
let centrosCosto = [];

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    cargarPeriodos();
    cargarCentrosCosto();
    cargarAsientos();
    
    document.getElementById('selectorPeriodo').addEventListener('change', function() {
        paginaActual = 1;
        cargarAsientos();
    });
    
    document.getElementById('agregarFila').addEventListener('click', agregarFilaDistribucion);
    document.getElementById('btnGuardar').addEventListener('click', guardarProrrateo);
});

// ===== CARGAR PERIODOS DESDE LA BD =====
function cargarPeriodos() {
    fetch(BASE_URL + 'AsientosController/getPeriodos')
        .then(response => response.json())
        .then(data => {
            periodos = data;
            const select = document.getElementById('selectorPeriodo');
            select.innerHTML = '';
            
            // Encontrar periodo activo (estado = 1)
            let periodoActivo = null;
            periodos.forEach(p => {
                const option = document.createElement('option');
                option.value = p.id_periodo;
                option.textContent = getNombreMes(p.mes) + ' ' + p.anio;
                if (p.estado == 1) {
                    option.selected = true;
                    periodoActivo = p.id_periodo;
                }
                select.appendChild(option);
            });
        });
}

function getNombreMes(mes) {
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return meses[mes - 1];
}

// ===== CARGAR CENTROS DE COSTO DESDE LA BD =====
function cargarCentrosCosto() {
    fetch(BASE_URL + 'AsientosController/getCentrosCosto')
        .then(response => response.json())
        .then(data => {
            centrosCosto = data;
        });
}

// ===== CARGAR ASIENTOS DESDE LA BD =====
function cargarAsientos() {
    const idPeriodo = document.getElementById('selectorPeriodo').value;
    
    fetch(`${BASE_URL}AsientosController/getAsientos?id_periodo=${idPeriodo}&pagina=${paginaActual}`)
        .then(response => response.json())
        .then(data => {
            renderizarAsientos(data.asientos);
            renderizarPaginacion(data.total_paginas, data.pagina_actual);
        });
}

function renderizarAsientos(asientos) {
    const tbody = document.getElementById('asientosBody');
    tbody.innerHTML = '';
    
    asientos.forEach(asiento => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${asiento.consecutivo}</td>
            <td>${asiento.fecha}</td>
            <td>${asiento.codigo}</td>
            <td>${asiento.referencia}</td>
            <td><span class="badge ${getBadgeClass(asiento.estado)}">${asiento.estado}</span></td>
            <td>
                <button class="btn btn-sm btn-info ver-detalle" data-id="${asiento.id_asiento}">
                    Ver detalle
                </button>
            </td>
        `;
        tbody.appendChild(fila);
    });
    
    document.querySelectorAll('.ver-detalle').forEach(btn => {
        btn.addEventListener('click', function() {
            toggleDetalle(this.dataset.id, this);
        });
    });
}

function getBadgeClass(estado) {
    switch(estado) {
        case 'Borrador': return 'bg-secondary';
        case 'Pendiente': return 'bg-warning';
        case 'Aprobado': return 'bg-success';
        default: return 'bg-secondary';
    }
}

// ===== DETALLE DEL ASIENTO =====
function toggleDetalle(idAsiento, boton) {
    const fila = boton.closest('tr');
    const filaDetalle = document.getElementById(`detalle-${idAsiento}`);
    
    if (filaDetalle) {
        filaDetalle.remove();
        boton.textContent = 'Ver detalle';
    } else {
        fetch(`${BASE_URL}AsientosController/getDetalleAsiento/${idAsiento}`)
            .then(response => response.json())
            .then(detalle => {
                const detalleHTML = generarDetalleHTML(detalle, idAsiento);
                fila.insertAdjacentHTML('afterend', detalleHTML);
                boton.textContent = 'Ocultar detalle';
            });
    }
}

function generarDetalleHTML(detalle, idAsiento) {
    let html = `<tr id="detalle-${idAsiento}" class="table-secondary">`;
    html += '<td colspan="6"><div class="p-3">';
    html += '<table class="table table-sm table-bordered mb-0">';
    html += '<thead><tr><th>Cuenta</th><th>Tipo</th><th>Monto</th><th>Descripción</th><th>Acción</th></tr></thead>';
    html += '<tbody>';
    
    detalle.forEach(linea => {
        html += '<tr>';
        html += `<td>${linea.codigo_cuenta} - ${linea.nombre_cuenta}</td>`;
        html += `<td>${linea.tipo_movimiento}</td>`;
        html += `<td class="text-end">₡${parseFloat(linea.monto).toLocaleString()}</td>`;
        html += `<td>${linea.descripcion || ''}</td>`;
        html += `<td>
            <button class="btn btn-sm btn-warning asignar-cc" 
                    data-id-detalle="${linea.id_detalle}"
                    data-monto="${linea.monto}"
                    data-cuenta="${linea.codigo_cuenta} - ${linea.nombre_cuenta}">
                Asignar centro costo
            </button>
        </td>`;
        html += '</tr>';
    });
    
    html += '</tbody></table></div></td></tr>';
    return html;
}

// ===== MODAL DE PRORRATEO =====
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('asignar-cc')) {
        const idDetalle = e.target.dataset.idDetalle;
        const monto = e.target.dataset.monto;
        const cuenta = e.target.dataset.cuenta;
        
        abrirModalProrrateo(idDetalle, monto, cuenta);
    }
});

function abrirModalProrrateo(idDetalle, monto, cuenta) {
    document.getElementById('idDetalleActual').value = idDetalle;
    document.getElementById('lineaInfo').textContent = cuenta;
    document.getElementById('montoTotalLinea').textContent = parseFloat(monto).toFixed(2);
    
    // Cargar distribución existente si la hay
    fetch(`${BASE_URL}AsientosController/getProrrateoCC/${idDetalle}`)
        .then(response => response.json())
        .then(distribucionExistente => {
            document.getElementById('distribucionContainer').innerHTML = '';
            
            if (distribucionExistente && distribucionExistente.length > 0) {
                distribucionExistente.forEach(item => {
                    agregarFilaDistribucion(item.id_centro_costo, item.monto);
                });
            } else {
                agregarFilaDistribucion();
            }
            
            calcularTotalDistribuido();
            new bootstrap.Modal(document.getElementById('modalProrrateo')).show();
        });
}

function agregarFilaDistribucion(idCentroSeleccionado = '', monto = '') {
    const container = document.getElementById('distribucionContainer');
    const fila = document.createElement('div');
    fila.className = 'row mb-2 align-items-center fila-distribucion';
    
    let options = '<option value="">Seleccione centro</option>';
    centrosCosto.forEach(cc => {
        const selected = (idCentroSeleccionado == cc.id_centro_costo) ? 'selected' : '';
        options += `<option value="${cc.id_centro_costo}" ${selected}>${cc.codigo} - ${cc.nombre}</option>`;
    });
    
    fila.innerHTML = `
        <div class="col-md-6">
            <select class="form-select centro-select">${options}</select>
        </div>
        <div class="col-md-4">
            <input type="number" class="form-control monto-input" step="0.01" placeholder="Monto" value="${monto}">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm eliminar-fila">Eliminar</button>
        </div>
    `;
    
    container.appendChild(fila);
    
    fila.querySelector('.monto-input').addEventListener('input', calcularTotalDistribuido);
    fila.querySelector('.eliminar-fila').addEventListener('click', function() {
        fila.remove();
        calcularTotalDistribuido();
    });
}

function calcularTotalDistribuido() {
    const montos = document.querySelectorAll('.monto-input');
    let total = 0;
    montos.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    const montoTotal = parseFloat(document.getElementById('montoTotalLinea').textContent) || 0;
    
    document.getElementById('totalDistribuido').textContent = total.toFixed(2);
    
    const validacion = document.getElementById('validacionSuma');
    const btnGuardar = document.getElementById('btnGuardar');
    
    if (Math.abs(total - montoTotal) < 0.01) {
        validacion.textContent = '✅ Correcto';
        validacion.className = 'ms-3 text-success';
        btnGuardar.disabled = false;
    } else {
        validacion.textContent = '❌ No coincide con el monto total';
        validacion.className = 'ms-3 text-danger';
        btnGuardar.disabled = true;
    }
}

function guardarProrrateo() {
    const idDetalle = document.getElementById('idDetalleActual').value;
    const filas = document.querySelectorAll('.fila-distribucion');
    const distribucion = [];
    
    filas.forEach(fila => {
        const select = fila.querySelector('.centro-select');
        const monto = fila.querySelector('.monto-input');
        
        if (select.value && monto.value) {
            distribucion.push({
                id_centro_costo: parseInt(select.value),
                monto: parseFloat(monto.value)
            });
        }
    });
    
    const data = {
        id_detalle: parseInt(idDetalle),
        distribucion: distribucion
    };
    
    fetch(BASE_URL + 'ProrrateoController/guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.exito) {
            alert('Prorrateo guardado correctamente');
            bootstrap.Modal.getInstance(document.getElementById('modalProrrateo')).hide();
        } else {
            alert('Error: ' + result.mensaje);
        }
    });
}

// ===== PAGINACIÓN =====
function renderizarPaginacion(totalPaginas, paginaActual) {
    const nav = document.getElementById('paginacion');
    if (totalPaginas <= 1) {
        nav.innerHTML = '';
        return;
    }
    
    let html = '<ul class="pagination justify-content-center">';
    
    html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">`;
    html += `<a class="page-link" href="#" data-pagina="${paginaActual - 1}">Anterior</a></li>`;
    
    for (let i = 1; i <= totalPaginas; i++) {
        html += `<li class="page-item ${i === paginaActual ? 'active' : ''}">`;
        html += `<a class="page-link" href="#" data-pagina="${i}">${i}</a></li>`;
    }
    
    html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">`;
    html += `<a class="page-link" href="#" data-pagina="${paginaActual + 1}">Siguiente</a></li>`;
    
    html += '</ul>';
    nav.innerHTML = html;
    
    nav.querySelectorAll('a.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const pagina = parseInt(this.dataset.pagina);
            if (!isNaN(pagina) && pagina >= 1 && pagina <= totalPaginas) {
                paginaActual = pagina;
                cargarAsientos();
            }
        });
    });
}
</script>

<style>
.fila-distribucion {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
}
.badge { font-size: 0.9em; padding: 0.5em 0.8em; }
.table-secondary { background-color: #f8f9fa; }
</style>

<?php include VIEWS_PATH . 'layout/footer.php'; ?>