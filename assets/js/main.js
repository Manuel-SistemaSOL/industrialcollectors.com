window.BM_PRODUCT_IMAGE_FALLBACK = window.BM_PRODUCT_IMAGE_FALLBACK || 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png';

jQuery(function ($) {
    'use strict';

	// Header Sticky
	$(window).on('scroll',function() {
		if ($(this).scrollTop() > 120){  
			$('.navbar-area').addClass("is-sticky");
		}
		else{
			$('.navbar-area').removeClass("is-sticky");
		}
	});

	// Mean Menu
	jQuery('.mean-menu').meanmenu({
		meanScreenWidth: "991"
	});
	
	// Others Option For Responsive JS
	$(".others-option-for-responsive .dot-menu").on("click", function(){
		$(".others-option-for-responsive .container .container").toggleClass("active");
	});
	
	// Button Hover JS
	$('.default-btn')
	.on('mouseenter', function(e) {
		var parentOffset = $(this).offset(),
		relX = e.pageX - parentOffset.left,
		relY = e.pageY - parentOffset.top;
		$(this).find('span').css({top:relY, left:relX})
	})
	.on('mouseout', function(e) {
		var parentOffset = $(this).offset(),
		relX = e.pageX - parentOffset.left,
		relY = e.pageY - parentOffset.top;
		$(this).find('span').css({top:relY, left:relX})
	});

	// Nice Select JS
	$('select').niceSelect();
	
	// Cerrar dropdown de categorías al hacer clic fuera
	$(document).on('click', function(e) {
		if (!$(e.target).closest('.nice-select').length) {
			$('.nice-select').removeClass('open');
		}
	});
	
	// Home Slides
	$('.home-slides').owlCarousel({
		loop: true,
		nav: true,
		dots: false,
		autoplayHoverPause: true,
		items: 1,
		smartSpeed: 100,
		autoplay: false,
		navText: [
			"<i class='flaticon-left-arrow'></i>",
			"<i class='flaticon-right-arrow'></i>"
		],
	});
	$(".home-slides").on("translate.owl.carousel", function(){
		$(".main-slider-content b").removeClass("animate__animated animate__fadeInUp").css("opacity", "0");
		$(".main-slider-content h1").removeClass("animate__animated animate__fadeInUp").css("opacity", "0");
		$(".main-slider-content p").removeClass("animate__animated animate__fadeInUp").css("opacity", "0");
		$(".main-slider-content a").removeClass("animate__animated animate__fadeInUp").css("opacity", "0");
	});
	$(".home-slides").on("translated.owl.carousel", function(){
		$(".main-slider-content b").addClass("animate__animated animate__fadeInUp").css("opacity", "1");
		$(".main-slider-content h1").addClass("animate__animated animate__fadeInUp").css("opacity", "1");
		$(".main-slider-content p").addClass("animate__animated animate__fadeInUp").css("opacity", "1");
		$(".main-slider-content a").addClass("animate__animated animate__fadeInUp").css("opacity", "1");
	});
	
	// Home Slides Two - Slider manual tipo stacked cards
	const sliderStack = document.querySelector('.home-slides-two.slider-stack');
	const nextBtnStack = document.querySelector('.slider-stack-next');
	const prevBtnStack = document.querySelector('.slider-stack-prev');

	if (sliderStack && nextBtnStack && prevBtnStack) {
		const moveNext = () => {
			const items = sliderStack.querySelectorAll('.main-slider-item-box');
			if (items.length) {
				sliderStack.appendChild(items[0]);
			}
		};

		const movePrev = () => {
			const items = sliderStack.querySelectorAll('.main-slider-item-box');
			if (items.length) {
				sliderStack.prepend(items[items.length - 1]);
			}
		};

		nextBtnStack.addEventListener('click', moveNext);
		prevBtnStack.addEventListener('click', movePrev);

		let autoSlider = setInterval(moveNext, 5500);

		const sliderArea = document.querySelector('.main-slider-area');
		if (sliderArea) {
			sliderArea.addEventListener('mouseenter', () => clearInterval(autoSlider));
			sliderArea.addEventListener('mouseleave', () => {
				autoSlider = setInterval(moveNext, 5500);
			});
		}
	}

	// Products Details Image Slides
	$('.products-details-image-slides').slick({
		dots: true,
		speed: 500,
		fade: false,
		slide: 'li',
		slidesToShow: 1,
		autoplay: true,
		autoplaySpeed: 4000,
		prevArrow: false,
		nextArrow: false,
		responsive: [{
			breakpoint: 800,
			settings: {
				arrows: false,
				centerMode: false,
				centerPadding: '40px',
				variableWidth: false,
				slidesToShow: 1,
				dots: true
			},
			breakpoint: 1200,
			settings: {
				arrows: false,
				centerMode: false,
				centerPadding: '40px',
				variableWidth: false,
				slidesToShow: 1,
				dots: true
			}
		}],
		customPaging: function (slider, i) {
			return '<button class="tab">' + $('.slick-thumbs li:nth-child(' + (i + 1) + ')').html() + '</button>';
		}
	});
	
	// Testimonial Slides
	$('.testimonial-slides').owlCarousel({
		loop: true,
		nav: true,
		dots: false,
		autoplayHoverPause: true,
		items: 1,
		smartSpeed: 100,
		autoplay: false,
		navText: [
			"<i class='flaticon-left-arrow'></i>",
			"<i class='flaticon-right-arrow'></i>"
		],
	});

	// Main Products Image Slides
	$('.slider-for').slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: false,
		fade: true,
		asNavFor: '.slider-nav'
	});
	$('.slider-nav').slick({
		slidesToShow: 3,
		slidesToScroll: 1,
		asNavFor: '.slider-for',
		dots: false,
		arrows: false,
		focusOnSelect: true,
		verticalSwiping: true,
		vertical: true
	});
	$('a[data-slide]').on('click', function(e) {
		e.preventDefault();
		var slideno = $(this).data('slide');
		$('.slider-nav').slick('slickGoTo', slideno - 1);
	});
	
	// Tabs
	$('.tab ul.tabs').addClass('active').find('> li:eq(0)').addClass('current');
	$('.tab ul.tabs li a').on('click', function (g) {
		var tab = $(this).closest('.tab'), 
		index = $(this).closest('li').index();
		tab.find('ul.tabs > li').removeClass('current');
		$(this).closest('li').addClass('current');
		tab.find('.tab_content').find('div.tabs_item').not('div.tabs_item:eq(' + index + ')').slideUp();
		tab.find('.tab_content').find('div.tabs_item:eq(' + index + ')').slideDown();
		g.preventDefault();
	});

	// Count Time 
	function makeTimer() {
		var endTime = new Date("September 20, 2029 17:00:00 PDT");			
		var endTime = (Date.parse(endTime)) / 1000;
		var now = new Date();
		var now = (Date.parse(now) / 1000);
		var timeLeft = endTime - now;
		var days = Math.floor(timeLeft / 86400); 
		var hours = Math.floor((timeLeft - (days * 86400)) / 3600);
		var minutes = Math.floor((timeLeft - (days * 86400) - (hours * 3600 )) / 60);
		var seconds = Math.floor((timeLeft - (days * 86400) - (hours * 3600) - (minutes * 60)));
		if (hours < "10") { hours = "0" + hours; }
		if (minutes < "10") { minutes = "0" + minutes; }
		if (seconds < "10") { seconds = "0" + seconds; }
		$("#days").html(days + "<span>Days</span>");
		$("#hours").html(hours + "<span>Hours</span>");
		$("#minutes").html(minutes + "<span>Minutes</span>");
		$("#seconds").html(seconds + "<span>Seconds</span>");
	}
	setInterval(function() { makeTimer(); }, 0);

	// Products Filter Options
	$(".icon-view-two").on("click", function(e){
		e.preventDefault();
		document.getElementById("products-collections-filter").classList.add('products-col-two')
		document.getElementById("products-collections-filter").classList.remove('products-col-one', 'products-col-three', 'products-col-four', 'products-row-view');
	});
	$(".icon-view-three").on("click", function(e){
		e.preventDefault();
		document.getElementById("products-collections-filter").classList.add('products-col-three')
		document.getElementById("products-collections-filter").classList.remove('products-col-one', 'products-col-two', 'products-col-four', 'products-row-view');
	});
	$('.products-filter-options .view-column a').on('click', function(){
		$('.view-column a').removeClass("active");
		$(this).addClass("active");
	});
	
	// Range Slider - Solo inicializar si no está en tienda.php (donde se inicializa dinámicamente)
	if ($("#range-slider").length && !$("#range-slider").hasClass("ui-slider")) {
		$( "#range-slider" ).slider({
			range: true,
			min: 50,
			max: 400,
			values: [50, 400],
			slide: function( event, ui ) {
				$( "#price-amount" ).val( "Q" + ui.values[ 0 ] + " - Q" + ui.values[ 1 ] );
			}
		});
		$( "#price-amount" ).val( "Q" + $( "#range-slider" ).slider( "values", 0 ) +
		" - Q" + $( "#range-slider" ).slider( "values", 1 ) );
	}  

	// Popup Video
	$('.popup-youtube').magnificPopup({
		disableOn: 320,
		type: 'iframe',
		mainClass: 'mfp-fade',
		removalDelay: 160,
		preloader: false,
		fixedContentPos: false
	});

	// FAQ Accordion
	$('.accordion').find('.accordion-title').on('click', function(){
		$(this).toggleClass('active');
		$(this).next().slideToggle('fast')
		$('.accordion-content').not($(this).next()).slideUp('fast');
		$('.accordion-title').not($(this)).removeClass('active');		
	});

	// Odometer JS
	$('.odometer').appear(function(e) {
		var odo = $(".odometer");
		odo.each(function() {
			var countNumber = $(this).attr("data-count");
			$(this).html(countNumber);
		});
	});
	
	// Subscribe form
	$(".newsletter-form").validator().on("submit", function (event) {
		if (event.isDefaultPrevented()) {
			formErrorSub();
			submitMSGSub(false, "Please enter your email correctly.");
		} 
		else {
			event.preventDefault();
		}
	});
	function callbackFunction (resp) {
		if (resp.result === "success") {
			formSuccessSub();
		}
		else {
			formErrorSub();
		}
	}
	function formSuccessSub(){
		$(".newsletter-form")[0].reset();
		submitMSGSub(true, "Thank you for subscribing!");
		setTimeout(function() {
			$("#validator-newsletter").addClass('hide');
		}, 4000)
	}
	function formErrorSub(){
		$(".newsletter-form").addClass("animated shake");
		setTimeout(function() {
			$(".newsletter-form").removeClass("animated shake");
		}, 1000)
	}
	function submitMSGSub(valid, msg){
		if(valid){
			var msgClasses = "validation-success";
		} 
		else {
			var msgClasses = "validation-danger";
		}
		$("#validator-newsletter").removeClass().addClass(msgClasses).text(msg);
	}

	// AJAX MailChimp
	$(".newsletter-form").ajaxChimp({
		url: "https://envytheme.us20.list-manage.com/subscribe/post?u=60e1ffe2e8a68ce1204cd39a5&amp;id=42d6d188d9", 
		callback: callbackFunction
	});

	// Input Plus & Minus Number JS
	$('.input-counter').each(function() {
		var spinner = jQuery(this),
		input = spinner.find('input[type="text"]'),
		btnUp = spinner.find('.plus-btn'),
		btnDown = spinner.find('.minus-btn'),
		min = parseInt(input.attr('min'), 10) || 1,
		max = parseInt(input.attr('max'), 10) || 99;

		if (btnUp.attr('onclick') || btnDown.attr('onclick')) {
			return;
		}
		
		btnUp.on('click', function() {
			var oldValue = parseInt(input.val(), 10) || min;
			if (oldValue >= max) {
				var newVal = oldValue;
			} 
			else {
				var newVal = oldValue + 1;
			}
			spinner.find("input").val(newVal);
			spinner.find("input").trigger("change");
		});
		btnDown.on('click', function() {
			var oldValue = parseInt(input.val(), 10) || min;
			if (oldValue <= min) {
				var newVal = oldValue;
			} 
			else {
				var newVal = oldValue - 1;
			}
			spinner.find("input").val(newVal);
			spinner.find("input").trigger("change");
		});
	});
	
	// Go to Top JS
	$(window).on('scroll', function() {
		var scrolled = $(window).scrollTop();
		if (scrolled > 600) $('.go-top').addClass('active');
		if (scrolled < 600) $('.go-top').removeClass('active');
	});  
	$('.go-top').on('click', function() {
		$("html, body").animate({ scrollTop: "0" },  500);
	});
	
	jQuery(function ($) {
		$('.preloader').fadeOut();
	});

});

// ============================================================
// FUNCIÓN GLOBAL PARA BUSCAR PRODUCTOS
// ============================================================
function BM_esPaginaTiendaConBusqueda() {
    return Boolean(
        document.getElementById('products-collections-filter') &&
        typeof window.cargarProductosPorBusqueda === 'function'
    );
}

function BM_buscarProductosEnVivo(termino) {
    const texto = (termino || '').trim();

    if (!BM_esPaginaTiendaConBusqueda()) {
        return false;
    }

    if (!texto) {
        if (typeof window.cargarProductosNuevos === 'function') {
            window.cargarProductosNuevos();
        }
        return true;
    }

    window.cargarProductosPorBusqueda(texto);
    return true;
}

function BM_getHeaderSearchInputs() {
    const inputs = Array.prototype.slice.call(document.querySelectorAll('.bm-search-input, #search, #search-mobile'));

    return inputs.filter(function(input, index) {
        return input && inputs.indexOf(input) === index;
    });
}

function BM_isVisibleHeaderSearchInput(input) {
    if (!input) {
        return false;
    }

    const styles = window.getComputedStyle(input);
    const rect = input.getBoundingClientRect();

    return styles.display !== 'none' &&
        styles.visibility !== 'hidden' &&
        rect.width > 0 &&
        rect.height > 0;
}

function BM_getActiveHeaderSearchInput(source) {
    const trigger = source && source.nodeType === 1 ? source : null;
    const scopedInput = trigger && trigger.closest('form')
        ? trigger.closest('form').querySelector('.bm-search-input, input[name="search"]')
        : null;

    if (scopedInput) {
        return scopedInput;
    }

    if (
        document.activeElement &&
        document.activeElement.matches &&
        document.activeElement.matches('.bm-search-input, #search, #search-mobile')
    ) {
        return document.activeElement;
    }

    const inputs = BM_getHeaderSearchInputs();
    return inputs.find(BM_isVisibleHeaderSearchInput) || inputs[0] || null;
}

function BM_syncHeaderSearchInputs(value, source) {
    BM_getHeaderSearchInputs().forEach(function(input) {
        if (input !== source) {
            input.value = value;
        }

        input.defaultValue = value;
        input.setAttribute('value', value);
    });
}

function BM_restoreHeaderSearchFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('buscar')) {
        BM_syncHeaderSearchInputs(urlParams.get('buscar') || '');
    }
}

function buscarProductos(source) {
    const searchInput = BM_getActiveHeaderSearchInput(source);
    const termino = searchInput ? searchInput.value.trim() : '';
    BM_syncHeaderSearchInputs(termino, searchInput);

    if (BM_buscarProductosEnVivo(termino)) {
        return;
    }
    
    if (!termino) {
        if (searchInput) {
            searchInput.focus();
        }
        return;
    }
    
    // Redirigir a tienda.php con el parámetro de búsqueda
    window.location.href = 'tienda.php?buscar=' + encodeURIComponent(termino);
}

window.buscarProductos = buscarProductos;
window.BM_syncHeaderSearchInputs = BM_syncHeaderSearchInputs;
window.BM_restoreHeaderSearchFromUrl = BM_restoreHeaderSearchFromUrl;

// Restaurar, enviar y filtrar busqueda desde la barra fija.
document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = BM_getHeaderSearchInputs();
    const searchPanel = document.getElementById('header-search-panel');
    let searchTimer = null;

    if (searchPanel) {
        searchPanel.classList.add('is-open');
    }

    if (!searchInputs.length) {
        return;
    }

    BM_restoreHeaderSearchFromUrl();
    window.setTimeout(BM_restoreHeaderSearchFromUrl, 150);
    window.setTimeout(BM_restoreHeaderSearchFromUrl, 650);

    searchInputs.forEach(function(searchInput) {
        searchInput.addEventListener('input', function() {
            BM_syncHeaderSearchInputs(searchInput.value, searchInput);

            if (!BM_esPaginaTiendaConBusqueda()) {
                return;
            }

            const termino = searchInput.value.trim();
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function() {
                BM_buscarProductosEnVivo(termino);
            }, 260);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarProductos(searchInput);
            }
        });
    });
});

window.addEventListener('load', BM_restoreHeaderSearchFromUrl);
window.addEventListener('pageshow', BM_restoreHeaderSearchFromUrl);

document.addEventListener('click', function(event) {
    const menuLink = event.target.closest(
        '.bm-desktop-nav .nav-link[href], .bm-mobile-row[href], .navbar-brand[href], .main-responsive-logo a[href]'
    );

    if (!menuLink) {
        return;
    }

    const href = menuLink.getAttribute('href') || '';
    const isPlainLeftClick = event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey;

    if (!isPlainLeftClick || href === '' || href.charAt(0) === '#' || /^javascript:/i.test(href) || menuLink.target === '_blank') {
        return;
    }

    event.preventDefault();
    window.location.href = menuLink.href;
}, true);

// ============================================================
// Helpers compartidos de catalogo y vista rapida
// ============================================================
(function (window, document) {
    'use strict';

    let mpIndex = 0;
    let mpImagenes = [];
    let mpProductoActual = null;

    function normalizarNumeroPrecio(valor) {
        if (typeof valor === 'number') {
            return Number.isFinite(valor) ? valor : NaN;
        }

        const numero = parseFloat(
            String(valor ?? '')
                .replace(/[^\d.,-]/g, '')
                .replace(/,/g, '')
        );

        return Number.isFinite(numero) ? numero : NaN;
    }

    function formatearPrecioProducto(valor) {
        const numero = normalizarNumeroPrecio(valor);
        return Number.isFinite(numero) ? numero.toFixed(2) : '0.00';
    }

    function obtenerPrecioVentaProducto(producto) {
        if (!producto || typeof producto !== 'object') {
            return 0;
        }

        const precio = normalizarNumeroPrecio(
            producto.precio_venta_original ??
            producto.precioVentaOriginal ??
            producto.precio_normal ??
            producto.precio_venta ??
            producto.precio ??
            0
        );

        return Number.isFinite(precio) && precio > 0 ? precio : 0;
    }

    function obtenerPrecioOfertaProducto(producto) {
        if (!producto || typeof producto !== 'object') {
            return 0;
        }

        const camposOferta = [
            producto.precio_09,
            producto.precio09,
            producto.precio9,
            producto.precio_oferta,
            producto.precioOferta,
            producto.precio_promocion,
            producto.precioPromocion,
        ];

        for (const valor of camposOferta) {
            const precio = normalizarNumeroPrecio(valor);

            if (Number.isFinite(precio) && precio > 0) {
                return precio;
            }
        }

        return 0;
    }

    function obtenerPrecioFinalProducto(producto) {
        const precioOferta = obtenerPrecioOfertaProducto(producto);

        if (precioOferta > 0) {
            return precioOferta;
        }

        return obtenerPrecioVentaProducto(producto);
    }

    function tienePrecioOfertaProducto(producto) {
        return obtenerPrecioOfertaProducto(producto) > 0;
    }

    function normalizarPreciosProducto(producto) {
        if (!producto || typeof producto !== 'object') {
            return producto;
        }

        const precioVenta = obtenerPrecioVentaProducto(producto);
        const precioOferta = obtenerPrecioOfertaProducto(producto);
        const precioFinal = precioOferta > 0 ? precioOferta : precioVenta;

        return {
            ...producto,
            precio_venta_original: precioVenta > 0 ? formatearPrecioProducto(precioVenta) : '0.00',
            precio_oferta: precioOferta > 0 ? formatearPrecioProducto(precioOferta) : '0.00',
            precio_09: producto.precio_09 ?? (precioOferta > 0 ? formatearPrecioProducto(precioOferta) : '0.00'),
            precio_venta: formatearPrecioProducto(precioFinal),
            precio: formatearPrecioProducto(precioFinal),
        };
    }

    function obtenerValorStockProducto(producto) {
        if (!producto || typeof producto !== 'object') {
            return '';
        }

        const camposStock = [
            producto.stock,
            producto.stocksucursal,
            producto.stock_sucursal,
            producto.existencia,
            producto.cantidad_stock,
            producto.disponibilidad,
        ];

        for (const valor of camposStock) {
            if (valor === 0 || valor === '0') {
                return '0';
            }

            if (valor === null || valor === undefined) {
                continue;
            }

            const texto = String(valor).trim();
            const textoNormalizado = texto.toLowerCase();

            if (
                texto !== '' &&
                textoNormalizado !== 'null' &&
                textoNormalizado !== 'undefined' &&
                textoNormalizado !== 'n/a'
            ) {
                return texto;
            }
        }

        return '';
    }

    function esProductoAgotado(producto) {
        const stock = obtenerValorStockProducto(producto);

        if (stock === '') {
            return false;
        }

        const texto = String(stock).trim().toLowerCase();
        const numero = parseFloat(texto.replace(',', '.'));

        if (Number.isFinite(numero)) {
            return numero <= 0;
        }

        return [
            'agotado',
            'sin stock',
            'no disponible',
            'no hay stock',
            '0 unidades',
        ].some((estado) => texto.includes(estado));
    }

    function obtenerHtmlEtiquetaAgotadoProducto(producto, claseExtra = '') {
        if (!esProductoAgotado(producto)) {
            return '';
        }

        const clases = ['bm-stock-ribbon', claseExtra].filter(Boolean).join(' ');
        return `<span class="${clases}" aria-label="Producto agotado">Agotado</span>`;
    }

    function obtenerHtmlPreciosProducto(producto) {
        const precioFinal = formatearPrecioProducto(obtenerPrecioFinalProducto(producto));

        return `
            <span class="product-card-price product-card-price-normal"><span class="product-card-price-label">Precio: </span>Q${precioFinal}</span>
        `;
    }

    function escaparAtributoHtml(valor) {
        return String(valor ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function escaparCadenaInline(valor) {
        return escaparAtributoHtml(
            String(valor ?? '')
                .replace(/\\/g, '\\\\')
                .replace(/[\r\n]+/g, ' ')
                .replace(/'/g, "\\'")
        );
    }

    function obtenerHtmlAccionesProducto(producto, imagen) {
        const idarticulo = producto && producto.idarticulo ? producto.idarticulo : '';
        const nombre = producto && producto.nombre ? producto.nombre : '';
        const precio = formatearPrecioProducto(obtenerPrecioFinalProducto(producto));
        const descripcion = producto && producto.descripcion ? producto.descripcion : '';
        const urlDetalle = 'producto.php?id=' + encodeURIComponent(String(idarticulo));
        const agotado = esProductoAgotado(producto);
        const accionCarrito = agotado
            ? '<span class="product-card-action product-card-action-cart is-disabled" aria-disabled="true">Agotado</span>'
            : `<a class="product-card-action product-card-action-cart" href="javascript:void(0)"
                    onclick="agregarAlCarrito(
                        '${escaparCadenaInline(idarticulo)}',
                        '${escaparCadenaInline(nombre)}',
                        '${escaparCadenaInline(precio)}',
                        '${escaparCadenaInline(imagen)}',
                        '${escaparCadenaInline(descripcion)}',
                        1
                    )">
                    Agregar al carrito
                </a>`;

        return `
            <div class="product-card-actions">
                ${accionCarrito}
                <a class="product-card-action product-card-action-details" href="${escaparAtributoHtml(urlDetalle)}">
                    Ver m&aacute;s
                </a>
            </div>
        `;
    }

    function vistaRapida(nombre, imagen, precio, idarticulo) {
        window.location.href = 'producto.php?id=' + encodeURIComponent(idarticulo);
    }

    function actualizarClaseImagenTemporal(imageElement, imageUrl) {
        if (!imageElement) return;

        if (typeof window.BM_isProductImageFallbackUrl === 'function' && window.BM_isProductImageFallbackUrl(imageUrl)) {
            imageElement.classList.add('bm-product-image-fallback');
            return;
        }

        imageElement.classList.remove('bm-product-image-fallback');
    }

    function abrirModalProducto(producto) {
        const modal = document.getElementById('modalProducto');
        if (!modal || !producto) return;

        producto = normalizarPreciosProducto(producto);

        const imagenFallback = window.BM_PRODUCT_IMAGE_FALLBACK || 'assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png';
        mpImagenes = Array.isArray(producto.imagenes) && producto.imagenes.length
            ? producto.imagenes
            : [producto.imagen].filter(function (img) {
                return typeof window.BM_isInvalidProductImage === 'function'
                    ? !window.BM_isInvalidProductImage(img)
                    : Boolean(img);
            });

        mpImagenes = mpImagenes.map(function (img) {
            return typeof window.BM_resolveProductImage === 'function'
                ? window.BM_resolveProductImage(img)
                : img;
        });

        if (!mpImagenes.length) {
            mpImagenes = [imagenFallback];
        }
        mpIndex = 0;
        mpProductoActual = producto;

        const titulo = document.getElementById('mp-titulo');
        const precio = document.getElementById('mp-precio');
        const descripcion = document.getElementById('mp-descripcion');
        const stock = document.getElementById('mp-stock');
        const sku = document.getElementById('mp-sku');
        const imagenPrincipal = document.getElementById('mp-imagen-principal');
        const thumbs = document.getElementById('mp-thumbs');

        if (titulo) titulo.innerText = producto.nombre || '';
        if (precio) precio.innerText = 'Q' + formatearPrecioProducto(obtenerPrecioFinalProducto(producto));
        if (descripcion) descripcion.innerText = producto.descripcion || '';
        if (stock) stock.innerText = producto.stocksucursal || 'En stock';
        if (sku) sku.innerText = producto.sku || producto.idarticulo || '';
        if (imagenPrincipal) {
            imagenPrincipal.onerror = function () {
                if (typeof window.BM_applyProductImageFallback === 'function') {
                    window.BM_applyProductImageFallback(this);
                    return;
                }

                this.onerror = null;
                this.classList.add('bm-product-image-fallback');
                this.src = imagenFallback;
            };
        }
        if (imagenPrincipal && mpImagenes[0]) {
            imagenPrincipal.src = mpImagenes[0];
            actualizarClaseImagenTemporal(imagenPrincipal, mpImagenes[0]);
        }

        if (thumbs) {
            thumbs.innerHTML = '';
            mpImagenes.forEach((img, i) => {
                const imageFallbackAttribute = typeof window.BM_isProductImageFallbackUrl === 'function' && window.BM_isProductImageFallbackUrl(img)
                    ? ' class="bm-product-image-fallback"'
                    : '';
                thumbs.innerHTML += `
                    <div class="mp-thumb-item ${i === 0 ? 'active' : ''}" onclick="mpMostrar(${i})">
                        <img${imageFallbackAttribute} src="${img}" alt="" onerror="window.BM_applyProductImageFallback ? window.BM_applyProductImageFallback(this) : (this.onerror=null,this.src='${window.BM_PRODUCT_IMAGE_FALLBACK}')">
                    </div>
                `;
            });
        }

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal() {
        const modal = document.getElementById('modalProducto');
        if (modal) {
            modal.classList.remove('show');
        }
        document.body.style.overflow = '';
    }

    function mpMostrar(i) {
        if (!mpImagenes.length) return;

        mpIndex = Math.max(0, Math.min(parseInt(i, 10) || 0, mpImagenes.length - 1));
        const imagenPrincipal = document.getElementById('mp-imagen-principal');
        if (imagenPrincipal) {
            imagenPrincipal.src = mpImagenes[mpIndex];
            actualizarClaseImagenTemporal(imagenPrincipal, mpImagenes[mpIndex]);
        }

        const thumbs = document.querySelectorAll('.mp-thumb-item, .mp-thumb');
        thumbs.forEach((thumb) => thumb.classList.remove('active'));
        if (thumbs[mpIndex]) {
            thumbs[mpIndex].classList.add('active');
        }
    }

    function imgNext() {
        if (!mpImagenes.length) return;
        mpMostrar((mpIndex + 1) % mpImagenes.length);
    }

    function imgPrev() {
        if (!mpImagenes.length) return;
        mpMostrar((mpIndex - 1 + mpImagenes.length) % mpImagenes.length);
    }

    function obtenerUrlProducto() {
        if (!mpProductoActual || !mpProductoActual.idarticulo) {
            return window.location.origin + window.location.pathname;
        }

        return new URL(
            `producto.php?id=${encodeURIComponent(mpProductoActual.idarticulo)}`,
            window.location.href
        ).href;
    }

    function obtenerTextoCompartir() {
        if (!mpProductoActual) return '';

        const nombre = mpProductoActual.nombre || 'Producto';
        const precio = formatearPrecioProducto(obtenerPrecioFinalProducto(mpProductoActual));
        return `Mira este producto en Industrial Collectors, S.A.\n\n${nombre}\nPrecio: Q${precio}\n\n${obtenerUrlProducto()}`;
    }

    function compartirWhatsApp(event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!mpProductoActual) {
            alert('No hay informacion del producto para compartir');
            return false;
        }

        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(obtenerTextoCompartir())}`;
        const esMovil = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        if (esMovil) {
            window.location.href = whatsappUrl;
        } else {
            window.open(whatsappUrl, '_blank');
        }

        return false;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalProducto');
        if (modal && modal.dataset.catalogoModalReady !== '1') {
            modal.dataset.catalogoModalReady = '1';
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    cerrarModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            const modalActual = document.getElementById('modalProducto');
            if (event.key === 'Escape' && modalActual && modalActual.classList.contains('show')) {
                cerrarModal();
            }
        });
    });

    window.BM_parseProductPrice = normalizarNumeroPrecio;
    window.BM_getProductRegularPrice = obtenerPrecioVentaProducto;
    window.BM_getProductOfferPrice = obtenerPrecioOfertaProducto;
    window.BM_getProductEffectivePrice = obtenerPrecioFinalProducto;
    window.BM_hasProductOfferPrice = tienePrecioOfertaProducto;
    window.BM_normalizeProductPricing = normalizarPreciosProducto;
    window.formatearPrecioProducto = formatearPrecioProducto;
    window.obtenerHtmlPreciosProducto = obtenerHtmlPreciosProducto;
    window.obtenerHtmlAccionesProducto = obtenerHtmlAccionesProducto;
    window.BM_getProductStockValue = obtenerValorStockProducto;
    window.BM_isProductOutOfStock = esProductoAgotado;
    window.BM_getOutOfStockRibbonHtml = obtenerHtmlEtiquetaAgotadoProducto;
    window.vistaRapida = vistaRapida;
    window.abrirModalProducto = abrirModalProducto;
    window.cerrarModal = cerrarModal;
    window.mpMostrar = mpMostrar;
    window.imgNext = imgNext;
    window.imgPrev = imgPrev;
    window.obtenerUrlProducto = obtenerUrlProducto;
    window.obtenerTextoCompartir = obtenerTextoCompartir;
    window.compartirWhatsApp = compartirWhatsApp;
})(window, document);

// function to set a given theme/color-scheme
function setTheme(themeName) {
    localStorage.setItem('ejon_theme', themeName);
    document.documentElement.className = themeName;
}

// function to toggle between light and dark theme
function toggleTheme() {
    if (localStorage.getItem('ejon_theme') === 'theme-dark') {
        setTheme('theme-light');
    } else {
        setTheme('theme-dark');
    }
}

// Immediately invoked function to set the theme on initial load
(function () {
    const slider = document.getElementById('slider');
    if (localStorage.getItem('ejon_theme') === 'theme-dark') {
        setTheme('theme-dark');
        if (slider) slider.checked = false;
    } else {
        setTheme('theme-light');
        if (slider) slider.checked = true;
    }
})();
