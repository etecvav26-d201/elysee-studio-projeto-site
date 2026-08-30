document.addEventListener("DOMContentLoaded", () => {

    /*
     * Exibe as observações do agendamento.
     */

    const notesButtons = document.querySelectorAll(
        ".notes-toggle"
    );


    notesButtons.forEach((button) => {

        button.addEventListener("click", () => {

            const notes = button.dataset.notes || "";


            if (!notes) {
                return;
            }


            window.alert(
                "Observações do agendamento:\n\n" + notes
            );

        });

    });

});