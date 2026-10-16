# Industrial Collectors, S.A.

Tienda PHP para consultar materiales metálicos y enviar solicitudes de cotización. El dominio principal es https://industrialcollectors.com.

## Flujo de cotización

1. El cliente selecciona productos del catálogo y completa sus datos en el carrito.
2. La solicitud se registra en el sistema SOL mediante `api/orders/create_quote.php`.
3. El sitio prepara un mensaje dirigido a WhatsApp **+502 3088-8818**. El cliente revisa y confirma su envío desde WhatsApp; no se usa una API de mensajería automática.
4. El equipo de Industrial Collectors confirma disponibilidad, importes y retiro en tienda.

Las opciones son **transferencia bancaria**, **efectivo al recoger en tienda** y **tarjeta al recoger en tienda**. Todas las cotizaciones son exclusivamente para recoger en sucursal, también las pagadas por transferencia; el sitio no ofrece envíos. La tarjeta se usa presencialmente en la tienda. El sitio no captura datos de tarjeta ni realiza cobros en línea. No se anuncian descuentos ni recargos por este flujo.

## Integración y configuración

- El catálogo conserva sus APIs e imágenes actuales.
- La recepción de cotizaciones usa `integrations/quotes` y `config/quotes.php`.
- Mantener nombres de endpoints, parámetros y datos de productos del sistema SOL.
- No incluir credenciales, archivos privados, registros ni historial de solicitudes en paquetes públicos.

## Actualización del servidor

Subir un ZIP nuevo no elimina los archivos de una instalación anterior. Al migrar al flujo de cotización, retirar del servidor los directorios obsoletos `api/cybersource/` e `integrations/cybersource/` y la configuración pública antigua `config/cybersource.php` y `config/cybersource.private.example.php`, si existen.

Actualizar `cart.php`, `assets/js/cart.js`, `assets/js/carrito.js`, las cuatro páginas informativas modificadas y `api/orders/create_quote.php`. Añadir `api/orders/_bootstrap.php`, `integrations/quotes/` y `config/quotes.php`. Los archivos van directamente dentro de `public_html`, sin una carpeta adicional del dominio. El servidor necesita PHP con cURL y sesiones, y permiso de escritura en `logs/`.

Si el servidor tiene `config/cybersource.private.php`, migrar únicamente los ajustes pertinentes de sitio, cotizaciones y correo a `config/quotes.private.php`; no copiar credenciales de la pasarela. Después retirar la configuración antigua del proyecto. El archivo privado nuevo es opcional: `config/quotes.php` incluye los valores predeterminados de Industrial. No sobrescribir ajustes del servidor con valores locales.

Antes de retirar archivos, verificar las rutas exactas de esa instalación. Archivar cualquier historial anterior de `storage/cybersource/` y `logs/cybersource.log` fuera de la raíz pública y del paquete de despliegue; no borrar `storage/` ni `logs/` completos. La copia local ya tiene ese historial archivado fuera del proyecto. Conservar la carpeta `.private` de Hostinger sin cambios y no utilizarla como archivo propio.

No publicar `.git/`, `.agents/`, `tests/`, `tools/`, documentación, ejemplos de configuración ni respaldos. No hace falta subir `config/quotes.private.example.php`.

Comprobar la sintaxis PHP, la consulta del catálogo y el flujo de cotización antes de publicar. Las pruebas que registren cotizaciones o envíen mensajes deben hacerse únicamente con autorización y datos aprobados; no se realizan cobros de prueba.
