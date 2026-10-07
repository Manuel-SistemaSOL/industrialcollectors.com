document.addEventListener("DOMContentLoaded", function () {
    if (typeof window.bmLoadSpecialProducts !== "function") {
        return;
    }

    window.bmLoadSpecialProducts({
        apiUrl: "https://ssl.sol.sistemasolgt.com/brovic/api/api_tienda_menosde100.php",
        tagText: "Menos de Q100",
        errorLabel: "productos por menos de Q100",
        emptyMessage: "No hay productos con precio menor o igual a Q100 para mostrar.",
        maxDisplayPrice: 100
    });
});
