// ============================================================
// CARGAR Y MOSTRAR PRODUCTOS DEL CARRITO
// ============================================================

const METODO_TARJETA_NEONET = 'tarjeta';
const CYBERSOURCE_CHECKOUT_URL = (window.CYBERSOURCE_CHECKOUT_URL || 'api/cybersource/create_checkout.php').trim();
const ORDER_CREATE_QUOTE_URL = (window.ORDER_CREATE_QUOTE_URL || 'api/orders/create_quote.php').trim();
const CYBERSOURCE_CSRF_TOKEN = (window.CYBERSOURCE_CSRF_TOKEN || '').trim();
const NEONET_PAYMENT_EVENT_KEY = 'beautymarket_neonet_payment_approved';
const BEAUTY_MARKET_LOGO_URL = 'assets/img/logotipo IC/version horizontal/Logo-Industrial-Collectors-horizontal.png';

const ETIQUETAS_FORMA_PAGO = {
    contra_entrega: 'Pago Contra Entrega',
    tarjeta: 'Tarjeta de Credito/Debito',
    transferencia: 'Transferencia Bancaria'
};

const OPCION_RETIRO_TIENDA = {
    tipo: 'option',
    value: 'recoge_tienda',
    label: 'Recoge en tienda - GRATIS',
    costo: 0,
    detalle: 'Retiro en tienda sin costo de envío.',
    nota: 'Puedes retirar tu pedido en tienda sin costo de envío.',
    metodos: ['contra_entrega', 'tarjeta', 'transferencia']
};

const OPCIONES_ENTREGA_POR_METODO = {
    contra_entrega: [
        OPCION_RETIRO_TIENDA,
        { tipo: 'group', label: 'Pago contra entrega' },
        {
            tipo: 'option',
            value: 'contra_capital',
            label: 'Zonas capitalinas - Q30.00',
            costo: 30,
            detalle: 'Pago contra entrega en zonas capitalinas.',
            nota: 'Aplican restricciones en zonas de riesgo.',
            metodos: ['contra_entrega']
        },
        {
            tipo: 'option',
            value: 'contra_municipios',
            label: 'Villa Nueva, Villa Canales, Ciudad San Cristóbal o Mixco - Q35.00',
            costo: 35,
            detalle: 'Pago contra entrega en Villa Nueva, Villa Canales, Ciudad San Cristóbal o Mixco.',
            nota: 'Tarifa especial para los municipios indicados.',
            metodos: ['contra_entrega']
        },
        {
            tipo: 'option',
            value: 'contra_interior',
            label: 'Interior de la República - Q45.00',
            costo: 45,
            detalle: 'Pago contra entrega al interior de la República.',
            nota: 'Servicio disponible al interior de la República con pago contra entrega.',
            metodos: ['contra_entrega']
        }
    ],
    transferencia: [
        OPCION_RETIRO_TIENDA,
        { tipo: 'group', label: 'Pago previo por depósito o transferencia' },
        {
            tipo: 'option',
            value: 'envio_previo_todo_pais',
            label: 'Envío a todo el país - Q30.00',
            costo: 30,
            detalle: 'Envío a todo el país con pago previo por depósito o transferencia.',
            nota: 'Costo fijo para todo el país al pagar previamente por depósito o transferencia.',
            metodos: ['transferencia', 'tarjeta']
        }
    ],
    tarjeta: [
        OPCION_RETIRO_TIENDA,
        { tipo: 'group', label: 'Pago previo' },
        {
            tipo: 'option',
            value: 'envio_previo_todo_pais',
            label: 'Envío a todo el país - Q30.00',
            costo: 30,
            detalle: 'Envío a todo el país con pago previo.',
            nota: 'Costo fijo para todo el país con pago previo.',
            metodos: ['transferencia', 'tarjeta']
        }
    ]
};

function obtenerOpcionesEntrega(metodo) {
    return OPCIONES_ENTREGA_POR_METODO[metodo] || [];
}

function obtenerDetalleEntrega(value, metodo) {
    const opciones = obtenerOpcionesEntrega(metodo);
    return opciones.find((opcion) => opcion.tipo === 'option' && opcion.value === value) || null;
}

function actualizarNiceSelect(select) {
    if (window.jQuery && typeof window.jQuery.fn.niceSelect === 'function' && select) {
        window.jQuery(select).niceSelect('update');
    }
}

function actualizarOpcionesEntrega() {
    const formaPago = document.getElementById("formaPago");
    const modalidadEntrega = document.getElementById("modalidadEntrega");

    if (!modalidadEntrega) {
        return;
    }

    const metodo = formaPago ? formaPago.value : '';
    const valorPrevio = modalidadEntrega.value;
    modalidadEntrega.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = metodo ? 'Seleccione modalidad y lugar de entrega' : 'Seleccione primero el método de pago';
    modalidadEntrega.appendChild(placeholder);

    const opciones = obtenerOpcionesEntrega(metodo);
    opciones.forEach((opcion) => {
        const optionElement = document.createElement('option');

        if (opcion.tipo === 'group') {
            optionElement.value = '';
            optionElement.disabled = true;
            optionElement.textContent = opcion.label;
        } else {
            optionElement.value = opcion.value;
            optionElement.textContent = opcion.label;
            optionElement.dataset.costo = String(opcion.costo);
        }

        modalidadEntrega.appendChild(optionElement);
    });

    modalidadEntrega.disabled = !metodo;
    if (metodo && obtenerDetalleEntrega(valorPrevio, metodo)) {
        modalidadEntrega.value = valorPrevio;
    } else {
        modalidadEntrega.value = '';
    }

    actualizarNotaEntrega();
    actualizarNiceSelect(modalidadEntrega);
}

function obtenerEntregaSeleccionada() {
    const formaPago = document.getElementById("formaPago");
    const modalidadEntrega = document.getElementById("modalidadEntrega");

    if (!formaPago || !modalidadEntrega || !formaPago.value || !modalidadEntrega.value) {
        return null;
    }

    return obtenerDetalleEntrega(modalidadEntrega.value, formaPago.value);
}

function actualizarNotaEntrega() {
    const nota = document.getElementById("modalidadEntregaNota");
    const entrega = obtenerEntregaSeleccionada();

    if (!nota) {
        return;
    }

    nota.classList.toggle('is-warning', Boolean(entrega && entrega.value === 'contra_capital'));
    nota.textContent = entrega
        ? entrega.nota
        : 'El costo de envío se calculará según la modalidad seleccionada.';
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
                        <p>Agrega productos desde la tienda para comenzar a comprar.</p>
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
    inicializarRetornoPagoNeonet();

    const nombre = document.getElementById("nombreCompleto");
    const direccion = document.getElementById("direccion");
    const telefono = document.getElementById("telefono");
    const correo = document.getElementById("correo");
    const numeroDocumento = document.getElementById("numeroDocumento");
    const tipoDocumento = document.getElementById("tipoDocumento");
    const formaPago = document.getElementById("formaPago");
    const modalidadEntrega = document.getElementById("modalidadEntrega");

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
            numeroDocumento.value = "";
            limpiarError(tipoDocumento, "errorTipoDocumento");
            limpiarError(numeroDocumento, "errorNumeroDocumento");
        });
    }

    if (formaPago) {
        const refrescarResumenFormaPago = function () {
            limpiarError(formaPago, "errorFormaPago");
            actualizarOpcionesEntrega();
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

    if (modalidadEntrega) {
        const refrescarResumenEntrega = function () {
            limpiarError(modalidadEntrega, "errorModalidadEntrega");
            actualizarNotaEntrega();
            window.setTimeout(actualizarResumenPedido, 0);
        };

        modalidadEntrega.addEventListener("change", refrescarResumenEntrega);
        modalidadEntrega.addEventListener("input", refrescarResumenEntrega);
    }

    actualizarOpcionesEntrega();

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
    const formaPago = document.getElementById("formaPago");
    const totalElement = document.getElementById("pedidoTotalModal");
    const detalleElement = document.getElementById("resumenPagoDetalle");
    const resumenBox = document.getElementById("resumenPagoPedido");

    if (!totalElement || !detalleElement) {
        return;
    }

    const metodo = formaPago ? formaPago.value : '';
    const esTarjeta = metodo === METODO_TARJETA_NEONET;
    const entrega = obtenerEntregaSeleccionada();
    const totales = calcularTotalesPedido();

    totalElement.textContent = formatearMoneda(totales.totalSeleccionado);
    totalElement.hidden = false;

    if (!metodo) {
        detalleElement.textContent = 'Seleccione método de pago y modalidad de entrega para confirmar el total.';
    } else if (!entrega) {
        detalleElement.textContent = 'Seleccione modalidad y lugar de entrega para calcular el envío.';
    } else {
        detalleElement.textContent = `${ETIQUETAS_FORMA_PAGO[metodo] || metodo} | ${entrega.label}. Productos ${formatearMoneda(totales.subtotalNormal)} + envío ${formatearMoneda(totales.costoEnvio)}.`;
    }

    if (resumenBox) {
        resumenBox.classList.toggle('mp-total-unselected', !metodo || !entrega);
        resumenBox.classList.toggle('mp-total-card', esTarjeta);
    }
}

function finalizarPedidoExitoso(numeroCotizacion, formData) {
    const mensajeExito = `Cotizacion #${numeroCotizacion}`;

    Carrito.limpiarCarrito();
    cerrarModalPedido();

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Pedido enviado con exito',
            text: mensajeExito,
            confirmButtonText: 'Aceptar',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            window.location.href = 'index.php';
        });
    } else {
        alert(`Pedido enviado con exito. ${mensajeExito}`);
        window.location.href = 'index.php';
    }
}

function restaurarBotonPedido(btnEnviar) {
    if (btnEnviar) {
        btnEnviar.disabled = false;
        btnEnviar.textContent = 'Enviar Pedido';
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

function abrirVentanaPagoPendiente() {
    const popupWidth = 980;
    const popupHeight = 900;
    const parentLeft = Number.isFinite(window.screenX) ? window.screenX : (window.screenLeft || 0);
    const parentTop = Number.isFinite(window.screenY) ? window.screenY : (window.screenTop || 0);
    const parentWidth = window.outerWidth || window.innerWidth || screen.availWidth || popupWidth;
    const parentHeight = window.outerHeight || window.innerHeight || screen.availHeight || popupHeight;
    const popupLeft = Math.max(0, Math.round(parentLeft + ((parentWidth - popupWidth) / 2)));
    const popupTop = Math.max(0, Math.round(parentTop + ((parentHeight - popupHeight) / 2)));
    const popupFeatures = [
        `width=${popupWidth}`,
        `height=${popupHeight}`,
        `left=${popupLeft}`,
        `top=${popupTop}`,
        'resizable=yes',
        'scrollbars=yes'
    ].join(',');
    const ventana = window.open('', `beautyMarketNeoNetCheckout${Date.now()}`, popupFeatures);
    if (!ventana) {
        return null;
    }

    try {
        ventana.resizeTo(popupWidth, popupHeight);
        ventana.moveTo(popupLeft, popupTop);
        ventana.focus();
    } catch (error) {
        // El navegador puede bloquear resize/move en algunas configuraciones.
    }

    ventana.document.write(`
        <!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Industrial Collectors, S.A.</title>
            <style>
                *{box-sizing:border-box}
                body{margin:0;display:grid;min-height:100vh;place-items:center;background:#fff}
                .logo{display:block;width:min(300px,70vw);height:auto}
            </style>
        </head>
        <body>
            <img class="logo" src="${BEAUTY_MARKET_LOGO_URL}" alt="Industrial Collectors, S.A.">
        </body>
        </html>
    `);
    ventana.document.close();

    return ventana;
}

function mostrarErrorVentanaPago(ventana, mensaje) {
    if (!ventana || ventana.closed) {
        return;
    }

    ventana.document.body.innerHTML = `
        <main style="width:min(90vw,520px);background:#fff;border:1px solid #f1c5c5;border-radius:8px;padding:28px;text-align:center;box-shadow:0 14px 35px rgba(0,0,0,.08)">
            <h1 style="color:#a54545;margin:0 0 10px;font-size:26px">Pago no disponible</h1>
            <p style="line-height:1.5;margin:0;color:#555">${escaparHtml(mensaje)}</p>
        </main>
    `;
}

function abrirCheckoutSeguro(urlPago, referencia, ventanaPago = null) {
    let abrioPestana = false;

    if (ventanaPago && !ventanaPago.closed) {
        ventanaPago.location.href = urlPago;
        abrioPestana = true;
    } else {
        const ventana = window.open(urlPago, '_blank');
        abrioPestana = !!ventana;
    }

    if (!abrioPestana && typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Abrir pago seguro',
            html: `Referencia ${referencia}. <br><br><a href="${urlPago}" target="_blank" rel="noopener noreferrer">Abrir portal seguro de NeoNet</a>`,
            confirmButtonText: 'Entendido'
        });
    } else if (!abrioPestana) {
        alert(`Referencia ${referencia}. Complete el pago en NeoNet: ${urlPago}`);
    }

    return abrioPestana;
}

function finalizarPagoNeonetEnTienda() {
    if (typeof Carrito !== 'undefined' && typeof Carrito.limpiarCarrito === 'function') {
        Carrito.limpiarCarrito();
    }

    window.location.href = 'index.php';
}

function inicializarRetornoPagoNeonet() {
    window.addEventListener('storage', function (event) {
        if (event.key !== NEONET_PAYMENT_EVENT_KEY || !event.newValue) {
            return;
        }

        try {
            const payload = JSON.parse(event.newValue);
            if (payload && payload.status === 'approved') {
                finalizarPagoNeonetEnTienda();
            }
        } catch (error) {
            console.warn('No fue posible interpretar la confirmacion de NeoNet.', error);
        }
    });

    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin || !event.data || event.data.type !== 'beautymarket:neonet-approved') {
            return;
        }

        finalizarPagoNeonetEnTienda();
    });
}

function iniciarPagoCybersource(formData, apiData, btnEnviar, ventanaPago = null) {
    if (!CYBERSOURCE_CSRF_TOKEN) {
        mostrarErrorVentanaPago(ventanaPago, 'Token de seguridad no disponible. Recargue la pagina e intente nuevamente.');
        mostrarErrorPedido('Token de seguridad no disponible. Recargue la pagina e intente nuevamente.', btnEnviar);
        return;
    }

    fetch(CYBERSOURCE_CHECKOUT_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            csrf_token: CYBERSOURCE_CSRF_TOKEN,
            formData: formData,
            apiData: apiData
        })
    })
    .then(response => response.text().then(text => {
        let data;
        try {
            data = JSON.parse(text);
        } catch (error) {
            throw new Error('La respuesta de la pasarela no es valida.');
        }

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'No fue posible iniciar el pago en NeoNet.');
        }

        return data;
    }))
    .then(data => {
        cerrarModalPedido();
        abrirCheckoutSeguro(data.checkout_url, data.reference_number, ventanaPago);
        restaurarBotonPedido(btnEnviar);
    })
    .catch(error => {
        console.error('Error NeoNet/CyberSource:', error);
        mostrarErrorVentanaPago(ventanaPago, error.message || 'No fue posible iniciar el pago en NeoNet.');
        mostrarErrorPedido(error.message || 'No fue posible iniciar el pago en NeoNet.', btnEnviar);
    });
}

// ============================================================
// ELIMINAR PRODUCTO DEL CARRITO
// ============================================================
function eliminarDelCarrito(idarticulo) {
    if (confirm('Esta seguro de que desea eliminar este producto del carrito?')) {
        Carrito.eliminarProducto(idarticulo);
        cargarCarrito();
    }
}

// ============================================================
// CAMBIAR CANTIDAD DE PRODUCTO
// ============================================================
function cambiarCantidad(idarticulo, cambio) {
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
    document.getElementById('nit-group').style.display = 'none';
    document.getElementById('numeroDocumento-group').style.display = 'none';
    
    document.getElementById('formaPago').value = '';
    const modalidadEntrega = document.getElementById('modalidadEntrega');
    if (modalidadEntrega) {
        modalidadEntrega.value = '';
    }
    actualizarOpcionesEntrega();
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
function cerrarModalPedido() {
    document.getElementById('modalPedido').classList.remove('show');
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

    const nombre = document.getElementById("nombreCompleto");
    const direccion = document.getElementById("direccion");
    const telefono = document.getElementById("telefono");
    const correo = document.getElementById("correo");
    const necesitaFactura = document.getElementById("necesitaFactura");
    const tipoDocumento = document.getElementById("tipoDocumento");
    const numeroDocumento = document.getElementById("numeroDocumento");
    const formaPago = document.getElementById("formaPago");
    const modalidadEntrega = document.getElementById("modalidadEntrega");

    let valido = true;

    limpiarError(nombre, "errorNombreCompleto");
    limpiarError(direccion, "errorDireccion");
    limpiarError(telefono, "errorTelefono");
    limpiarError(correo, "errorCorreo");
    limpiarError(tipoDocumento, "errorTipoDocumento");
    limpiarError(numeroDocumento, "errorNumeroDocumento");
    limpiarError(formaPago, "errorFormaPago");
    limpiarError(modalidadEntrega, "errorModalidadEntrega");

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

    if (!formaPago.value) {
        mostrarError(formaPago, "Seleccione un metodo de pago.", "errorFormaPago");
        valido = false;
    }

    const entregaSeleccionada = obtenerEntregaSeleccionada();
    if (!entregaSeleccionada) {
        mostrarError(modalidadEntrega, "Seleccione modalidad y lugar de entrega.", "errorModalidadEntrega");
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
    let formaPagoFinal = formaPago.value;
    const totalesPedido = calcularTotalesPedido(formaPagoFinal);

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
    const costoEnvio = totalesPedido.costoEnvio;
    const total = totalesPedido.totalSeleccionado;
    const resumenEntregaPedido = `${entregaSeleccionada.detalle} Envío: ${formatearMoneda(costoEnvio)}.`;

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
        valor_tarjeta: formaPagoFinal === METODO_TARJETA_NEONET ? total : 0,
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
        comentario_cotizacion: `Tienda en linea | Metodo de pago: ${ETIQUETAS_FORMA_PAGO[formaPagoFinal] || formaPagoFinal} | Entrega: ${entregaSeleccionada.label} | Envío: ${formatearMoneda(costoEnvio)} | Productos: ${formatearMoneda(subtotal)} | Total: ${formatearMoneda(total)}${formaPagoFinal === METODO_TARJETA_NEONET ? ' | Pago pendiente en NeoNet' : ' | Pendiente de confirmacion/cobro'}`,
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
    if (btnEnviar) {
        btnEnviar.disabled = true;
        btnEnviar.textContent = 'Enviando...';
    }

    if (formaPagoFinal === METODO_TARJETA_NEONET) {
        const ventanaPago = abrirVentanaPagoPendiente();
        if (btnEnviar) {
            btnEnviar.textContent = 'Preparando pago...';
        }
        iniciarPagoCybersource(formData, apiData, btnEnviar, ventanaPago);
        return false;
    }

    fetch(ORDER_CREATE_QUOTE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            csrf_token: CYBERSOURCE_CSRF_TOKEN,
            formData: formData,
            apiData: apiData
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Error parsing JSON:', text);
                throw new Error('La respuesta del servidor no es valida');
            }
        });
    })
    .then(data => {
        if (data.success) {
            const numeroCotizacion = data.idcotizacion || data.numero_cotizacion || data.reference_number || 'N/A';
            finalizarPedidoExitoso(numeroCotizacion, formData);
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error al procesar el pedido. Por favor intenta nuevamente.',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    if (btnEnviar) {
                        btnEnviar.disabled = false;
                        btnEnviar.textContent = 'Enviar Pedido';
                    }
                });
            } else {
                alert(data.message || 'Error al procesar el pedido. Por favor intenta nuevamente.');
                if (btnEnviar) {
                    btnEnviar.disabled = false;
                    btnEnviar.textContent = 'Enviar Pedido';
                }
            }
        }
    })
    .catch(error => {
        console.error('Error completo:', error);

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexion',
                text: error.message || 'No se pudo conectar con el servidor. Por favor intenta nuevamente.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                if (btnEnviar) {
                    btnEnviar.disabled = false;
                    btnEnviar.textContent = 'Enviar Pedido';
                }
            });
        } else {
            alert(error.message || 'No se pudo conectar con el servidor. Por favor intenta nuevamente.');
            if (btnEnviar) {
                btnEnviar.disabled = false;
                btnEnviar.textContent = 'Enviar Pedido';
            }
        }
    });
}
