//slide up alerts after a delay
$(".alert").delay(3000).slideUp();


//recuperer l'input de type file et exécuter une action dès que cet input est actionné
$('input[type="file"]').change(function (event) {
   //recuperer le nom du fichier chargé
   var fileName = event.target.files[0].name;
   //sélectionner le label de l'input pour inserer dans le texte le nom du fichier
   $(".custom-file-label").text(fileName);
});

//pour
let length = $(".table-event").children('thead').children('tr').children('th').length;
console.log(length);
for (let i=1; i<=length;i++){
    let widthTd = parseInt($(".table-event").children('tbody').children('tr:first').children('td:nth-child('+i+')').width());

    let owidthTh = $(".table-event").children('thead').children('tr').children('th:nth-child('+i+')').width(widthTd);
   console.log("owidthTh = " + parseInt(owidthTh.width()) + " widthTd = " +widthTd );

   if (owidthTh.width()>widthTd){
      let widthTh = parseInt($(".table-event").children('thead').children('tr').children('th:nth-child('+i+')').width());
      let owidthTd = $(".table-event").children('tbody').children('tr:first').children('td:nth-child('+i+')').width(widthTh);

      console.log("2 widthTh = " + widthTh + " widthTd = " +parseInt(owidthTd.width()));
   }
}

