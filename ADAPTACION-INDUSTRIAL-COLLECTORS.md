# Adaptación de identidad: Industrial Collectors, S.A.

## Cambios realizados

- Nombre comercial, textos institucionales estáticos, metadatos corporativos y dominio estático de SEO.
- Misión y visión proporcionadas en los respaldos estáticos de Nosotros; historia de respaldo neutral, sin fechas ni antecedentes inventados.
- Correo industrialcollectorsventas@gmail.com, llamadas 3200-1888, WhatsApp 3088-8818, direcciones de las dos sucursales y horarios de lunes a sábado.
- Enlaces existentes de Facebook, Instagram, TikTok y ubicaciones proporcionadas. Se reutilizó el icono social duplicado para Instagram. Contacto contiene dos tarjetas de sucursales con las mismas clases, diseño, imagen de fachada, horarios y canales de atención; cada una usa su dirección y su enlace de Google Maps.
- Información estática de efectivo, tarjeta de crédito con recargo del 5%, transferencia a cuenta y link de pago. No se programó recargo ni se cambió el checkout.
- Tema existente: carbón #1D1D1B, azul #1D71B8, celeste #009FE3, blanco, gris #F3F5F7 y acero #AEB6BF; violeta #312783 reservado como acento secundario. Se mantuvieron tipografías, estructura y breakpoints.
- Logos horizontales suministrados: versión para fondo oscuro en header; principal en carga, favicon, metadatos y ventana de pago. Sin editar archivos de imagen.
- Se actualizaron referencias de la fachada y del respaldo de imagen hacia los archivos de Industrial Collectors ya colocados por el usuario, porque los anteriores estaban retirados al iniciar. No se generaron, descargaron, editaron ni eliminaron imágenes. Las demás referencias gráficas se conservaron.

## Archivos modificados

- Páginas y componentes: head.php, footer.php, index.php, contact.php, nosotros.php, tienda.php, producto.php, cart.php, catalogo.php, derechos.php, gifscard.php, menosde100.php, ofertas.php, metodos_de_pago.php, politicas_de_garantia.php, preguntas_frecuentes.php, terminos.php.
- Tema: assets/css/tjdc-theme.css.
- Textos o referencias de recursos exclusivamente: assets/php/category_visuals.php, assets/php/product_seo.php, assets/php/form-process.php; assets/js/bm-special-products.js, assets/js/carrito.js, assets/js/cart.js, assets/js/index.js, assets/js/main.js, assets/js/product-image.js, assets/js/tienda.js.
- Correcciones de la auditoría: textos y logos de api/cybersource/secure_acceptance_redirect.php y api/cybersource/payment_return.php, textos y logo de integrations/cybersource/src/OrderEmailNotifier.php, y nombre visible del remitente de respaldo en config/cybersource.php. No cambian sus procesos ni transporte de correo.

## Funcionalidad conservada

Sin cambios en lógica de APIs, endpoints, integraciones, consultas, base de datos, datos de productos, categorías, precios, existencias, cálculos, carrito, checkout, autenticación ni validaciones. No se agregaron ni eliminaron funciones de negocio. La tarjeta adicional corresponde a la petición posterior del usuario. En JavaScript únicamente se cambiaron literales corporativos, referencias de los recursos suministrados y la transparencia aplicada al logo. El formulario conserva su procesamiento y configuración mediante variables de entorno; solo se actualizó la identidad y el correo estático de respaldo. La presentación de pantallas y correos de pago sí se adaptó; sus instrucciones de ejecución se conservaron.

## Validación

- Sintaxis PHP de páginas y archivos auxiliares: correcta.
- Sintaxis de los siete archivos JavaScript modificados: correcta.
- Prueba existente tests/cybersource_order_payload_test.php: correcta.
- Contacto, catálogo y portada revisados a 1440, 768 y 390 píxeles: sin desbordamiento horizontal.
- Menú móvil abierto y cerrado correctamente; catálogo cargó 30 productos.
- Sin errores de consola capturados en las páginas revisadas. Contacto sin imágenes rotas; el catálogo contiene un elemento de imagen vacío perteneciente al componente existente.
- No se ejecutaron compras, cobros ni envíos de formularios externos.

## Contenido que debe actualizarse en origen

La API de Nosotros todavía devuelve historia de La Broca (incluido 2012), misión, visión y diferencias comerciales anteriores. Se conservaron exactamente sus datos y renderizado; los textos institucionales nuevos se muestran solo cuando esos campos no llegan desde la API.

La API de portada mantiene títulos como “Ferretería en un solo lugar” y textos sobre herramientas. Las categorías y productos de la API mantienen su catálogo anterior. No se sustituyeron por contenido inventado.

Los banners y la imagen CompraLABROCA.png que no tienen reemplazo suministrado permanecen intactos, incluido cualquier texto o color incrustado en ellos. Robots, sitemaps y reglas de servidor se conservaron durante la adaptación inicial; sus referencias de dominio se actualizaron por la autorización posterior de publicación indicada al final de este informe.

## Auditoría de las dos instrucciones — 8 de octubre de 2026

El trabajo anterior no estaba completo en todos los detalles. La revisión del documento original, la segunda solicitud, el diff y la presentación real identificó y corrigió:

- Términos mostraba el número de llamadas como WhatsApp: ahora indica +502 3088 8818.
- Pantallas de redirección/resultado de pago y correo de pedido mantenían nombre, logo o WhatsApp de La Broca: sus textos y referencias gráficas ahora usan Industrial Collectors.
- El logo de la ventana de pago y de redirección tenía transparencia: se retiró para conservar sus colores.
- Contadores, etiquetas, CTA, paginación y estados de botones mantenían texto carbón sobre el nuevo azul: ahora usan blanco en esos estados. También se corrigieron los estados de los enlaces Ver todo y Ver más de portada.
- Se completaron palabras clave y etiquetas accesibles estáticas secundarias y se corrigió puntuación en los textos de correo.

| Aspecto requerido | Resultado de auditoría |
| --- | --- |
| Identidad y textos estáticos de la empresa | Adaptados; excepciones operacionales documentadas abajo |
| Historia estática sin datos inventados | Cumple |
| Misión y visión proporcionadas | Correctas en respaldo estático; API sigue entregando las anteriores |
| Contactos, teléfonos, correo y horarios sin domingo inventado | Cumple en textos de tienda y mensajes de presentación |
| Direcciones de ambas sucursales | Cumple con las direcciones proporcionadas |
| Dos tarjetas, mismo diseño y misma fachada | Cumple; dos tarjetas y tres botones por tarjeta |
| Google Maps sucursal 1 | Conserva enlace proporcionado |
| Google Maps sucursal 2 | Usa exactamente el enlace proporcionado; ficha externa con nombre diferente requiere confirmar |
| Facebook, Instagram y TikTok | Cumple |
| Pagos informativos y recargo 5% solo como texto | Cumple; cálculos y pasarela intactos |
| Colores, tipografía y logo | Paleta adaptada, familia existente conservada y logo sin efectos |
| Imágenes sin reemplazo | Conservadas; archivos de imagen no editados por esta adaptación |
| Datos dinámicos de APIs | Conservados |
| Funciones, rutas, consultas, pagos, carrito y validaciones | Sin cambios de lógica |
| Escritorio, tablet y móvil | Tarjetas revisadas a 1440, 768 y 390 px; sin desbordamiento del texto o página |
| Sintaxis y prueba existente de pagos | Correctas |

La comparación de 23 archivos PHP modificados contra HEAD, normalizando saltos de línea y excluyendo literales, HTML y comentarios, encontró los mismos tokens de ejecución. Contacto se revisó por separado por la tarjeta y URL estática añadidas. Esta comprobación complementa la revisión del diff y no sustituye una compra real, que no se ejecutó.

### Pendientes conservados deliberadamente

1. Historia, misión, visión, diferencias y portada anteriores proceden de APIs. Deben actualizarse en su sistema de origen; no corresponde sobrescribirlos en esta base.
2. Los botones Waze abren Google Maps: era comportamiento heredado y se replicó en la segunda tarjeta. No se inventó un destino Waze ni se cambiaron servicios. Para corregirlo sin ambigüedad hacen falta los enlaces Waze confirmados de ambas sucursales. La documentación oficial permite enlaces por coordenadas o búsqueda de dirección: https://developers.google.com/waze/deeplinks/.
3. Al abrir el enlace suministrado para sucursal 2 se muestra una ficha llamada “La Recicladora, S.A.”, con dirección Calle Real, Villa Nueva. El enlace se conserva exactamente como fue entregado; se solicitó confirmar si es la ubicación correcta.
4. Las direcciones técnicas de remitente, reply-to y envelope del correo de pedidos, la URL operacional de respaldo de la integración y valores de respaldo del payload de pago mantienen su configuración anterior para no alterar servicios. El nombre visible de remitente de respaldo y los cuerpos/asuntos sí fueron adaptados. Variables de entorno configuradas siguen teniendo precedencia.

Por estos pendientes no se certifica cumplimiento absoluto del resultado visible. Se confirma cumplimiento de la adaptación estática verificada y preservación del comportamiento en el alcance revisado.

## Preparación del dominio principal — 13 de octubre de 2026

El usuario autorizó preparar directamente industrialcollectors.com y pidió mantener las APIs tal como están. Se actualizaron únicamente las referencias del dominio en .htaccess, robots.txt, sitemap.xml, sitemap-products.xml, tools/generate_product_sitemap.php, integrations/cybersource/src/Config.php, config/cybersource.private.example.php, la URL base de config/cybersource.private.php y el título de README.md. No se ejecutó el generador de sitemaps ni se consultaron o modificaron datos externos.

- Dominio definitivo: https://industrialcollectors.com, sin www, con HTTPS.
- Se conserva la excepción de localhost y 127.0.0.1 en las reglas de servidor.
- Las 13 URLs del sitemap principal y las 636 URLs de productos conservaron sus rutas e IDs; solo cambió el host.
- Retorno y cancelación de pagos conservan sus rutas existentes. Se verificó la URL https://industrialcollectors.com/api/cybersource/payment_return.php mediante la configuración local.
- Las credenciales privadas, los valores restantes de configuración y el ambiente test se conservaron; no se activaron cobros de producción.
- Todos los archivos de api/, assets/php/rutas.php y los JavaScript se compararon por hash antes/después de este cambio y quedaron idénticos.
- La sintaxis PHP de los archivos editados y la prueba existente de pagos pasaron. La comprobación por HTTP local no pudo completarse porque Apache no estaba escuchando en el puerto 80; queda validar las redirecciones al iniciar Apache o instalar en Hostinger.

Se creó industrialcollectors.com.zip con index.php directamente en la raíz, imágenes actuales y archivos de ejecución. El ZIP fue comprobado por integridad y sus archivos API coinciden byte por byte con el proyecto. Excluye credenciales privadas, claves, registros, sesiones, cachés, historial Git, herramientas y documentos internos. Las credenciales de pagos y los remitentes de correo deben configurarse en el servidor antes de comprobar transacciones reales.

Este paquete corresponde a public_html del dominio principal. Instalado en el subdominio pruebas, sus reglas canónicas redirigirían al dominio principal. No se subieron archivos a Hostinger, no se cambiaron DNS y no se reemplazó WordPress.

## Cambio de proyecto de API autorizado posteriormente — 13 de octubre de 2026

El usuario confirmó que el segmento brovic de las APIs se cambiaría por induscollesitio. Esta autorización posterior modifica la restricción de mantener la base anterior: se cambió únicamente el proyecto de las rutas API, conservando nombres de endpoints, parámetros, idsucursal, validaciones y lógica de compra.

Se actualizaron assets/php/rutas.php, api/shop_products.php, config/cybersource.php, index.php, nosotros.php, producto.php y assets/js/tienda.js, gifscard.js, ofertas.js y menosde100.js. En tienda.js la base de imágenes compartía la constante de API; se fijó su valor anterior para conservar exactamente https://ssl.sol.sistemasolgt.com/brovic/files/articulos/. Las demás rutas de imágenes también permanecen como estaban.

Una consulta de lectura por POST a https://ssl.sol.sistemasolgt.com/induscollesitio/api/api_tienda_categorias_listar.php obtuvo HTTP 200, JSON válido y success=true, con cero categorías. No se enviaron pedidos ni se llamó a la API de insertar cotizaciones. La carga de datos corresponde al sistema de origen.

Se respaldaron fuera del proyecto 50 archivos JSON de caché temporal de productos de brovic para impedir que se mostraran como respuestas de la nueva API. Se conservaron las sesiones de pagos, el inventario remoto y storage/seo-catalog.json; no se regeneraron sitemaps ni catálogos. La sintaxis PHP/JavaScript y la prueba existente de pagos pasaron. El ZIP del dominio principal fue regenerado y verificado con las nuevas bases API, sin caché ni credenciales privadas.
