// ============================================================
// CARGAR Y MOSTRAR PRODUCTOS DEL CARRITO
// ============================================================

const ORDER_CREATE_QUOTE_URL = (window.ORDER_CREATE_QUOTE_URL || 'api/orders/create_quote.php').trim();
const QUOTE_CSRF_TOKEN = (window.QUOTE_CSRF_TOKEN || '').trim();
const INDUSTRIAL_WHATSAPP = '50230888818';
let cotizacionEnCurso = false;

const ETIQUETAS_FORMA_PAGO = {
    transferencia: 'Transferencia bancaria',
    efectivo: 'Efectivo al recoger en tienda',
    tarjeta: 'Tarjeta al recoger en tienda'
};

const OPCION_RETIRO_TIENDA = {
    value: 'recoge_tienda',
    label: 'Recogida en tienda',
    costo: 0,
    detalle: 'Recogida en tienda coordinada con Industrial Collectors.'
};

function actualizarNiceSelect(select) {
    if (window.jQuery && typeof window.jQuery.fn.niceSelect === 'function' && select) {
        window.jQuery(select).niceSelect('update');
    }
}

function obtenerEntregaSeleccionada() {
    const formaPago = document.getElementById('formaPago');
    return formaPago && Object.prototype.hasOwnProperty.call(ETIQUETAS_FORMA_PAGO, formaPago.value)
        ? OPCION_RETIRO_TIENDA
        : null;
}

function actualizarNotaEntrega() {
    const nota = document.getElementById('modalidadEntregaNota');
    const metodo = document.getElementById('formaPago')?.value;
    if (nota) {
        nota.textContent = metodo === 'transferencia'
            ? 'Coordina la transferencia y la recogida con nuestro equipo. Esta cotización no confirma ningún pago.'
            : 'Todas las cotizaciones son para recoger en tienda. El efectivo y la tarjeta se pagan presencialmente al recoger.';
    }
}

function formatearMoneda(valor) {
    return `Q${(parseFloat(valor) || 0).toFixed(2)}`;
}

function sanitizarInput(valor) {
    return String(valor ?? '')
        .replace(/[<>]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
}

function escaparHtml(valor) {
    return String(valor ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function calcularTotalesPedido() {
    if (typeof Carrito === 'undefined') {
        console.error('Carrito no esta disponible. Verifica que assets/js/carrito.js cargue antes de cart.js.');

        return {
            subtotalNormal: 0,
            totalNormal: 0,
            totalSeleccionado: 0
        };
    }

    const subtotalNormal = Carrito.obtenerSubtotal();
    const entrega = obtenerEntregaSeleccionada();
    const costoEnvio = entrega ? entrega.costo : 0;
    const total = subtotalNormal + costoEnvio;

    return {
        subtotalNormal,
        costoEnvio,
        totalNormal: total,
        totalSeleccionado: total
    };
}

// ============================================================
// CARGAR PRODUCTOS DEL CARRITO DESDE LOCALSTORAGE
// ============================================================
function cargarCarrito() {
    const tbody = document.querySelector('.cart-table tbody');

    if (!tbody) {
        console.error('No se encontro el tbody del carrito');
        return;
    }

    if (typeof Carrito === 'undefined') {
        console.error('Carrito no esta disponible. Verifica assets/js/carrito.js.');

        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center">
                    <div class="alert alert-danger">
                        <h4>No se pudo cargar el carrito</h4>
                        <p>Recarga la pagina o intenta nuevamente.</p>
                    </div>
                </td>
            </tr>
        `;

        actualizarTotales(0);
        return;
    }

    const carrito = Carrito.obtenerCarrito();

    if (carrito.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center cart-empty-cell">
                    <div class="alert alert-info cart-empty-state">
                        <h4>Tu carrito esta vacio</h4>
                        <p>Agrega productos desde la tienda para solicitar una cotización.</p>
                        <a href="tienda.php" class="default-btn">Ir a la Tienda</a>
                    </div>
                </td>
            </tr>
        `;
        actualizarTotales(0);
        return;
    }

    tbody.innerHTML = '';

    carrito.forEach((producto) => {
        const imagen = typeof window.BM_resolveProductImage === 'function'
            ? window.BM_resolveProductImage(producto.imagen)
            : (producto.imagen || (window.BM_PRODUCT_IMAGE_FALLBACK || 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png'));
        const precio = parseFloat(producto.precio_normal || producto.precio) || 0;
        const cantidad = Carrito.normalizarCantidad(producto.cantidad || 1);
        const total = precio * cantidad;

        const idArticulo = escaparHtml(producto.idarticulo);
        const nombreProducto = escaparHtml(producto.nombre || 'Producto');
        const imagenProducto = escaparHtml(imagen);
        const claseImagenFallback = typeof window.BM_isProductImageFallbackUrl === 'function' && window.BM_isProductImageFallbackUrl(imagen)
            ? ' class="bm-product-image-fallback"'
            : '';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="product-thumbnail">
                <a href="javascript:void(0)" class="remove" onclick="eliminarDelCarrito('${idArticulo}')">
                    <i class='bx bx-x'></i>
                </a>
                <a href="#">
                    <img src="${imagenProducto}" alt="${nombreProducto}"${claseImagenFallback} onerror="window.BM_applyProductImageFallback ? window.BM_applyProductImageFallback(this) : (this.onerror=null,this.src='${window.BM_PRODUCT_IMAGE_FALLBACK || 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png'}')">
                </a>
            </td>
            <td class="product-name">
                <a href="producto.php?id=${idArticulo}">${nombreProducto}</a>
            </td>
            <td class="product-price">
                <span class="unit-amount">${formatearMoneda(precio)}</span>
            </td>
            <td class="product-quantity">
                <div class="input-counter">
                    <span class="minus-btn" onclick="cambiarCantidad('${idArticulo}', -1)">
                        <i class='bx bx-minus'></i>
                    </span>
                    <input type="text" value="${cantidad}" id="cantidad-${idArticulo}"
                           class="cart-quantity-input" inputmode="numeric" pattern="[0-9]*" min="1" max="99"
                           aria-label="Cantidad de ${nombreProducto}"
                           onchange="actualizarCantidadInput('${idArticulo}', this.value)">
                    <span class="plus-btn" onclick="cambiarCantidad('${idArticulo}', 1)">
                        <i class='bx bx-plus'></i>
                    </span>
                </div>
            </td>
            <td class="product-subtotal">
                <span class="subtotal-amount">${formatearMoneda(total)}</span>
            </td>
        `;

        tbody.appendChild(tr);
    });

    actualizarTotales();
}

// Inicializar carrito cuando el DOM este listo
document.addEventListener("DOMContentLoaded", function () {
    cargarCarrito();
    inicializarEventos();

    const nombre = document.getElementById("nombreCompleto");
    const direccion = document.getElementById("direccion");
    const telefono = document.getElementById("telefono");
    const correo = document.getElementById("correo");
    const numeroDocumento = document.getElementById("numeroDocumento");
    const tipoDocumento = document.getElementById("tipoDocumento");
    const formaPago = document.getElementById("formaPago");

    if (nombre) {
        nombre.addEventListener("input", function () {
            this.value = this.value.replace(/[^\p{L}\s]/gu, "");
            limpiarError(this, "errorNombreCompleto");
        });
    }

    if (direccion) {
        direccion.addEventListener("input", function () {
            this.value = this.value.replace(/[^\p{L}0-9\s#\-,.]/gu, "");
            limpiarError(this, "errorDireccion");
        });
    }

    if (telefono) {
        telefono.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 8);
            limpiarError(this, "errorTelefono");
        });
    }

    if (correo) {
        correo.addEventListener("input", function () {
            limpiarError(this, "errorCorreo");
        });
    }

    if (numeroDocumento) {
        numeroDocumento.addEventListener("input", function () {
            const tipo = tipoDocumento ? tipoDocumento.value : "";

            if (tipo === "dpi") {
                this.value = this.value.replace(/\D/g, "").slice(0, 13);
            }

            if (tipo === "nit") {
                this.value = this.value.replace(/[^0-9Kk\-]/g, "").slice(0, 20);
            }

            limpiarError(this, "errorNumeroDocumento");
        });
    }

    if (tipoDocumento && numeroDocumento) {
        tipoDocumento.addEventListener("change", function () {
            numeroDocumento.value = this.value === 'cf' ? 'C/F' : '';
            limpiarError(tipoDocumento, "errorTipoDocumento");
            limpiarError(numeroDocumento, "errorNumeroDocumento");
        });
    }

    if (formaPago) {
        const refrescarResumenFormaPago = function () {
            limpiarError(formaPago, "errorFormaPago");
            actualizarNotaEntrega();
            window.setTimeout(actualizarResumenPedido, 0);
        };

        formaPago.addEventListener("change", refrescarResumenFormaPago);
        formaPago.addEventListener("input", refrescarResumenFormaPago);

        document.addEventListener("click", function (event) {
            const modal = document.getElementById('modalPedido');
            if (!modal || !modal.contains(event.target)) {
                return;
            }

            if (event.target.closest('#formaPago') || event.target.closest('.nice-select')) {
                window.setTimeout(actualizarResumenPedido, 80);
            }
        });
    }

    actualizarNotaEntrega();

    const modalPedido = document.getElementById('modalPedido');
    if (modalPedido) {
        modalPedido.addEventListener('click', function(e) {
            if (e.target === modalPedido) {
                cerrarModalPedido();
            }
        });
    }
});

// ============================================================
// ACTUALIZAR TOTALES DEL CARRITO
// ============================================================
function actualizarTotales(subtotal = null) {
    if (subtotal === null) {
        subtotal = Carrito.obtenerSubtotal();
    }
    
    const totales = calcularTotalesPedido();
    
    const totalNormalElement = document.querySelector('.cart-total-normal strong');
    
    if (totalNormalElement) totalNormalElement.textContent = formatearMoneda(totales.totalNormal);

    actualizarResumenPedido();
}

function actualizarResumenPedido() {
    const metodo = document.getElementById('formaPago')?.value || '';
    const totalElement = document.getElementById('pedidoTotalModal');
    const detalleElement = document.getElementById('resumenPagoDetalle');
    const resumenBox = document.getElementById('resumenPagoPedido');
    if (!totalElement || !detalleElement) return;

    const entrega = obtenerEntregaSeleccionada();
    totalElement.textContent = formatearMoneda(calcularTotalesPedido().totalSeleccionado);
    totalElement.hidden = false;
    detalleElement.textContent = entrega
        ? `${ETIQUETAS_FORMA_PAGO[metodo]}. Recogida en tienda; pendiente de confirmación, sin cobro en línea.`
        : 'Selecciona la forma de pago prevista. Recogida en tienda.';
    resumenBox?.classList.toggle('mp-total-unselected', !entrega);
    actualizarNotaEntrega();
}

function crearMensajeCotizacion(numeroCotizacion, formData, referencia) {
    const lineas = [
        'COTIZACIÓN - INDUSTRIAL COLLECTORS, S.A.',
        `Cotización: #${numeroCotizacion}`
    ];
    if (referencia) lineas.push(`Referencia: ${referencia}`);
    lineas.push(
        `Cliente: ${formData.nombreCompleto}`,
        `Teléfono: ${formData.telefono}`,
        `Correo: ${formData.correo}`,
        `Dirección del cliente: ${formData.direccion}`,
        `Pago previsto: ${ETIQUETAS_FORMA_PAGO[formData.formaPago]}`,
        'Recogida en tienda; coordinar sucursal y disponibilidad.',
        '',
        'PRODUCTOS'
    );
    formData.carrito.forEach((producto, index) => {
        const cantidad = Number(producto.cantidad || 1);
        const precio = Number(producto.precio_normal || producto.precio || 0);
        lineas.push(`${index + 1}. ${producto.nombre} | ${cantidad} x ${formatearMoneda(precio)} = ${formatearMoneda(cantidad * precio)}`);
    });
    lineas.push('', `Total estimado: ${formatearMoneda(formData.total)}`, 'Cotización pendiente de confirmación. No se ha realizado ningún cobro.');
    return lineas.join('\n');
}

function finalizarPedidoExitoso(numeroCotizacion, formData, referencia = '') {
    const mensaje = crearMensajeCotizacion(numeroCotizacion, formData, referencia);
    const whatsappUrl = `https://wa.me/${INDUSTRIAL_WHATSAPP}?text=${encodeURIComponent(mensaje)}`;
    const confirmacion = document.getElementById('cotizacion-confirmacion');
    const referenciaElement = document.getElementById('cotizacion-referencia');
    const whatsappLink = document.getElementById('cotizacion-whatsapp');
    if (referenciaElement) referenciaElement.textContent = `#${numeroCotizacion}`;
    if (whatsappLink) whatsappLink.href = whatsappUrl;
    if (confirmacion) confirmacion.hidden = false;

    // La cotización ya existe en SOL: un problema de interfaz no debe volver a enviarla.
    try {
        Carrito.limpiarCarrito();
        cargarCarrito();
    } catch (error) {
        console.error('No fue posible actualizar el carrito después de registrar la cotización.');
    }
    cerrarModalPedido(true);
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Cotización registrada',
            html: `Cotización <strong>#${escaparHtml(numeroCotizacion)}</strong> registrada en el sistema.<br><br>Envía el detalle a Industrial para coordinar la recogida.<br><br><a href="${escaparHtml(whatsappUrl)}" target="_blank" rel="noopener noreferrer">Enviar cotización por WhatsApp</a>`,
            confirmButtonText: 'Cerrar'
        });
    } else {
        alert(`Cotización #${numeroCotizacion} registrada. Usa el botón Enviar por WhatsApp para coordinar la recogida.`);
    }
}

function restaurarBotonPedido(btnEnviar) {
    if (btnEnviar) {
        btnEnviar.disabled = false;
        btnEnviar.textContent = 'Enviar cotización';
    }
}

function mostrarErrorPedido(mensaje, btnEnviar) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje,
            confirmButtonText: 'Aceptar'
        }).then(() => restaurarBotonPedido(btnEnviar));
    } else {
        alert(mensaje);
        restaurarBotonPedido(btnEnviar);
    }
}

// ============================================================
// ELIMINAR PRODUCTO DEL CARRITO
// ============================================================
function eliminarDelCarrito(idarticulo) {
    if (cotizacionEnCurso) return;
    if (confirm('Esta seguro de que desea eliminar este producto del carrito?')) {
        Carrito.eliminarProducto(idarticulo);
        cargarCarrito();
    }
}

// ============================================================
// CAMBIAR CANTIDAD DE PRODUCTO
// ============================================================
function cambiarCantidad(idarticulo, cambio) {
    if (cotizacionEnCurso) return;
    const carrito = Carrito.obtenerCarrito();
    const producto = carrito.find(item => item.idarticulo == idarticulo);
    
    if (producto) {
        const cantidadActual = Carrito.normalizarCantidad(producto.cantidad || 1);
        const paso = parseInt(cambio, 10) >= 0 ? 1 : -1;
        const nuevaCantidad = cantidadActual + paso;

        if (nuevaCantidad < 1) {
            eliminarDelCarrito(idarticulo);
        } else {
            const actualizado = Carrito.actualizarCantidad(idarticulo, nuevaCantidad);
            if (actualizado) {
                cargarCarrito();
            }
        }
    }
}

// ============================================================
// ACTUALIZAR CANTIDAD DESDE INPUT
// ============================================================
function actualizarCantidadInput(idarticulo, valor) {
    if (cotizacionEnCurso) {
        cargarCarrito();
        return;
    }
    const cantidad = Carrito.normalizarCantidad(valor, 1);
    const input = document.getElementById(`cantidad-${idarticulo}`);

    if (input) {
        input.value = cantidad;
    }

    if (Carrito.actualizarCantidad(idarticulo, cantidad)) {
        cargarCarrito();
    }
}

// ============================================================
// INICIALIZAR EVENTOS
// ============================================================
function inicializarEventos() {
    document.addEventListener('input', function(e) {
        if (e.target.classList && e.target.classList.contains('cart-quantity-input')) {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 2);
        }
    });

    // Evento para el boton "Update Cart"
    const updateBtn = document.querySelector('.cart-buttons .default-btn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            cargarCarrito();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Carrito actualizado',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    }
}

// ============================================================
// MODAL DE PEDIDO
// ============================================================

// Abrir modal de pedido
function abrirModalPedido() {
    if (cotizacionEnCurso) return;
    // Verificar que el carrito no este vacio
    const carrito = Carrito.obtenerCarrito();
    if (carrito.length === 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Carrito vacio',
                text: 'Agrega productos al carrito antes de continuar',
                confirmButtonText: 'Aceptar'
            });
        } else {
            alert('El carrito esta vacio. Agrega productos antes de continuar.');
        }
        return;
    }

    // Limpiar formulario
    document.getElementById('formPedido').reset();
    toggleNit();
    document.getElementById('documentoHelp').textContent = '';
    document.querySelectorAll('#formPedido .input-error').forEach(element => { element.textContent = ''; });
    document.querySelectorAll('#formPedido .input-invalid').forEach(element => element.classList.remove('input-invalid'));
    actualizarNiceSelect(document.getElementById('tipoDocumento'));
    
    document.getElementById('formaPago').value = '';
    const modalidadEntrega = document.getElementById('modalidadEntrega');
    if (modalidadEntrega) {
        modalidadEntrega.value = 'recoge_tienda';
    }
    actualizarNiceSelect(document.getElementById('formaPago'));
    actualizarResumenPedido();
    
    // Mostrar modal
    const modalPedido = document.getElementById('modalPedido');
    modalPedido.classList.add('show');
    const modalBox = modalPedido.querySelector('.mp-modal');
    if (modalBox) {
        modalBox.scrollTop = 0;
    }
}

// Cerrar modal de pedido
function cerrarModalPedido(forzar = false) {
    if (cotizacionEnCurso && !forzar) return;
    document.getElementById('modalPedido').classList.remove('show');
}

function establecerEnvioCotizacion(enCurso) {
    cotizacionEnCurso = enCurso;
    const modal = document.getElementById('modalPedido');
    if (modal) modal.setAttribute('aria-busy', String(enCurso));
    document.querySelectorAll('#modalPedido .mp-close, #modalPedido .mp-btn-cancelar').forEach(button => {
        button.disabled = enCurso;
    });
}

// Mostrar/ocultar campo de documento segun checkbox
function toggleNit() {
    const checkbox = document.getElementById('necesitaFactura');
    if (!checkbox) return;
    
    const nitGroup = document.getElementById('nit-group');
    const numeroDocumentoGroup = document.getElementById('numeroDocumento-group');
    const tipoDocumento = document.getElementById('tipoDocumento');
    const numeroDocumento = document.getElementById('numeroDocumento');
    
    if (!nitGroup || !numeroDocumentoGroup || !tipoDocumento || !numeroDocumento) return;
    
    if (checkbox.checked) {
        nitGroup.style.display = 'block';
        tipoDocumento.setAttribute('required', 'required');
    } else {
        nitGroup.style.display = 'none';
        numeroDocumentoGroup.style.display = 'none';
        tipoDocumento.removeAttribute('required');
        numeroDocumento.removeAttribute('required');
        tipoDocumento.value = '';
        numeroDocumento.value = '';
    }
}

// Cambiar tipo de documento (NIT o DPI)
function cambiarTipoDocumento() {
    const tipoDocumento = document.getElementById('tipoDocumento').value;
    const numeroDocumentoGroup = document.getElementById('numeroDocumento-group');
    const labelNumeroDocumento = document.getElementById('labelNumeroDocumento');
    const numeroDocumento = document.getElementById('numeroDocumento');
    const documentoHelp = document.getElementById('documentoHelp');
    
    if (tipoDocumento === '') {
        numeroDocumentoGroup.style.display = 'none';
        numeroDocumento.removeAttribute('required');
        numeroDocumento.value = '';
        return;
    }

    if (tipoDocumento === 'cf') {
        numeroDocumentoGroup.style.display = 'none';
        numeroDocumento.removeAttribute('required');
        numeroDocumento.value = 'C/F';
        if (documentoHelp) documentoHelp.textContent = '';
        return;
    }
    
    numeroDocumentoGroup.style.display = 'block';
    numeroDocumento.setAttribute('required', 'required');
    numeroDocumento.value = '';
    
    if (tipoDocumento === 'nit') {
        labelNumeroDocumento.innerHTML = 'NIT <span class="required">*</span>';
        numeroDocumento.setAttribute('maxlength', '12');
        documentoHelp.textContent = 'El NIT debe tener menos de 13 caracteres';
        documentoHelp.style.color = '#666';
    } else if (tipoDocumento === 'dpi') {
        labelNumeroDocumento.innerHTML = 'DPI <span class="required">*</span>';
        numeroDocumento.setAttribute('maxlength', '13');
        documentoHelp.textContent = 'El DPI debe tener 13 caracteres';
        documentoHelp.style.color = '#666';
    }
    
    validarDocumento();
}

// Validar el documento segun el tipo
function validarDocumento() {
    const tipoDocumento = document.getElementById('tipoDocumento').value;
    const numeroDocumentoInput = document.getElementById('numeroDocumento');
    const numeroDocumento = numeroDocumentoInput.value.trim();
    const documentoHelp = document.getElementById('documentoHelp');

    if (!tipoDocumento) {
        documentoHelp.textContent = '';
        return;
    }

    if (tipoDocumento === 'cf') {
        numeroDocumentoInput.value = 'C/F';
        if (documentoHelp) documentoHelp.textContent = '';
        return;
    }

    if (tipoDocumento === 'dpi') {
        numeroDocumentoInput.value = numeroDocumento.replace(/\D/g, '').slice(0, 13);
    }

    if (tipoDocumento === 'nit') {
        numeroDocumentoInput.value = numeroDocumento.replace(/[^0-9Kk\-]/g, '').slice(0, 20);
    }

    const numeroDocumentoLimpiado = numeroDocumentoInput.value.trim();

    if (!numeroDocumentoLimpiado) {
        documentoHelp.textContent = '';
        return;
    }

    if (tipoDocumento === 'dpi') {
        if (numeroDocumentoLimpiado.length < 13) {
            documentoHelp.textContent = `El DPI debe tener 13 caracteres (${numeroDocumentoLimpiado.length}/13)`;
            documentoHelp.style.color = '#dc3545';
        } else if (numeroDocumentoLimpiado.length === 13) {
            documentoHelp.textContent = 'DPI valido';
            documentoHelp.style.color = '#28a745';
        }
    }

    if (tipoDocumento === 'nit') {
        if (!/^[0-9Kk\-]{3,20}$/.test(numeroDocumentoLimpiado)) {
            documentoHelp.textContent = 'Ingrese un NIT valido';
            documentoHelp.style.color = '#dc3545';
        } else {
            documentoHelp.textContent = 'NIT valido';
            documentoHelp.style.color = '#28a745';
        }
    }
}

// Validar formato de correo electronico
function validarCorreo(correo) {
    const valor = sanitizarInput(correo).toLowerCase();
    if (!valor) return false;
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    return regex.test(valor);
}

function mostrarError(input, mensaje, errorId) {
    if (input) input.classList.add("input-invalid");
    const error = document.getElementById(errorId);
    if (error) error.textContent = mensaje;
}

function limpiarError(input, errorId) {
    if (input) input.classList.remove("input-invalid");
    const error = document.getElementById(errorId);
    if (error) error.textContent = "";
}

function validarNombre(nombre) {
    const valor = sanitizarInput(nombre);
    const regex = /^[\p{L}\s]{3,80}$/u;
    return regex.test(valor);
}

function validarDireccion(direccion) {
    const valor = sanitizarInput(direccion);
    const regex = /^[\p{L}0-9\s#\-,.]{5,150}$/u;
    return regex.test(valor);
}

function validarTelefono(telefono) {
    const valor = telefono.replace(/\D/g, "");
    const regex = /^\d{8}$/;
    return regex.test(valor);
}

function validarCaracteresPermitidos(valor, regex) {
    return regex.test(valor);
}

function procesarPedido(event) {
    event.preventDefault();
    if (cotizacionEnCurso) return false;

    const nombre = document.getElementById("nombreCompleto");
    const direccion = document.getElementById("direccion");
    const telefono = document.getElementById("telefono");
    const correo = document.getElementById("correo");
    const necesitaFactura = document.getElementById("necesitaFactura");
    const tipoDocumento = document.getElementById("tipoDocumento");
    const numeroDocumento = document.getElementById("numeroDocumento");
    const formaPago = document.getElementById("formaPago");

    let valido = true;

    limpiarError(nombre, "errorNombreCompleto");
    limpiarError(direccion, "errorDireccion");
    limpiarError(telefono, "errorTelefono");
    limpiarError(correo, "errorCorreo");
    limpiarError(tipoDocumento, "errorTipoDocumento");
    limpiarError(numeroDocumento, "errorNumeroDocumento");
    limpiarError(formaPago, "errorFormaPago");

    nombre.value = sanitizarInput(nombre.value);
    direccion.value = sanitizarInput(direccion.value);
    telefono.value = telefono.value.replace(/\D/g, "");
    correo.value = sanitizarInput(correo.value);

    if (!validarNombre(nombre.value)) {
        mostrarError(nombre, "Ingrese un nombre valido.", "errorNombreCompleto");
        valido = false;
    }

    if (!validarDireccion(direccion.value)) {
        mostrarError(direccion, "Ingrese una direccion valida.", "errorDireccion");
        valido = false;
    }

    if (!validarTelefono(telefono.value)) {
        mostrarError(telefono, "El telefono debe tener 8 digitos.", "errorTelefono");
        valido = false;
    }

    if (!validarCorreo(correo.value)) {
        mostrarError(correo, "Ingrese un correo valido.", "errorCorreo");
        valido = false;
    }

    const facturaActiva = necesitaFactura ? necesitaFactura.checked : false;

    if (facturaActiva) {
        if (tipoDocumento.value === 'cf') numeroDocumento.value = 'C/F';
        if (!tipoDocumento.value) {
            mostrarError(tipoDocumento, "Seleccione un tipo de documento.", "errorTipoDocumento");
            valido = false;
        }

        if (!numeroDocumento.value.trim()) {
            mostrarError(numeroDocumento, "Ingrese el numero de documento.", "errorNumeroDocumento");
            valido = false;
        } else {
            const numeroDoc = sanitizarInput(numeroDocumento.value.trim());

            if (tipoDocumento.value === "dpi" && !/^\d{13}$/.test(numeroDoc)) {
                mostrarError(numeroDocumento, "El DPI debe tener 13 digitos.", "errorNumeroDocumento");
                valido = false;
            }

            if (tipoDocumento.value === "nit" && !/^[0-9Kk\-]{3,20}$/.test(numeroDoc)) {
                mostrarError(numeroDocumento, "Ingrese un NIT valido.", "errorNumeroDocumento");
                valido = false;
            }
        }
    }

    if (!Object.prototype.hasOwnProperty.call(ETIQUETAS_FORMA_PAGO, formaPago.value)) {
        mostrarError(formaPago, "Seleccione un metodo de pago.", "errorFormaPago");
        valido = false;
    }

    const entregaSeleccionada = obtenerEntregaSeleccionada();
    if (!entregaSeleccionada) {
        mostrarError(formaPago, "Seleccione una forma de pago válida para recoger en tienda.", "errorFormaPago");
        valido = false;
    }

    if (!valido) {
        if (typeof Swal !== "undefined") {
            Swal.fire({
                icon: "error",
                title: "Formulario invalido",
                text: "Corrige los campos marcados antes de continuar.",
                confirmButtonText: "Aceptar"
            });
        }
        return false;
    }

    const validacion = Carrito.validarCarritoParaPedido();
    if (!validacion.valido) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error de validacion',
                text: validacion.mensaje,
                confirmButtonText: 'Aceptar'
            });
        } else {
            alert(validacion.mensaje);
        }
        return false;
    }

    const nombreCompleto = nombre.value;
    const direccionFinal = direccion.value;
    const telefonoFinal = telefono.value;
    const correoFinal = correo.value;
    const formaPagoFinal = formaPago.value;
    const totalesPedido = calcularTotalesPedido();

    // Si no necesita factura, usar "C/F" como NIT
    let nitFinal = '';
    let tipoDocumentoFinal = '';

    if (facturaActiva) {
        tipoDocumentoFinal = tipoDocumento.value === 'dpi' ? 'DPI' : 'NIT';
        nitFinal = sanitizarInput(numeroDocumento.value.trim());
    } else {
        tipoDocumentoFinal = 'NIT';
        nitFinal = 'C/F';
    }

    const formData = {
        nombreCompleto: nombreCompleto,
        direccion: direccionFinal,
        telefono: telefonoFinal,
        correo: correoFinal || '',
        necesitaFactura: facturaActiva,
        tipoDocumento: tipoDocumentoFinal,
        numeroDocumento: nitFinal,
        formaPago: formaPagoFinal,
        modalidadEntrega: entregaSeleccionada.value,
        entregaLabel: entregaSeleccionada.label,
        entregaDetalle: entregaSeleccionada.detalle,
        costoEnvio: entregaSeleccionada.costo,
        carrito: Carrito.obtenerCarrito(),
        subtotal: Carrito.obtenerSubtotal(),
        subtotalNormal: totalesPedido.subtotalNormal,
        total: totalesPedido.totalSeleccionado
    };

    const carrito = Carrito.obtenerCarrito();
    const subtotal = totalesPedido.subtotalNormal;
    const total = totalesPedido.totalSeleccionado;
    const resumenEntregaPedido = entregaSeleccionada.detalle;

    const idarticulo = [];
    const cantidad = [];
    const precio_venta = [];
    const subtotal1 = [];
    const subtotaldes1 = [];

    carrito.forEach(producto => {
        idarticulo.push(parseInt(producto.idarticulo));
        cantidad.push(parseFloat(producto.cantidad || 1));
        const precio = parseFloat(producto.precio_normal || producto.precio || 0);
        precio_venta.push(precio);
        const subtotalItem = precio * (producto.cantidad || 1);
        subtotal1.push(subtotalItem);
        subtotaldes1.push(subtotalItem);
    });

    const apiData = {
        codigo_cliente: null,
        nit: formData.numeroDocumento,
        nombre_cliente: formData.nombreCompleto,
        telefono_cliente: formData.telefono,
        direccion_cliente: formData.direccion,
        correo_cliente: formData.correo || '',
        valor_tarjeta: 0,
        tipo_documento_cliente: formData.tipoDocumento,
        fecha_hora: null,
        forma_pago: ETIQUETAS_FORMA_PAGO[formData.formaPago] || formData.formaPago,
        tipo_comprobante: "",
        tipo_pagoBacVisaNet: "",
        opcionesAdicionales: resumenEntregaPedido,
        total_venta: total,
        total_ventades: subtotal,
        idvendedor: 0,
        tipo_cliente: "Cliente",
        forma_productos: "Detallado",
        comentario_cotizacion: `Cotización web | Pago previsto: ${ETIQUETAS_FORMA_PAGO[formaPagoFinal]} | Recogida en tienda | Productos: ${formatearMoneda(subtotal)} | Total estimado: ${formatearMoneda(total)} | Pendiente de confirmación, sin cobro`,
        destino: "VENTA",
        no_auto_tarjeta: "",
        datosArticulos: {
            articulos: {
                idarticulo: idarticulo,
                cantidad: cantidad,
                precio_venta: precio_venta,
                subtotal1: subtotal1,
                subtotaldes1: subtotaldes1
            }
        }
    };

    const btnEnviar = document.querySelector('.mp-btn-enviar');
    if (!QUOTE_CSRF_TOKEN) {
        mostrarErrorPedido('Recarga la página antes de enviar la cotización.', btnEnviar);
        return false;
    }
    establecerEnvioCotizacion(true);
    if (btnEnviar) {
        btnEnviar.disabled = true;
        btnEnviar.textContent = 'Enviando cotización...';
    }

    fetch(ORDER_CREATE_QUOTE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ csrf_token: QUOTE_CSRF_TOKEN, formData, apiData })
    })
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (error) {
            throw new Error('La respuesta del servidor no es válida.');
        }
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'No fue posible registrar la cotización.');
        }
        return data;
    })
    .then(data => {
        const numero = data.idcotizacion || data.numero_cotizacion || data.reference_number || 'Sin número';
        finalizarPedidoExitoso(numero, formData, data.reference_number || '');
    })
    .catch(error => {
        mostrarErrorPedido(error.message || 'No fue posible registrar la cotización. Intenta nuevamente.', btnEnviar);
    })
    .finally(() => {
        establecerEnvioCotizacion(false);
        restaurarBotonPedido(btnEnviar);
    });
    return false;
}
