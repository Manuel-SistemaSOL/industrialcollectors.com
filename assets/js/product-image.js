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
        const raw = cleanImageValue(value);

        if (isInvalidProductImage(raw)) {
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

        if (/^files\//i.test(normalizedPath)) {
            return encodeURI("https://ssl.sol.sistemasolgt.com/brovic/" + normalizedPath.replace(/^[/\\]+/, ""));
        }

        const normalizedBase = baseUrl || window.BM_PRODUCT_IMAGE_BASE_URL || DEFAULT_IMAGE_BASE;
        return encodeURI(normalizedBase + normalizedPath.replace(/^[/\\]+/, ""));
    }

    function resolveFirstProductImage(values, baseUrl) {
        const imageValues = Array.isArray(values) ? values : Array.prototype.slice.call(arguments);
        const imageBase = Array.isArray(values) ? baseUrl : undefined;

        for (let index = 0; index < imageValues.length; index += 1) {
            const value = imageValues[index];

            if (!isInvalidProductImage(value)) {
                return resolveProductImage(value, imageBase);
            }
        }

        return window.BM_PRODUCT_IMAGE_FALLBACK;
    }

    function applyProductImageFallback(image) {
        if (!image) {
            return;
        }

        image.onerror = null;
        image.classList.add("bm-product-image-fallback");
        image.src = window.BM_PRODUCT_IMAGE_FALLBACK;
    }

    function isProductImageFallbackUrl(value) {
        return getImageFileName(value) === getImageFileName(window.BM_PRODUCT_IMAGE_FALLBACK);
    }

    window.BM_isInvalidProductImage = isInvalidProductImage;
    window.BM_resolveProductImage = resolveProductImage;
    window.BM_resolveFirstProductImage = resolveFirstProductImage;
    window.BM_applyProductImageFallback = applyProductImageFallback;
    window.BM_isProductImageFallbackUrl = isProductImageFallbackUrl;
})(window);
