document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector("#bookingForm");
    const telefone = document.querySelector("#telefone");

    if (telefone) {

        telefone.addEventListener("input", () => {

            let valor = telefone.value
                .replace(/\D/g, "")
                .slice(0, 11);


            if (valor.length <= 10) {

                valor = valor.replace(
                    /^(\d{2})(\d)/,
                    "($1) $2"
                );

                valor = valor.replace(
                    /(\d{4})(\d)/,
                    "$1-$2"
                );

            } else {

                valor = valor.replace(
                    /^(\d{2})(\d)/,
                    "($1) $2"
                );

                valor = valor.replace(
                    /(\d{5})(\d)/,
                    "$1-$2"
                );

            }
            telefone.value = valor;
        });
    }

    /*evita múltiplos envios*/
    if (form) {

        form.addEventListener("submit", () => {

            const button = form.querySelector(
                ".btn-submit"
            );


            if (!button) {
                return;
            }

            button.disabled = true;

            button.querySelector("span").textContent =
                "Enviando...";

        });

    }

});