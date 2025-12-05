// Les variables locales
let NOM_BTN = 'motdepasseoublier';

function click(nomBTN = 'motdepasseoublier') {
    let btn = $('#' + nomBTN);
    btn.on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: "Infos",
            text: "Veuillez contacter l'administrateur de l'application web pour la réinitialisation de votre mot de passe.",
            icon: "info",
            timer: 10000
        });
    });
}

click();
