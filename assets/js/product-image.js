(function (window) {
    "use strict";

    const FALLBACK_IMAGE = "assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png";
    const DEFAULT_IMAGE_BASE = "https://ssl.sol.sistemasolgt.com/brovic/files/articulos/";
    const INVALID_IMAGE_VALUES = new Set([
        "404",
        "404.png",
        "default",
        "default.jpg",
        "default.png",
        "0",
        "0.jpg",
        "0.png",
        "null",
        "null.jpg",
        "null.png",
        "undefined",
        "undefined.jpg",
        "undefined.png",
        "false",
        "nan",
        "n/a",
        "no-image",
        "no-image.jpg",
        "no-image.png",
        "no aplica",
        "no foto",
        "no-foto",
        "no-foto.jpg",
        "no-foto.png",
        "no_foto",
        "no_foto.jpg",
        "no_foto.png",
        "no-picture",
        "no-picture.jpg",
        "no-picture.png",
        "placeholder",
        "placeholder-image",
        "image-placeholder",
        "sin imagen",
        "sin-imagen",
        "sin-imagen.jpg",
        "sin-imagen.png",
        "sin_imagen",
        "sin_imagen.jpg",
        "sin_imagen.png",
        "nofoto",
        "nofoto.jpg",
        "nofoto.png"
    ]);

    window.BM_PRODUCT_IMAGE_FALLBACK = window.BM_PRODUCT_IMAGE_FALLBACK || FALLBACK_IMAGE;
    window.BM_PRODUCT_IMAGE_BASE_URL = window.BM_PRODUCT_IMAGE_BASE_URL || DEFAULT_IMAGE_BASE;

    function cleanImageValue(value) {
        return String(value ?? "").trim();
    }

    function normalizeImageValueForCheck(value) {
        return cleanImageValue(value)
            .replace(/^["']+|["']+$/g, "")
            .replace(/\\/g, "/")
            .split(/[?#]/)[0]
            .trim()
            .toLowerCase();
    }

    function getImageFileName(value) {
        const comparable = normalizeImageValueForCheck(value).replace(/\/+$/g, "");
        const parts = comparable.split("/");
        return parts[parts.length - 1] || comparable;
    }

    function isInvalidProductImage(value) {
        if (typeof value === 'boolean' || (value !== null && typeof value === 'object')) {
            return true;
        }
        const raw = cleanImageValue(value);

        if (!raw) {
            return true;
        }

        const comparable = normalizeImageValueForCheck(raw);
        const fileName = getImageFileName(raw);
        const fileNameWithoutExtension = fileName.replace(/\.(?:avif|gif|jpe?g|png|svg|webp)$/i, "");

        return raw.includes("..")
            || !comparable
            || comparable.endsWith("/")
            || INVALID_IMAGE_VALUES.has(comparable)
            || INVALID_IMAGE_VALUES.has(fileName)
            || INVALID_IMAGE_VALUES.has(fileNameWithoutExtension);
    }

    function resolveProductImage(value, baseUrl) {
        const raw = cleanImageValue(value).replace(/^["']+|["']+$/g, '');

        if (isInvalidProductImage(value)) {
            return window.BM_PRODUCT_IMAGE_FALLBACK;
        }

        if (/^(?:data:image\/|blob:)/i.test(raw)) {
            return raw;
        }

        if (/^\/\//.test(raw)) {
            return "https:" + raw;
        }

        if (/^https?:\/\//i.test(raw)) {
            try {
                return new URL(raw).href;
            } catch (error) {
                return window.BM_PRODUCT_IMAGE_FALLBACK;
            }
        }

        const normalizedPath = raw.replace(/\\/g, "/");

        if (/^(?:\.\/)?assets\//i.test(normalizedPath) || normalizedPath.startsWith("/assets/")) {
            return normalizedPath;
        }

        if (/^[a-z][a-z0-9+.-]*:/i.test(normalizedPath)) {
            return window.BM_PRODUCT_IMAGE_FALLBACK;
        }

        const normalizedBase = baseUrl || window.BM_PRODUCT_IMAGE_BASE_URL || DEFAULT_IMAGE_BASE;
        let imageBase = normalizedBase.replace(/\/?$/, '/');
        const relativePath = normalizedPath.replace(/^\/+/, '');
        if (/^[^/]+\/files\//i.test(relativePath)) {
            imageBase = new URL(imageBase).origin + '/';
        } else if (/^files\//i.test(relativePath)) {
            const filesPosition = imageBase.indexOf('/files/');
            if (filesPosition !== -1) imageBase = imageBase.slice(0, filesPosition + 1);
        }
        const suffixIndex = relativePath.search(/[?#]/);
        const path = suffixIndex === -1 ? relativePath : relativePath.slice(0, suffixIndex);
        const suffix = suffixIndex === -1 ? '' : relativePath.slice(suffixIndex);
        const encodedPath = path.split('/').filter(Boolean).map(function (segment) {
            try { return encodeURIComponent(decodeURIComponent(segment)); }
            catch (error) { return encodeURIComponent(segment); }
        }).join('/');
        return imageBase + encodedPath + suffix;
    }

    function resolveFirstProductImage(values, baseUrl) {
        const imageValues = Array.isArray(values) ? values : Array.prototype.slice.call(arguments);
        const imageBase = Array.isArray(values) ? baseUrl : undefined;

        for (let index = 0; index < imageValues.length; index += 1) {
            const value = imageValues[index];

            if (!isInvalidProductImage(value)) {
                const source = resolveProductImage(value, imageBase);
                if (!isProductImageFallbackUrl(source)) return source;
            }
        }

        return window.BM_PRODUCT_IMAGE_FALLBACK;
    }

    function applyProductImageFallback(image) {
        if (!image) {
            return;
        }

        const alternate = image.getAttribute('data-bm-api-alternate-source');
        if (alternate) {
            image.removeAttribute('data-bm-api-alternate-source');
            image.src = alternate;
            return;
        }

        const source = image.getAttribute('src') || '';
        if (!isInvalidProductImage(source) && !isProductImageFallbackUrl(source)) {
            image.setAttribute('data-bm-api-source', source);
        }
        if (!image.hasAttribute('data-bm-api-image')) image.setAttribute('data-bm-api-image', 'true');
        image.classList.add("bm-product-image-fallback");
        if (!isProductImageFallbackUrl(source)) image.src = window.BM_PRODUCT_IMAGE_FALLBACK;
    }

    function isProductImageFallbackUrl(value) {
        return getImageFileName(value) === getImageFileName(window.BM_PRODUCT_IMAGE_FALLBACK);
    }

    function setProductImage(image, value, baseUrl) {
        if (!image) return;
        const source = resolveProductImage(value, baseUrl);
        image.setAttribute('data-bm-api-image', 'true');
        image.setAttribute('data-bm-api-source', isProductImageFallbackUrl(source) ? '' : source);
        image.classList.toggle('bm-product-image-fallback', isProductImageFallbackUrl(source));
        image.src = source;
    }

    function getProductImageSource(image) {
        if (!image) return '';
        const source = image.getAttribute('src') || '';
        return isProductImageFallbackUrl(source)
            ? (image.getAttribute('data-bm-api-source') || source)
            : source;
    }

    // Only API images and product image containers use this fallback. Local
    // logos, branch photos and other static assets keep their own references.
    const productContainers = '.arrivals-products-image, .shop-products-image, '
        + '.bestsellers-products-image, .special-products-image, .hot-products-image, '
        + '.offer-products-image, .products-details-image, #slider-for, #slider-nav, '
        + '#modalProducto .mp-left, .cart-table .product-thumbnail';

    function isManagedImage(image) {
        if (!image || image.tagName !== 'IMG') return false;
        if (image.hasAttribute('data-bm-api-image') || image.classList.contains('bm-product-image-fallback')
            || image.closest(productContainers)) return true;
        try {
            const source = new URL(image.getAttribute('src') || '', window.location.href);
            return source.origin === new URL(window.BM_PRODUCT_IMAGE_BASE_URL).origin;
        } catch (error) { return false; }
    }

    function inspectImage(image) {
        if (!isManagedImage(image)) return;
        const source = image.getAttribute('src') || '';
        if (isInvalidProductImage(source)) {
            applyProductImageFallback(image);
        } else if (isProductImageFallbackUrl(source)) {
            image.classList.add('bm-product-image-fallback');
        } else if (image.complete) {
            if (image.naturalWidth > 0) image.classList.remove('bm-product-image-fallback');
            else applyProductImageFallback(image);
        }
    }

    function inspectImagesWithin(node) {
        if (!node || node.nodeType !== 1) return;
        if (node.tagName === 'IMG') inspectImage(node);
        node.querySelectorAll('img').forEach(inspectImage);
    }

    window.BM_isInvalidProductImage = isInvalidProductImage;
    window.BM_resolveProductImage = resolveProductImage;
    window.BM_resolveFirstProductImage = resolveFirstProductImage;
    window.BM_applyProductImageFallback = applyProductImageFallback;
    window.BM_isProductImageFallbackUrl = isProductImageFallbackUrl;
    window.BM_setProductImage = setProductImage;
    window.BM_getProductImageSource = getProductImageSource;

    const document = window.document;
    if (document) {
        document.addEventListener('error', function (event) {
            if (isManagedImage(event.target)) applyProductImageFallback(event.target);
        }, true);
        document.addEventListener('load', function (event) {
            if (isManagedImage(event.target)) inspectImage(event.target);
        }, true);
        if (document.documentElement) {
            document.querySelectorAll('img').forEach(inspectImage);
            new window.MutationObserver(function (records) {
                records.forEach(function (record) {
                    if (record.type === 'attributes') inspectImage(record.target);
                    else record.addedNodes.forEach(inspectImagesWithin);
                });
            }).observe(document.documentElement, {
                childList: true, subtree: true, attributes: true,
                attributeFilter: ['src', 'data-bm-api-image']
            });
        }
    }
})(window);
