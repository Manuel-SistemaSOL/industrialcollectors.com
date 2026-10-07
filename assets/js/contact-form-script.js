/*==============================================================*/
// Contact Form JS
/*==============================================================*/
(function ($) {
    "use strict";

    var contactForm = $("#contactForm");

    if (!contactForm.length || typeof contactForm.validator !== "function") {
        return;
    }

    contactForm.validator().on("submit", function (event) {
        if (event.isDefaultPrevented()) {
            formError();
            submitMSG(false, "Por favor complete todos los campos requeridos correctamente.");
            return;
        }

        event.preventDefault();
        submitForm();
    });

    function submitForm() {
        var form = contactForm;
        var submitButton = form.find("button[type='submit']");
        var originalButtonText = submitButton.text();

        submitButton.prop("disabled", true).addClass("disabled").text("Enviando...");
        submitMSG(true, "Enviando tu mensaje...");

        $.ajax({
            type: "POST",
            url: form.attr("action") || "assets/php/form-process.php",
            data: form.serialize(),
            dataType: "json",
            success: function (response) {
                if (response === "success" || (response && response.success)) {
                    formSuccess(response && response.message ? response.message : "Mensaje enviado correctamente.");
                    return;
                }

                formError();
                submitMSG(false, response && response.message ? response.message : "No fue posible enviar el mensaje. Intente nuevamente.");
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};

                formError();
                submitMSG(false, response.message || xhr.responseText || "No fue posible enviar el mensaje. Intente nuevamente.");
            },
            complete: function () {
                submitButton.prop("disabled", false).removeClass("disabled").text(originalButtonText);
            }
        });
    }

    function formSuccess(message) {
        contactForm[0].reset();
        submitMSG(true, message || "Mensaje enviado correctamente.");

        if (typeof Swal !== "undefined") {
            Swal.fire({
                icon: "success",
                title: "Mensaje recibido",
                text: message || "Tu mensaje ha sido enviado correctamente. Nos pondremos en contacto contigo pronto.",
                confirmButtonText: "Aceptar"
            });
        }
    }

    function formError() {
        contactForm.addClass("shake animated").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function () {
            $(this).removeClass("shake animated");
        });
    }

    function submitMSG(valid, msg) {
        var msgClasses = valid ? "h4 tada animated text-success" : "h4 text-danger";
        $("#msgSubmit").removeClass().addClass(msgClasses).text(msg);
    }
}(jQuery));
