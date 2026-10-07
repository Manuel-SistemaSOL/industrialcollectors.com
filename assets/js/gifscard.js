document.addEventListener("DOMContentLoaded", function () {
    if (typeof window.bmLoadSpecialProducts !== "function") {
        return;
    }

    window.bmLoadSpecialProducts({
        apiUrl: "https://ssl.sol.sistemasolgt.com/brovic/api/api_tienda_gifcard.php",
        tagText: "Gift Card",
        errorLabel: "Gift Card"
    });
});
