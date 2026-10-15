<?php

if (!function_exists('bm_normalize_visual_key')) {
    function bm_normalize_visual_key($value)
    {
        $value = strtolower(trim((string) $value));
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($converted !== false && $converted !== '') {
            $value = $converted;
        }

        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim((string) $value, '-');
    }
}

if (!function_exists('bm_title_case')) {
    function bm_title_case($value)
    {
        return ucwords(strtolower(trim((string) $value)));
    }
}

if (!function_exists('bm_category_icon_class')) {
    function bm_category_icon_class($categoryName)
    {
        $key = bm_normalize_visual_key((string) $categoryName);

        $specificMap = [
            'todos-los-productos' => 'bx bx-grid-alt',
            'construccion' => 'bx bx-building-house',
            'plomeria' => 'bx bx-water',
            'electricidad' => 'bx bx-bulb',
            'pintura' => 'bx bx-brush',
            'herramientas' => 'bx bx-wrench',
            'ferreteria' => 'bx bx-cog',
        ];

        if (isset($specificMap[$key])) {
            return $specificMap[$key];
        }

        $keywordMap = [
            'construccion' => 'bx bx-building-house',
            'cemento' => 'bx bx-building-house',
            'plomeria' => 'bx bx-water',
            'tuberia' => 'bx bx-water',
            'electricidad' => 'bx bx-bulb',
            'electrico' => 'bx bx-bulb',
            'pintura' => 'bx bx-brush',
            'brocha' => 'bx bx-brush',
            'herramienta' => 'bx bx-wrench',
            'herramientas' => 'bx bx-wrench',
            'tornillo' => 'bx bx-cog',
            'ferreteria' => 'bx bx-cog',
            'envio' => 'bx bxs-truck',
            'envios' => 'bx bxs-truck',
            'hogar' => 'bx bx-home-alt',
            'profesional' => 'bx bx-briefcase-alt-2',
            'oferta' => 'bx bx-purchase-tag-alt',
            'best-seller' => 'bx bx-star',
            'bestseller' => 'bx bx-star',
        ];

        foreach ($keywordMap as $needle => $icon) {
            if (strpos($key, $needle) !== false) {
                return $icon;
            }
        }

        return 'bx bx-category-alt';
    }
}

if (!function_exists('bm_asset_path_if_exists')) {
    function bm_asset_path_if_exists($relativePath)
    {
        $relativePath = ltrim((string) $relativePath, '/\\');
        $absolutePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);

        return file_exists($absolutePath) ? str_replace('\\', '/', $relativePath) : '';
    }
}

if (!function_exists('bm_category_visual_descriptions')) {
    function bm_category_visual_descriptions()
    {
        return [
            'varios' => 'Explora productos seleccionados para el hogar, el taller y proyectos de construcción.',
            'construccion' => 'Materiales y soluciones para avanzar tus proyectos con calidad y confianza.',
            'plomeria' => 'Productos de plomería para mantenimiento, instalación y reparaciones.',
            'electricidad' => 'Opciones de electricidad para instalaciones, mantenimiento y mejoras.',
            'pintura' => 'Pinturas, accesorios y soluciones para renovar tus espacios.',
            'herramientas' => 'Herramientas para hogares, profesionales y proyectos exigentes.',
            'ferreteria' => 'Productos de ferretería general para resolver necesidades del día a día.',
            'best-sellers' => 'Productos destacados y solicitados por nuestros clientes.',
            'profesional' => 'Materiales y herramientas seleccionadas para trabajos profesionales.',
            'combos' => 'Selecciones de productos complementarios para tus proyectos.',
            'set-regalo' => 'Opciones seleccionadas para obsequiar productos útiles y prácticos.',
        ];
    }
}

if (!function_exists('bm_category_visual_description_for_slug')) {
    function bm_category_visual_description_for_slug($slug)
    {
        $descriptions = bm_category_visual_descriptions();
        $slug = bm_normalize_visual_key($slug);

        return $descriptions[$slug] ?? '';
    }
}

if (!function_exists('bm_category_visual')) {
    function bm_category_visual($category)
    {
        $id = (string) ($category['idcategoria'] ?? '');
        $name = trim((string) ($category['nombre'] ?? ''));
        $description = trim((string) ($category['descripcion'] ?? ''));
        $slug = bm_normalize_visual_key($name);
        $descriptions = bm_category_visual_descriptions();

        $imageKeys = array_unique(array_filter([
            $slug,
            str_replace('-', '_', $slug),
            $id !== '' ? 'categoria-' . $id : '',
        ]));

        $image = '';
        foreach ($imageKeys as $imageKey) {
            foreach (['assets/img/Categorias', 'assets/img/categorias', 'assets/img'] as $folder) {
                foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
                    $candidate = bm_asset_path_if_exists($folder . '/' . $imageKey . '.' . $extension);
                    if ($candidate !== '') {
                        $image = $candidate;
                        break 3;
                    }
                }
            }
        }

        if ($image === '') {
            $image = bm_asset_path_if_exists('assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png');
        }

        if ($image === '') {
            $image = bm_asset_path_if_exists('assets/img/404.png');
        }

        return [
            'id' => $id,
            'name' => $name,
            'title' => bm_title_case($name),
            'description' => $descriptions[$slug] ?? ($description !== '' ? bm_title_case($description) : 'Explora productos seleccionados para el hogar, el taller y tus proyectos.'),
            'image' => $image,
            'slug' => $slug,
        ];
    }
}

if (!function_exists('bm_build_category_visuals')) {
    function bm_build_category_visuals($categories)
    {
        $items = [];

        foreach ((array) $categories as $category) {
            if (isset($category['condicion']) && (string) $category['condicion'] !== '1') {
                continue;
            }

            $visual = bm_category_visual($category);
            if ($visual['id'] === '' || $visual['name'] === '') {
                continue;
            }

            $items[] = $visual;
        }

        return $items;
    }
}

if (!function_exists('bm_find_category_visual_by_id')) {
    function bm_find_category_visual_by_id($categories, $categoryId)
    {
        $categoryId = (string) $categoryId;

        foreach ((array) $categories as $category) {
            if ((string) ($category['idcategoria'] ?? '') === $categoryId) {
                return bm_category_visual($category);
            }
        }

        return null;
    }
}
