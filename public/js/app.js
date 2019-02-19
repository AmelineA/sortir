//slide up alerts after a delay
$(".alert").delay(3000).slideUp();


//recuperer l'input de type file et exécuter une action dès que cet input est actionné
$('input[type="file"]').change(function (event) {
   //recuperer le nom du fichier chargé
   var fileName = event.target.files[0].name;
   //sélectionner le label de l'input pour inserer dans le texte le nom du fichier
   $(".custom-file-label").text(fileName);
});

