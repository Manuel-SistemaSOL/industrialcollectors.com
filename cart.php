<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$csrfKey = 'cybersource_csrf_token';
if (empty($_SESSION[$csrfKey])) {
    $_SESSION[$csrfKey] = bin2hex(random_bytes(32));
}

$current_page = basename($_SERVER['PHP_SELF']);

$seo_title = "Carrito de compras | Industrial Collectors, S.A.";

$seo_description = "Revisa los productos agregados a tu carrito y continúa con tu pedido en Industrial Collectors, S.A.";

$seo_canonical_url = "https://industrialcollectors.com/cart.php";

$seo_robots = "noindex, follow";

include 'head.php';

?>
        <!-- Start Page Banner -->
        <div class="page-title-area">
            <div class="container">
                <div class="page-title-content">
                    <h2>Carrito de compras</h2>

                    <ul>
                        <li><a href="/">Inicio</a></li>
                        <li>Carrito de compras</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- End Page Banner -->

        <!-- Start Cart Area -->
		<section class="cart-area ptb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-12">
                        <form>
                            <div class="cart-table table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col">Opciones</th>
                                            <th scope="col">Artículo</th>
                                            <th scope="col">Precio unitario</th>
                                            <th scope="col">Cantidad</th>
                                            <th scope="col">Total</th>
                                        </tr>
                                    </thead>
        
                                    <tbody>
                                        <!-- Los productos se cargarán dinámicamente desde localStorage -->
                                    </tbody>
                                </table>
                            </div>
        

                        </form>
                    </div>

                    <div class="col-lg-4 col-md-12">
                        <div class="cart-totals">
                            <h3>Resumen del carrito</h3>
    
                            <ul>
                                <li class="cart-total-normal">
                                    <span>Total del pedido</span>
                                    <strong>Q0.00</strong>
                                </li>
                            </ul>
    
                            <a href="javascript:void(0)" class="default-btn" id="btn-continuar-pedido" onclick="abrirModalPedido()">
                                Continuar con el pedido
                                <span></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Cart Area -->

        <!-- ===========================
            MODAL FORMULARIO DE PEDIDO
        =========================== -->
        <div id="modalPedido" class="mp-overlay">
            <div class="mp-modal mp-modal-form">
                <button class="mp-close" onclick="cerrarModalPedido()">&times;</button>
                
                <div class="mp-form-content">
                    <h2>Datos del Pedido</h2>
                    <p class="mp-form-subtitle">Por favor complete la siguiente informaci&oacute;n para continuar</p>
                    
                    <form id="formPedido" onsubmit="procesarPedido(event)">
                        <div class="mp-form-group">
                            <label for="nombreCompleto">Nombre Completo <span class="required">*</span></label>
                            <input type="text" id="nombreCompleto" name="nombreCompleto"
                                class="form-control"
                                maxlength="80"
                                autocomplete="name"
                                required>
                            <small class="input-error" id="errorNombreCompleto"></small>
                        </div>

                        <div class="mp-form-group">
                            <label for="direccion">Direcci&oacute;n <span class="required">*</span></label>
                            <input type="text" id="direccion" name="direccion"
                                class="form-control"
                                maxlength="150"
                                autocomplete="street-address"
                                required>
                            <small class="input-error" id="errorDireccion"></small>
                        </div>

                        <div class="mp-form-group">
                            <label for="telefono">Tel&eacute;fono <span class="required">*</span></label>
                            <input type="text" id="telefono" name="telefono"
                                class="form-control"
                                maxlength="8"
                                inputmode="numeric"
                                autocomplete="tel"
                                required>
                            <small class="input-error" id="errorTelefono"></small>
                        </div>

                        <div class="mp-form-group">
                            <label for="correo">Correo Electr&oacute;nico <span class="required">*</span></label>
                            <input type="email" id="correo" name="correo"
                                class="form-control"
                                maxlength="120"
                                autocomplete="email"
                                required>
                            <small class="input-error" id="errorCorreo"></small>
                        </div>

                        <div class="mp-form-group mp-checkbox-group">
                            <label>
                                <input type="checkbox" id="necesitaFactura" name="necesitaFactura" onchange="toggleNit()">
                                Necesito factura
                            </label>
                        </div>

                        <div class="mp-form-group" id="nit-group" style="display: none;">
                            <label for="tipoDocumento">Tipo de Documento <span class="required">*</span></label>
                            <select id="tipoDocumento" name="tipoDocumento" class="form-control" onchange="cambiarTipoDocumento()">
                                <option value="">Seleccione</option>
                                <option value="nit">NIT</option>
                                <option value="dpi">DPI</option>
                                <option value="cf">CF</option>
                            </select>
                            <small class="input-error" id="errorTipoDocumento"></small>
                            <small class="document-help" id="documentoHelp"></small>
                        </div>

                        <div class="mp-form-group" id="numeroDocumento-group" style="display: none;">
                            <label for="numeroDocumento" id="labelNumeroDocumento">N&uacute;mero de Documento <span class="required">*</span></label>
                            <input type="text" id="numeroDocumento" name="numeroDocumento"
                                class="form-control"
                                maxlength="20">
                            <small class="input-error" id="errorNumeroDocumento"></small>
                        </div>

                        <div class="mp-form-group mp-payment-group">
                            <label for="formaPago">M&eacute;todo de Pago <span class="required">*</span></label>
                            <select id="formaPago" name="formaPago" class="form-control" onchange="actualizarResumenPedido()" required>
                                <option value="">Seleccione m&eacute;todo de pago</option>
                                <option value="contra_entrega">Pago Contra Entrega</option>
                                <option value="tarjeta">Tarjeta de Cr&eacute;dito/D&eacute;bito</option>
                                <option value="transferencia">Transferencia Bancaria</option>
                            </select>
                            <small class="input-error" id="errorFormaPago"></small>
                        </div>

                        <div class="mp-form-group mp-delivery-group">
                            <label for="modalidadEntrega">Modalidad y lugar de entrega <span class="required">*</span></label>
                            <select id="modalidadEntrega" name="modalidadEntrega" class="form-control mp-delivery-select" onchange="actualizarResumenPedido()" required disabled>
                                <option value="">Seleccione primero el m&eacute;todo de pago</option>
                            </select>
                            <small class="mp-delivery-note" id="modalidadEntregaNota">El costo de env&iacute;o se calcular&aacute; seg&uacute;n la modalidad seleccionada.</small>
                            <small class="input-error" id="errorModalidadEntrega"></small>
                        </div>

                        <div class="mp-total-box" id="resumenPagoPedido">
                            <div class="mp-total-copy">
                                <strong>Total a pagar</strong>
                                <small id="resumenPagoDetalle">Seleccione m&eacute;todo de pago y entrega para confirmar el total.</small>
                            </div>
                            <span class="mp-total-selected" id="pedidoTotalModal">Q0.00</span>
                        </div>

                        <div class="mp-form-buttons">
                            <button type="button" class="mp-btn-cancelar" onclick="cerrarModalPedido()">Cancelar</button>
                            <button type="submit" class="mp-btn-enviar">Enviar Pedido</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            /* ===========================
                ESTILOS BASE DEL MODAL
            =========================== */
            .mp-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.7);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 99999;
            }

            .mp-overlay.show {
                display: flex !important;
            }

            .mp-modal {
                width: min(92vw, 760px);
                max-width: 760px;
                background: #fff;
                border-radius: 10px;
                padding: 16px 34px 20px;
                position: relative;
                box-shadow: 0 10px 35px rgba(0,0,0,0.25);
                animation: fadeIn .25s ease-out;
                max-height: 92vh;
                overflow-y: auto;
            }

            @keyframes fadeIn {
                from { transform: scale(.95); opacity: 0; }
                to   { transform: scale(1); opacity: 1; }
            }

            .mp-close {
                position: absolute;
                top: 12px; 
                right: 15px;
                background: none;
                border: none;
                font-size: 26px;
                cursor: pointer;
                color: #333;
                z-index: 10;
            }

            .mp-close:hover {
                color: #dc3545;
            }

            /* ===========================
                MODAL FORMULARIO
            =========================== */
            .mp-modal-form {
                max-width: 760px !important;
            }

            .mp-form-content {
                --mp-field-gap: 18px;
                padding: 0;
            }

            #formPedido {
                display: flex;
                flex-direction: column;
                gap: var(--mp-field-gap);
            }

            .mp-form-content h2 {
                margin-bottom: 5px;
                color: #1D1D1B;
                font-size: 28px;
                line-height: 1.1;
                font-weight: 800;
            }

            .mp-form-subtitle {
                margin-bottom: 14px;
                color: #666;
                font-size: 14px;
            }

            .mp-form-group {
                margin-bottom: 0;
            }

            .mp-form-group,
            .mp-total-box,
            .mp-form-buttons {
                width: min(100%, 650px);
                margin-left: auto;
                margin-right: auto;
                box-sizing: border-box;
            }

            .mp-form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #333;
                font-size: 15px;
            }

            .mp-form-group label input[type="checkbox"] {
                margin-right: 8px;
                width: auto;
            }

            .mp-checkbox-group label {
                margin-bottom: 0;
                padding: 3px 0;
            }

            .mp-checkbox-group {
                margin-bottom: 0;
            }

            #nit-group,
            #numeroDocumento-group,
            .mp-payment-group,
            .mp-delivery-group {
                margin-bottom: 0;
            }

            #numeroDocumento-group,
            .mp-delivery-group,
            .mp-total-box {
                margin-top: 0;
            }

            .required {
                color: #dc3545;
            }

            .mp-form-group .form-control,
            .mp-form-group input:not([type="checkbox"]),
            .mp-form-group select {
                width: 100%;
                height: 41px;
                padding: 8px 14px;
                border: 1px solid #ddd;
                border-radius: 8px;
                font-size: 15px;
                color: #222;
                transition: border-color 0.3s;
            }

            .mp-form-group .form-control:focus,
            .mp-form-group input:not([type="checkbox"]):focus,
            .mp-form-group select:focus {
                outline: none;
                border-color: #1D1D1B;
                box-shadow: 0 0 0 2px rgba(21, 21, 21, 0.10);
            }

            .mp-form-group select.form-control {
                cursor: pointer;

                text-align: center;
                text-align-last: center;

                padding-top: 8px;
                padding-bottom: 8px;

                height: 41px;
            }

            .mp-form-group select.form-control option {
                text-align: center;
            }

            #formPedido .nice-select.form-control {
                float: none;
                display: flex;
                align-items: center;
                width: 100%;
                height: 41px;
                line-height: 1.2;
                margin: 0;
                padding: 8px 38px 8px 14px;
                clear: none;
            }

            #formPedido .nice-select.form-control .current {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                line-height: 1.2;
            }

            #formPedido .nice-select.form-control .list {
                width: 100%;
            }

            #formPedido .nice-select.mp-delivery-select .option.disabled {
                background: #f6f8fb;
                color: #8a92a1;
                cursor: default;
                font-size: 14px;
                font-weight: 800;
            }

            .mp-delivery-note {
                display: block;
                margin-top: 7px;
                color: #5f6d80;
                font-size: 12px;
                line-height: 1.35;
            }

            .mp-delivery-note.is-warning {
                color: #986f1f;
                font-weight: 700;
            }

            .mp-form-buttons {
                display: flex;
                gap: 16px;
                justify-content: flex-end;
                align-items: stretch;
                margin-top: 0;
                padding-top: 14px;
                border-top: 1px solid #eee;
            }

            .mp-btn-cancelar,
            .mp-btn-enviar {
                min-width: 190px;
                min-height: 46px;
                padding: 10px 22px;
                border: 1px solid transparent;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.3s;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                line-height: 1.25;
                box-sizing: border-box;
            }

            .mp-btn-cancelar {
                background: #f5f7ff;
                color: #1D1D1B;
                border-color: #1D1D1B;
            }

            .mp-btn-cancelar:hover {
                background: #e8ecff;
            }

            .mp-btn-enviar {
                background: #1D1D1B;
                color: #fff;
            }

            .mp-btn-enviar:hover {
                background: #1d2aa3;
            }

            .mp-total-box {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 14px;
                min-height: 58px;
                margin-bottom: 0;
                padding: 11px 14px;
                border: 1px solid #c8d3e4;
                border-radius: 10px;
                background: #f7f9fc;
            }

            .mp-total-copy {
                flex: 1 1 auto;
                min-width: 150px;
            }

            .mp-total-box strong {
                display: block;
                color: #222;
                font-size: 16px;
                line-height: 1.2;
            }

            .mp-total-box small {
                display: block;
                margin-top: 3px;
                color: #666;
                font-size: 12px;
                line-height: 1.25;
            }

            .mp-total-box .mp-total-selected {
                display: block;
                color: #132546;
                font-size: 25px;
                font-weight: 800;
                white-space: nowrap;
            }

            .mp-total-box.mp-total-card {
                border-color: #cbd6ff;
                background: #f7f9ff;
            }

            .cart-totals {
                border: 1px solid rgba(111, 142, 196, 0.34);
                border-radius: 0;
                box-shadow: 0 10px 24px rgba(31, 36, 64, 0.04);
            }

            .cart-totals ul {
                margin-bottom: 22px;
            }

            .cart-totals ul li.cart-total-normal {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding: 14px 0;
                border-bottom: 0;
                color: #222;
                font-size: 16px;
                font-weight: 700;
            }

            .cart-totals ul li.cart-total-normal span {
                line-height: 1.25;
            }

            .cart-totals ul li.cart-total-normal strong {
                min-width: 95px;
                text-align: right;
                font-size: 18px;
                font-weight: 800;
                white-space: nowrap;
            }

            .cart-totals ul li.cart-total-normal strong {
                color: #1D1D1B;
            }

            .mp-btn-enviar:disabled {
                background: #ccc;
                cursor: not-allowed;
            }

            @media (max-width: 768px) {
          
                .mp-modal {
                    width: calc(100% - 24px);
                    max-width: 100%;
                    padding: 14px 13px;
                    border-radius: 12px;
                }

                .mp-form-content {
                    --mp-field-gap: 16px;
                    padding: 0;
                }

                .mp-form-group,
                .mp-total-box,
                .mp-form-buttons {
                    width: 100%;
                }

                .mp-form-group {
                    margin-bottom: 0;
                }

                .mp-checkbox-group {
                    margin-bottom: 0;
                }

                #nit-group,
                #numeroDocumento-group,
                .mp-payment-group,
                .mp-delivery-group {
                    margin-bottom: 0;
                }

                #numeroDocumento-group,
                .mp-delivery-group,
                .mp-total-box {
                    margin-top: 0;
                }


                .mp-form-buttons {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    justify-content: space-between;
                    align-items: stretch;
                    margin-top: 0;
                    padding-top: 12px;
                }

                .mp-btn-cancelar,
                .mp-btn-enviar {
                    flex: 1 1 calc(50% - 7px);
                    min-width: 0;
                    min-height: 46px;
                    padding: 10px 12px;
                    font-size: 15px;
                    line-height: 1.2;
                    white-space: normal;
                    word-break: break-word;
                }

                .mp-form-group select.form-control{
                        height: 42px;
                        padding-top: 6px;
                        padding-bottom: 6px;
                        font-size: 14px;
                    }

                    .mp-modal{
                        max-height: 85vh;
                    }

                .mp-total-box {
                    align-items: stretch;
                    flex-direction: column;
                    gap: 10px;
                    margin-bottom: 0;
                }

            }

            .cart-table td:nth-child(2){
                max-width: 220px;
            }

            .cart-table td:nth-child(2) a{
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;

                overflow: hidden;
                text-overflow: ellipsis;
                white-space: normal !important;
            }

            @media (max-width:768px){

                .cart-table td:nth-child(2){
                    max-width: 140px;
                }

            }

            /* ===== ESPACIO ENTRE IMAGEN Y NOMBRE ===== */

            .cart-table table tbody td:nth-child(1){
                padding-right:16px !important;
            }

            .cart-table table tbody td:nth-child(2){
                padding-left:16px !important;
            }

            /* versión celular */

            @media(max-width:768px){

                .cart-table table tbody td:nth-child(1){
                    padding-right:10px !important;
                }

                .cart-table table tbody td:nth-child(2){
                    padding-left:10px !important;
                }

            }

            /* ===== CARRITO RESPONSIVE ===== */
            @media (max-width: 768px) {

                .cart-table table thead th {
                    text-align:center !important;
                    vertical-align:middle !important;

                    padding:16px 10px !important;

                    height:auto !important;
                    line-height:normal !important;

                    white-space:nowrap;
                    font-size:14px;
                }

                .cart-table table tbody tr td.product-thumbnail {
                    padding-right: 12px !important;
                }

                .cart-table table tbody tr td.product-name {
                    padding-left: 12px !important;
                }

                .cart-table table tbody tr td.product-name a {
                    max-width: 140px;
                    line-height: 1.35;
                }
            }

            @media (max-width: 991px) {
                .cart-area {
                    padding-top: 28px !important;
                    padding-bottom: 34px !important;
                }

                .cart-area .row {
                    align-items: flex-start;
                    flex-direction: column;
                    flex-wrap: nowrap;
                    gap: 16px;
                    margin-left: 0;
                    margin-right: 0;
                    overflow-x: visible;
                    padding-bottom: 8px;
                    -webkit-overflow-scrolling: touch;
                }

                .cart-area .col-lg-8.col-md-12 {
                    flex: 0 0 100%;
                    max-width: 100%;
                    min-width: 0;
                    width: 100%;
                    padding-left: 0;
                    padding-right: 0;
                }

                .cart-area .col-lg-4.col-md-12 {
                    flex: 0 0 100%;
                    max-width: 100%;
                    min-width: 0;
                    width: 100%;
                    padding-left: 0;
                    padding-right: 0;
                }

                .cart-table.table-responsive {
                    border: 1px solid #e5e9f2;
                    border-radius: 6px;
                    overflow-x: auto;
                    width: 100%;
                }

                .cart-table table {
                    min-width: 560px;
                }

                .cart-table table thead tr th {
                    font-size: 12px !important;
                    padding: 12px 10px !important;
                }

                .cart-table table tbody tr td {
                    font-size: 12px !important;
                    padding: 16px 8px !important;
                }

                .cart-table table tbody tr td.product-thumbnail a img {
                    width: 58px;
                }

                .cart-table table tbody tr td.product-thumbnail .remove {
                    top: 38px;
                }

                .cart-table table tbody tr td.product-quantity .input-counter {
                    max-width: 98px;
                    min-width: 98px;
                }

                .cart-totals {
                    padding: 18px;
                }

                .cart-totals h3 {
                    font-size: 16px;
                    margin-bottom: 18px;
                    padding-bottom: 10px;
                }

                .cart-totals ul li.cart-total-normal {
                    align-items: flex-start;
                    flex-direction: column;
                    gap: 5px;
                    font-size: 13px;
                    padding: 10px 0;
                }

                .cart-totals ul li.cart-total-normal strong {
                    font-size: 15px;
                    min-width: 0;
                    text-align: left;
                }

                .cart-totals .default-btn {
                    font-size: 12px;
                    min-height: 42px;
                    padding: 11px 12px;
                    white-space: normal;
                }
            }

            @media (max-width: 575px) {
                .cart-area .container {
                    padding-left: 12px;
                    padding-right: 12px;
                }

                .cart-area .row {
                    gap: 12px;
                }

                .cart-area .col-lg-8.col-md-12 {
                    flex-basis: 100%;
                    max-width: 100%;
                    min-width: 0;
                }

                .cart-area .col-lg-4.col-md-12 {
                    flex-basis: 100%;
                    max-width: 100%;
                    min-width: 0;
                }

                .cart-table table {
                    min-width: 520px;
                }
            }

        </style>

<?php
$csrfToken = htmlspecialchars((string) $_SESSION[$csrfKey], ENT_QUOTES, 'UTF-8');

$cartJsPath = __DIR__ . '/assets/js/cart.js';
$cartJsVersion = file_exists($cartJsPath) ? filemtime($cartJsPath) : time();

$extra_footer_scripts = <<<HTML
<script>
    window.CYBERSOURCE_CHECKOUT_URL = "api/cybersource/create_checkout.php";
    window.ORDER_CREATE_QUOTE_URL = "api/orders/create_quote.php";
    window.CYBERSOURCE_CSRF_TOKEN = "{$csrfToken}";
</script>
<script type="text/javascript" src="assets/js/cart.js?v={$cartJsVersion}"></script>
HTML;

include 'footer.php';
?>
