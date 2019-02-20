//slide up alerts after a delay
$(".alert").delay(3000).slideUp();


//recuperer l'input de type file et exécuter une action dès que cet input est actionné
$('input[type="file"]').change(function (event) {
   //recuperer le nom du fichier chargé
   var fileName = event.target.files[0].name;
   //sélectionner le label de l'input pour inserer dans le texte le nom du fichier
   $(".custom-file-label").text(fileName);
});


//dropdown when button click
var drop = $('.dropdown');
var dropcontent = $('.dropcontent');

drop.click(function () {
   var dropcontent = $(this).children(".dropcontent");
   console.log($(this).children(".dropcontent"));
   dropcontent.addClass('active');
   $(this).addClass('active');
   $(this).css('backgroundColor', '#005066');
   $(this).click(function () {
      $(this).removeClass('active');
      dropcontent.removeClass('active');
   })
});

drop.click(function () {
   d();
});

function d() {
   console.log("click1");
   drop.click(function () {
      h();
   })
}

function h() {
   console.log('click2');
   drop.click(function () {
      d();
   })
}