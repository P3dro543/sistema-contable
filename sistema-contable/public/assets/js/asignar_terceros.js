(() => {
  const terceros = Array.isArray(window.TERCEROS_ACTIVOS) ? window.TERCEROS_ACTIVOS : [];
  const baseUrl = typeof window.BASE_URL === "string" ? window.BASE_URL : "/";

  const modalEl = document.getElementById("modalAsignarTerceros");
  if (!modalEl) return;

  const modal = new bootstrap.Modal(modalEl);
  const tbody = document.querySelector("#tablaDistribucion tbody");
  const modalLineaId = document.getElementById("modalLineaId");
  const modalMontoLinea = document.getElementById("modalMontoLinea");
  const modalSumatoria = document.getElementById("modalSumatoria");
  const btnAgregarFila = document.getElementById("btnAgregarFila");
  const btnGuardar = document.getElementById("btnGuardarAsignacion");
  const alertError = document.getElementById("asignarError");

  let currentDetalleId = null;
  let currentMontoLinea = 0;

  const format2 = (n) => (Math.round((Number(n) || 0) * 100) / 100).toFixed(2);

  const clearError = () => {
    alertError.classList.add("d-none");
    alertError.textContent = "";
  };

  const showError = (msg) => {
    alertError.classList.remove("d-none");
    alertError.textContent = msg;
  };

  const buildSelect = () => {
    const select = document.createElement("select");
    select.className = "form-select form-select-sm tercero-select";
    const opt0 = document.createElement("option");
    opt0.value = "";
    opt0.textContent = "Seleccione...";
    select.appendChild(opt0);

    for (const t of terceros) {
      const opt = document.createElement("option");
      opt.value = String(t.id_tercero);
      opt.textContent = `${t.nombre}${t.identificacion ? " (" + t.identificacion + ")" : ""}`;
      select.appendChild(opt);
    }
    return select;
  };

  const addRow = (preset = null) => {
    const tr = document.createElement("tr");

    const tdTercero = document.createElement("td");
    const select = buildSelect();
    if (preset && preset.id_tercero) select.value = String(preset.id_tercero);
    tdTercero.appendChild(select);

    const tdMonto = document.createElement("td");
    tdMonto.className = "text-end";
    const input = document.createElement("input");
    input.type = "number";
    input.step = "0.01";
    input.min = "0";
    input.className = "form-control form-control-sm text-end monto-input";
    input.value = preset && preset.monto != null ? String(preset.monto) : "0.00";
    tdMonto.appendChild(input);

    const tdAcc = document.createElement("td");
    tdAcc.className = "text-end";
    const btnDel = document.createElement("button");
    btnDel.type = "button";
    btnDel.className = "btn btn-outline-danger btn-sm";
    btnDel.textContent = "X";
    btnDel.addEventListener("click", () => {
      tr.remove();
      recalc();
    });
    tdAcc.appendChild(btnDel);

    tr.appendChild(tdTercero);
    tr.appendChild(tdMonto);
    tr.appendChild(tdAcc);

    select.addEventListener("change", recalc);
    input.addEventListener("input", recalc);
    tbody.appendChild(tr);
    recalc();
  };

  const recalc = () => {
    clearError();

    let sum = 0;
    let ok = true;
    const rows = [...tbody.querySelectorAll("tr")];
    if (rows.length < 1) ok = false;

    for (const r of rows) {
      const sel = r.querySelector(".tercero-select");
      const inp = r.querySelector(".monto-input");
      const id = sel ? sel.value : "";
      const monto = Number(inp ? inp.value : 0);

      if (!id) ok = false;
      if (!Number.isFinite(monto) || monto < 0) ok = false;
      sum += Number.isFinite(monto) ? monto : 0;
    }

    modalSumatoria.textContent = format2(sum);
    const equals = Math.round(sum * 100) === Math.round(currentMontoLinea * 100);
    btnGuardar.disabled = !(ok && equals);
  };

  const openModal = (detalleId, montoLinea) => {
    currentDetalleId = Number(detalleId);
    currentMontoLinea = Number(montoLinea) || 0;

    modalLineaId.textContent = String(currentDetalleId);
    modalMontoLinea.textContent = format2(currentMontoLinea);
    tbody.innerHTML = "";
    clearError();
    addRow({ monto: format2(currentMontoLinea) });
    modal.show();
  };

  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".js-asignar-terceros");
    if (!btn) return;

    const puede = btn.getAttribute("data-puede") === "1";
    if (!puede) return;

    const idDetalle = btn.getAttribute("data-id-detalle");
    const monto = btn.getAttribute("data-monto");
    openModal(idDetalle, monto);
  });

  btnAgregarFila.addEventListener("click", () => addRow({ monto: "0.00" }));

  btnGuardar.addEventListener("click", async () => {
    clearError();
    btnGuardar.disabled = true;

    const distribucion = [...tbody.querySelectorAll("tr")].map((r) => {
      const sel = r.querySelector(".tercero-select");
      const inp = r.querySelector(".monto-input");
      return {
        id_tercero: sel ? Number(sel.value) : null,
        monto: inp ? Number(inp.value) : 0,
      };
    });

    try {
      const res = await fetch(`${baseUrl}ProrrateoTercero/guardar`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_detalle: currentDetalleId, distribucion }),
      });
      const json = await res.json().catch(() => null);
      if (!res.ok || !json || json.exito !== true) {
        showError((json && json.mensaje) || "No se pudo guardar la asignación.");
        recalc();
        return;
      }
      modal.hide();
    } catch (err) {
      showError("Error de red al guardar la asignación.");
      recalc();
    }
  });
})();

