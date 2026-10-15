// ============================================================
// 🛒 SISTEMA DE CARRITO DE COMPRAS CON SESSIONSTORAGE
// ============================================================
// 
// 📌 IMPORTANTE: Usa sessionStorage en lugar de localStorage
//    - El carrito es específico por navegador y sesión
//    - Se borra automáticamente al cerrar la pestaña/ventana
//    - Cada navegador tiene su propio carrito independiente
// 
// 🔒 MEDIDAS DE SEGURIDAD IMPLEMENTADAS:
// 
// 1. HASH DE INTEGRIDAD: Detecta si el carrito ha sido modificado manualmente
//    en el sessionStorage. Si se detecta una modificación, el carrito se limpia
//    automáticamente.
// 
// 2. VALIDACIÓN DE PRECIOS: Valida que los precios estén dentro de rangos
//    razonables (0.01 - 999999.99) antes de guardar o enviar.
// 
// 3. PROTECCIÓN CONTRA MANIPULACIÓN: Los precios no pueden ser modificados
//    después de agregar un producto al carrito.
// 
// 4. VALIDACIÓN ANTES DE ENVIAR: Verifica la integridad del carrito antes
//    de procesar el pedido.
// 
// ⚠️ IMPORTANTE: Estas medidas son solo una capa de protección del lado del cliente.
//    El SERVIDOR (API) DEBE validar siempre:
//    - Que los precios coinciden con la base de datos
//    - Que los productos existen y están disponibles
//    - Que las cantidades son válidas
//    - Recalcular el total usando los precios del servidor
// 
// ============================================================

const Carrito = {
    // Usar sessionStorage en lugar de localStorage para que sea específico por sesión
    // sessionStorage se borra automáticamente al cerrar la pestaña/ventana
    storage: sessionStorage, // Cambiar a sessionStorage
    
    // Clave para sessionStorage
    STORAGE_KEY: 'compusisgt_carrito',
    // Clave para timestamp de expiración
    TIMESTAMP_KEY: 'compusisgt_carrito_timestamp',
    // Clave para hash de integridad
    HASH_KEY: 'compusisgt_carrito_hash',
    // Tiempo de expiración en horas (1 hora)
    EXPIRACION_HORAS: 1,
    // Bandera para evitar recursión infinita
    _limpiando: false,
    // Precio mínimo y máximo permitidos (protección básica)
    PRECIO_MINIMO: 0.01,
    PRECIO_MAXIMO: 999999.99,
    CANTIDAD_MINIMA: 1,
    CANTIDAD_MAXIMA: 99,

    // ============================================================
    // VALIDACIONES BASE DEL CARRITO
    // ============================================================
    validarCantidad(cantidad) {
        return Number.isInteger(cantidad) && cantidad >= this.CANTIDAD_MINIMA && cantidad <= this.CANTIDAD_MAXIMA;
    },

    normalizarCantidad(cantidad, fallback = 1) {
        const fallbackSeguro = parseInt(fallback, 10);
        let cantidadNormalizada = parseInt(String(cantidad ?? '').replace(/\D/g, ''), 10);

        if (!Number.isInteger(cantidadNormalizada) || cantidadNormalizada < this.CANTIDAD_MINIMA) {
            cantidadNormalizada = Number.isInteger(fallbackSeguro) ? fallbackSeguro : this.CANTIDAD_MINIMA;
        }

        return Math.min(Math.max(cantidadNormalizada, this.CANTIDAD_MINIMA), this.CANTIDAD_MAXIMA);
    },

    validarIdProducto(id) {
        return /^[a-zA-Z0-9_-]{1,50}$/.test(String(id || ''));
    },

    // ============================================================
    // GENERAR HASH DE INTEGRIDAD DEL CARRITO
    // ============================================================
    generarHash(carrito) {
        // Crear una cadena única basada en los datos del carrito
        const datos = JSON.stringify(carrito.map(item => ({
            id: item.idarticulo,
            precio: parseFloat(item.precio || 0).toFixed(2),
            cantidad: parseInt(item.cantidad || 1)
        })).sort((a, b) => a.id - b.id));
        
        // Hash simple pero efectivo (no es criptográficamente seguro, pero detecta cambios)
        let hash = 0;
        for (let i = 0; i < datos.length; i++) {
            const char = datos.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convertir a entero de 32 bits
        }
        return Math.abs(hash).toString(36);
    },

    // ============================================================
    // VERIFICAR INTEGRIDAD DEL CARRITO
    // ============================================================
    verificarIntegridad(carritoData = null) {
        try {
            if (carritoData !== null) {
                if (!Array.isArray(carritoData)) {
                    return false;
                }

                for (const item of carritoData) {
                    if (!item) return false;
                    if (!this.validarIdProducto(item.idarticulo || item.id)) return false;
                    if (!this.validarCantidad(Number(item.cantidad))) return false;
                    if (!this.validarPrecio(Number(item.precio))) return false;
                }

                return true;
            }

            const carrito = this.storage.getItem(this.STORAGE_KEY);
            const hashGuardado = this.storage.getItem(this.HASH_KEY);
            
            if (!carrito) {
                return true; // Carrito vacío es válido
            }
            
            const carritoParsed = JSON.parse(carrito);
            if (!this.verificarIntegridad(carritoParsed)) {
                console.warn('⚠️ ADVERTENCIA: El carrito contiene datos inválidos. Se limpiará por seguridad.');
                this.limpiarCarrito();
                return false;
            }
            const hashCalculado = this.generarHash(carritoParsed);
            
            if (hashGuardado !== hashCalculado) {
                console.warn('⚠️ ADVERTENCIA: El carrito ha sido modificado. Se limpiará por seguridad.');
                this.limpiarCarrito();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Carrito modificado',
                        text: 'Se detectó una modificación no autorizada del carrito. Por seguridad, se ha limpiado.',
                        confirmButtonText: 'Aceptar'
                    });
                }
                return false;
            }
            
            return true;
        } catch (error) {
            console.error('Error al verificar integridad:', error);
            return false;
        }
    },

    // ============================================================
    // OBTENER CARRITO DESDE LOCALSTORAGE (con verificación de expiración e integridad)
    // ============================================================
    obtenerCarrito(skipExpirationCheck = false, skipIntegrityCheck = false) {
        try {
            // Si estamos limpiando, retornar array vacío sin verificar expiración
            if (this._limpiando) {
                return [];
            }

            let carrito = this.storage.getItem(this.STORAGE_KEY);
            if (!carrito) {
                return [];
            }

            // Verificar integridad del carrito (a menos que se omita)
            if (!skipIntegrityCheck && !this.verificarIntegridad()) {
                return [];
            }

            // Verificar si el carrito ha expirado (solo si no se omite la verificación)
            carrito = this.storage.getItem(this.STORAGE_KEY);
            if (!carrito) {
                return [];
            }

            if (!skipExpirationCheck && this.carritoExpirado()) {
                this.limpiarCarrito();
                return [];
            }

            const productos = carrito ? JSON.parse(carrito) : [];

            return productos.map((producto) => {
                const productoNormalizado = { ...producto };
                delete productoNormalizado.precio_efectivo;
                delete productoNormalizado.precioEfectivo;
                return productoNormalizado;
            });
        } catch (error) {
            console.error('Error al obtener carrito:', error);
            return [];
        }
    },

    // ============================================================
    // VERIFICAR SI EL CARRITO HA EXPIRADO
    // ============================================================
    carritoExpirado() {
        try {
            const timestamp = this.storage.getItem(this.TIMESTAMP_KEY);
            
            // Si no hay timestamp, el carrito es antiguo (sin sistema de expiración)
            // Lo consideramos expirado y lo limpiamos
            if (!timestamp) {
                return true;
            }

            const fechaCreacion = new Date(parseInt(timestamp));
            const fechaActual = new Date();
            // Calcular horas transcurridas en lugar de días
            const horasTranscurridas = (fechaActual - fechaCreacion) / (1000 * 60 * 60);

            return horasTranscurridas >= this.EXPIRACION_HORAS;
        } catch (error) {
            console.error('Error al verificar expiración del carrito:', error);
            return true; // En caso de error, considerar expirado
        }
    },

    // ============================================================
    // VALIDAR PRECIO DEL PRODUCTO
    // ============================================================
    validarPrecio(precio) {
        const precioNum = parseFloat(precio);
        if (isNaN(precioNum) || precioNum < this.PRECIO_MINIMO || precioNum > this.PRECIO_MAXIMO) {
            console.error('⚠️ Precio inválido detectado:', precio);
            return false;
        }
        return true;
    },

    normalizarPrecio(precio, fallback = 0) {
        const precioNum = parseFloat(precio);
        if (Number.isFinite(precioNum) && precioNum > 0) {
            return precioNum;
        }

        return parseFloat(fallback) || 0;
    },

    obtenerPrecioProducto(producto) {
        if (typeof window.BM_getProductEffectivePrice === 'function') {
            const precioFinal = window.BM_getProductEffectivePrice(producto);

            if (Number.isFinite(precioFinal) && precioFinal > 0) {
                return precioFinal;
            }
        }

        const precioOferta = parseFloat(producto?.precio_09 || producto?.precio09 || producto?.precio9 || producto?.precio_oferta || 0);

        if (Number.isFinite(precioOferta) && precioOferta > 0) {
            return precioOferta;
        }

        return producto?.precio || producto?.precio_venta || 0;
    },

    // ============================================================
    // GUARDAR CARRITO EN LOCALSTORAGE (con timestamp y hash de integridad)
    // ============================================================
    guardarCarrito(carrito) {
        try {
            if (!this.verificarIntegridad(carrito)) {
                console.error('⚠️ Carrito inválido, no se guardó.');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: 'Se detectaron datos inválidos en el carrito. No se guardó la información.',
                        confirmButtonText: 'Aceptar'
                    });
                }
                return false;
            }
            
            // Generar hash de integridad
            const hash = this.generarHash(carrito);
            
            this.storage.setItem(this.STORAGE_KEY, JSON.stringify(carrito));
            // Guardar timestamp actual para control de expiración
            this.storage.setItem(this.TIMESTAMP_KEY, Date.now().toString());
            // Guardar hash de integridad
            this.storage.setItem(this.HASH_KEY, hash);
            
            this.actualizarContador();
            return true;
        } catch (error) {
            console.error('Error al guardar carrito:', error);
            return false;
        }
    },

    // ============================================================
    // AGREGAR PRODUCTO AL CARRITO (con validación de precio)
    // ============================================================
    agregarProducto(producto, cantidad = 1) {
        // Validar cantidad
        cantidad = this.normalizarCantidad(cantidad);
        if (!this.validarCantidad(cantidad)) {
            console.error('⚠️ No se puede agregar producto con cantidad inválida');
            return false;
        }
        
        // Validar y obtener precio
        const precio = this.normalizarPrecio(this.obtenerPrecioProducto(producto), producto.precio || producto.precio_venta);
        if (!this.validarPrecio(precio)) {
            console.error('⚠️ No se puede agregar producto con precio inválido');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El precio del producto no es válido. Por favor, recarga la página.',
                    confirmButtonText: 'Aceptar'
                });
            }
            return false;
        }

        const carrito = this.obtenerCarrito();
        const idarticulo = producto.idarticulo || producto.id;

        if (!this.validarIdProducto(idarticulo)) {
            console.error('⚠️ No se puede agregar producto con id inválido');
            return false;
        }

        // Buscar si el producto ya existe en el carrito
        const productoExistente = carrito.find(item => item.idarticulo == idarticulo);

        if (productoExistente) {
            // Si existe, aumentar la cantidad (pero mantener el precio original)
            const cantidadActual = this.normalizarCantidad(productoExistente.cantidad);
            const nuevaCantidad = cantidadActual + cantidad;
            if (!this.validarCantidad(nuevaCantidad)) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cantidad máxima',
                        text: `Puedes agregar hasta ${this.CANTIDAD_MAXIMA} unidades por producto.`,
                        confirmButtonText: 'Aceptar'
                    });
                }
                console.error('⚠️ No se puede exceder la cantidad permitida para el producto');
                return false;
            }
            productoExistente.cantidad = nuevaCantidad;
            productoExistente.precio_normal = this.normalizarPrecio(productoExistente.precio_normal, productoExistente.precio || precio);
            // IMPORTANTE: No permitir cambiar el precio de productos existentes
            // El precio se mantiene como estaba cuando se agregó por primera vez
        } else {
            // Si no existe, agregarlo
            const nuevoProducto = {
                idarticulo: idarticulo,
                nombre: producto.nombre || '',
                precio: precio, // Precio validado
                precio_normal: precio,
                imagen: typeof window.BM_resolveProductImage === 'function'
                    ? window.BM_resolveProductImage(producto.imagen)
                    : (producto.imagen || (window.BM_PRODUCT_IMAGE_FALLBACK || 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png')),
                cantidad: cantidad,
                descripcion: producto.descripcion || ''
            };
            carrito.push(nuevoProducto);
        }

        if (!this.guardarCarrito(carrito)) {
            return false;
        }
        
        // Mostrar notificación
        this.mostrarNotificacion('Producto agregado al carrito');
        
        return true;
    },

    // ============================================================
    // ELIMINAR PRODUCTO DEL CARRITO
    // ============================================================
    eliminarProducto(idarticulo) {
        const carrito = this.obtenerCarrito();
        const nuevoCarrito = carrito.filter(item => item.idarticulo != idarticulo);
        this.guardarCarrito(nuevoCarrito);
        this.mostrarNotificacion('Producto eliminado del carrito');
        return true;
    },

    // ============================================================
    // OBTENER CANTIDAD ACTUAL DE UN PRODUCTO
    // ============================================================
    obtenerCantidadProducto(idarticulo) {
        if (!this.validarIdProducto(idarticulo)) {
            return 0;
        }

        const carrito = this.obtenerCarrito();
        const producto = carrito.find(item => item.idarticulo == idarticulo);

        return producto ? this.normalizarCantidad(producto.cantidad) : 0;
    },

    // ============================================================
    // SUMAR O RESTAR UN PRODUCTO EN PASOS DE 1
    // ============================================================
    sumarCantidad(idarticulo, cambio = 1) {
        if (!this.validarIdProducto(idarticulo)) {
            return false;
        }

        const carrito = this.obtenerCarrito();
        const producto = carrito.find(item => item.idarticulo == idarticulo);

        if (!producto) {
            return false;
        }

        const paso = parseInt(cambio, 10) >= 0 ? 1 : -1;
        const cantidadActual = this.normalizarCantidad(producto.cantidad);
        const nuevaCantidad = cantidadActual + paso;

        if (nuevaCantidad < this.CANTIDAD_MINIMA) {
            return false;
        }

        return this.actualizarCantidad(idarticulo, nuevaCantidad);
    },

    // ============================================================
    // ACTUALIZAR CANTIDAD DE UN PRODUCTO (sin permitir cambiar precio)
    // ============================================================
    actualizarCantidad(idarticulo, nuevaCantidad) {
        if (!this.validarIdProducto(idarticulo)) {
            return false;
        }

        const carrito = this.obtenerCarrito();
        const producto = carrito.find(item => item.idarticulo == idarticulo);

        if (producto) {
            const cantidad = parseInt(nuevaCantidad, 10);
            if (!this.validarCantidad(cantidad)) {
                console.error('⚠️ Cantidad inválida detectada.');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cantidad inválida',
                        text: 'La cantidad debe ser un número entre 1 y 99.',
                        confirmButtonText: 'Aceptar'
                    });
                }
                return false;
            }

            // Validar que el precio no haya sido modificado
            const precioOriginal = parseFloat(producto.precio || 0);
            if (!this.validarPrecio(precioOriginal)) {
                console.error('⚠️ Precio inválido detectado al actualizar cantidad');
                this.eliminarProducto(idarticulo);
                return false;
            }
            
            producto.cantidad = cantidad;
            // Asegurar que el precio no se modifique
            producto.precio = precioOriginal;
            
            if (!this.guardarCarrito(carrito)) {
                return false;
            }
            return true;
        }
        return false;
    },

    // ============================================================
    // OBTENER CANTIDAD TOTAL DE PRODUCTOS
    // ============================================================
    obtenerCantidadTotal() {
        // Si estamos limpiando, retornar 0 sin verificar expiración
        if (this._limpiando) {
            return 0;
        }
        const carrito = this.obtenerCarrito(true); // Omitir verificación de expiración para evitar recursión
        return carrito.reduce((total, item) => total + (item.cantidad || 1), 0);
    },

    // ============================================================
    // OBTENER SUBTOTAL DEL CARRITO (con validación)
    // ============================================================
    obtenerSubtotal() {
        // Si estamos limpiando, retornar 0 sin verificar expiración
        if (this._limpiando) {
            return 0;
        }
        const carrito = this.obtenerCarrito(true, true); // Omitir verificaciones para evitar recursión
        return carrito.reduce((total, item) => {
            const precio = parseFloat(item.precio || 0);
            const cantidad = parseInt(item.cantidad || 1);
            // Validar precio antes de sumar
            if (this.validarPrecio(precio)) {
                return total + (precio * cantidad);
            }
            return total;
        }, 0);
    },

    // ============================================================
    // VALIDAR CARRITO ANTES DE ENVIAR (para procesar pedido)
    // ============================================================
    validarCarritoParaPedido() {
        const carrito = this.obtenerCarrito();
        
        if (!carrito || carrito.length === 0) {
            return { valido: false, mensaje: 'El carrito está vacío' };
        }
        
        // Verificar integridad
        if (!this.verificarIntegridad()) {
            return { valido: false, mensaje: 'El carrito ha sido modificado. Por favor, recarga la página.' };
        }
        
        // Validar cada producto
        for (let item of carrito) {
            if (!this.validarIdProducto(item.idarticulo || item.id)) {
                return {
                    valido: false,
                    mensaje: 'El carrito contiene productos con identificadores inválidos o alterados.'
                };
            }

            if (!this.validarPrecio(item.precio)) {
                return { 
                    valido: false, 
                    mensaje: `El producto "${item.nombre}" tiene un precio inválido. Por favor, recarga la página.` 
                };
            }
            
            const cantidad = parseInt(item.cantidad || 1);
            if (!this.validarCantidad(cantidad)) {
                return { 
                    valido: false, 
                    mensaje: `La cantidad del producto "${item.nombre}" no es válida.` 
                };
            }
        }
        
        return { valido: true, mensaje: 'Carrito válido' };
    },

    // ============================================================
    // LIMPIAR CARRITO (incluye timestamp y hash)
    // ============================================================
    limpiarCarrito() {
        // Activar bandera para evitar recursión
        this._limpiando = true;
        
        this.storage.removeItem(this.STORAGE_KEY);
        this.storage.removeItem(this.TIMESTAMP_KEY);
        this.storage.removeItem(this.HASH_KEY);
        
        // Actualizar contador (ahora retornará 0 porque _limpiando está activo)
        this.actualizarContador();
        
        // Desactivar bandera después de un breve delay
        setTimeout(() => {
            this._limpiando = false;
        }, 100);
        
        return true;
    },

    // ============================================================
    // OBTENER HORAS RESTANTES ANTES DE EXPIRAR
    // ============================================================
    obtenerHorasRestantes() {
        try {
            const timestamp = this.storage.getItem(this.TIMESTAMP_KEY);
            if (!timestamp) {
                return 0;
            }

            const fechaCreacion = new Date(parseInt(timestamp));
            const fechaActual = new Date();
            const horasTranscurridas = (fechaActual - fechaCreacion) / (1000 * 60 * 60);
            const horasRestantes = Math.max(0, this.EXPIRACION_HORAS - horasTranscurridas);

            return Math.floor(horasRestantes * 10) / 10; // Redondear a 1 decimal
        } catch (error) {
            console.error('Error al calcular horas restantes:', error);
            return 0;
        }
    },

    // ============================================================
    // ACTUALIZAR CONTADOR EN EL HEADER
    // ============================================================
    actualizarContador() {
        const cantidad = this.obtenerCantidadTotal();
        
        // Actualizar contador en el header superior (si existe)
        const cartLinkHeader = document.querySelector('.middle-header-optional a[href="cart.php"]');
        if (cartLinkHeader) {
            let contador = cartLinkHeader.querySelector('.cart-count');
            if (!contador) {
                contador = document.createElement('span');
                contador.className = 'cart-count';
                cartLinkHeader.appendChild(contador);
            }
            
            if (cantidad > 0) {
                contador.textContent = cantidad;
                contador.style.display = 'flex';
            } else {
                contador.textContent = '';
                contador.style.display = 'none';
            }
        }
        
        // Actualizar contador en el navbar principal
        const cartLinksNavbar = document.querySelectorAll('.cart-icon-navbar');
        cartLinksNavbar.forEach((cartLinkNavbar) => {
            let contadorNavbar = cartLinkNavbar.querySelector('.cart-count-navbar');
            if (!contadorNavbar) {
                contadorNavbar = document.createElement('span');
                contadorNavbar.className = 'cart-count-navbar';
                cartLinkNavbar.appendChild(contadorNavbar);
            }
            
            if (cantidad > 0) {
                contadorNavbar.textContent = cantidad;
                contadorNavbar.style.display = 'flex';
            } else {
                contadorNavbar.textContent = '';
                contadorNavbar.style.display = 'none';
            }
        });
    },

    // ============================================================
    // MOSTRAR NOTIFICACIÓN
    // ============================================================
    mostrarNotificacion(mensaje) {
        // Si existe SweetAlert, usarlo
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: mensaje,
                showConfirmButton: false,
                timer: 1500,
                toast: true,
                position: 'top-end'
            });
        } else {
            // Si no, usar alert simple
            alert(mensaje);
        }
    }
};

window.Carrito = Carrito;

window.addEventListener('storage', function(event) {
    if (event.key !== 'beautymarket_neonet_payment_approved' || !event.newValue) {
        return;
    }

    try {
        const payload = JSON.parse(event.newValue);
        if (payload && payload.status === 'approved') {
            Carrito.limpiarCarrito();
        }
    } catch (error) {
        console.warn('No fue posible limpiar el carrito despues de NeoNet.', error);
    }
});

// ============================================================
// FUNCIÓN GLOBAL PARA AGREGAR AL CARRITO
// ============================================================
function agregarAlCarrito(idarticulo, nombre, precio, imagen, descripcion = '', cantidad = 1) {
    const producto = {
        idarticulo: idarticulo,
        nombre: nombre,
        precio: precio,
        imagen: imagen,
        descripcion: descripcion
    };
    
    return Carrito.agregarProducto(producto, cantidad);
}

// ============================================================
// HELPERS GLOBALES PARA INPUTS DE CANTIDAD
// ============================================================
function normalizarInputCantidad(inputId, fallback = 1) {
    const input = typeof inputId === 'string' ? document.getElementById(inputId) : inputId;
    if (!input) return Carrito.normalizarCantidad(fallback);

    const cantidad = Carrito.normalizarCantidad(input.value, fallback);
    input.value = cantidad;
    return cantidad;
}

function cambiarInputCantidad(inputId, cambio) {
    const input = typeof inputId === 'string' ? document.getElementById(inputId) : inputId;
    if (!input) return Carrito.CANTIDAD_MINIMA;

    const actual = normalizarInputCantidad(input);
    const paso = parseInt(cambio, 10) >= 0 ? 1 : -1;
    const siguiente = Carrito.normalizarCantidad(actual + paso, actual);

    input.value = siguiente;
    return siguiente;
}

function inicializarInputCantidad(inputId) {
    const input = document.getElementById(inputId);
    if (!input || input.dataset.quantityReady === '1') return;

    input.dataset.quantityReady = '1';
    input.setAttribute('inputmode', 'numeric');
    input.setAttribute('pattern', '[0-9]*');
    input.setAttribute('min', Carrito.CANTIDAD_MINIMA);
    input.setAttribute('max', Carrito.CANTIDAD_MAXIMA);

    input.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 2);
    });

    input.addEventListener('blur', function() {
        normalizarInputCantidad(this);
    });
}

function mpCambiarCantidad(cambio) {
    return cambiarInputCantidad('mp-cantidad', cambio);
}

// ============================================================
// AGREGAR AL CARRITO DESDE EL MODAL
// ============================================================
function agregarAlCarritoDesdeModal() {
    const titulo = document.getElementById('mp-titulo')?.innerText || '';
    const precio = document.getElementById('mp-precio')?.innerText.replace('Q', '') || '0';
    const sku = document.getElementById('mp-sku')?.innerText || '';
    const imagen = document.getElementById('mp-imagen-principal')?.src || '';
    const descripcion = document.getElementById('mp-descripcion')?.innerText || '';
    const cantidad = normalizarInputCantidad('mp-cantidad');
    
    if (!sku) {
        console.error('No se pudo obtener el SKU del producto');
        return;
    }
    
    const agregado = agregarAlCarrito(sku, titulo, precio, imagen, descripcion, cantidad);

    if (!agregado) {
        return;
    }

    const inputCantidad = document.getElementById('mp-cantidad');
    if (inputCantidad) {
        inputCantidad.value = 1;
    }
    
    // Cerrar el modal después de agregar
    setTimeout(() => {
        if (typeof cerrarModal === 'function') {
            cerrarModal();
        }
    }, 500);
}

// ============================================================
// INICIALIZAR CONTADOR AL CARGAR LA PÁGINA
// Y VERIFICAR EXPIRACIÓN AUTOMÁTICAMENTE
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    // Verificar y limpiar carrito expirado al cargar la página
    if (Carrito.carritoExpirado()) {
        Carrito.limpiarCarrito();
    }
    
    Carrito.actualizarContador();
    inicializarInputCantidad('mp-cantidad');
    inicializarInputCantidad('producto-cantidad');
});

