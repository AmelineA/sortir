//slide up alerts after a delay
$(".alert").delay(3000).slideUp();


//recuperer l'input de type file et exécuter une action dès que cet input est actionné
$('input[type="file"]').change(function (event) {
   //recuperer le nom du fichier chargé
   var fileName = event.target.files[0].name;
   //sélectionner le label de l'input pour inserer dans le texte le nom du fichier
   $(".custom-file-label").text(fileName);
});

//pour faire correspondre la largeur des th aux largeurs des td
    let length = $(".table-event").children('thead').children('tr').children('th').length;
function resizeTable() {
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
}

$(document).resize(resizeTable());

// pointer le lieu sur une carte openstreetmap

    //initialiser les coordonnées (Nantes)
    let lat = $('#lat').text();
    let lon =$('#lon').text();
    let maCarte = null;
    console.log(lat, lon);
    //function d'initialisation de la carte
    function initMap() {
        //Créer l'objet "macarte" et l'inserer dans l'élément HTML id="map"
        maCarte = L.map('map').setView([lat, lon], 12) //3ème argument est la taille du zoom par défaut
        //récupérer la carte (tile) via openstreetmap
        L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
            //laisser la source de données
            attribution : 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
            minZoom : 1,
            maxZoom : 20
        }).addTo(maCarte);
        //ajouter le marqueur de coordonnées
        let marker = L.marker([lat,lon]).addTo(maCarte);
        marker.bindPopup(lat,lon); //popup du marqueur

    }

    //initMap() s'execute au chargement du DOM
    window.onload = function () {
        initMap();
    };