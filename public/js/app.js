//slide up alerts after a delay
$(".alert").delay(5000).slideUp();


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
        // console.log("owidthTh = " + parseInt(owidthTh.width()) + " widthTd = " +widthTd );

        if (owidthTh.width()>widthTd){
            let widthTh = parseInt($(".table-event").children('thead').children('tr').children('th:nth-child('+i+')').width());
            let owidthTd = $(".table-event").children('tbody').children('tr:first').children('td:nth-child('+i+')').width(widthTh);

            // console.log("2 widthTh = " + widthTh + " widthTd = " +parseInt(owidthTd.width()));
        }
    }
}

$(document).resize(resizeTable());


    //methode pour récupérer les coordonnées à partir de l' addresse  insérée par l'utilisateur avec nominatim(OSM)

    $('#coordonnates').click(function () {

        //récupérer l'adresse dans le dom :
            let street = $('#location_street').val();
            let zipcode = $('#location_zipCode').val();
            let city = $('#location_city').val();
            console.log(street+" "+zipcode+" "+city);
        //préparer la request pour l'api OSM type  https://nominatim.openstreetmap.org/search?q=135+pilkington+avenue,+birmingham&format=xml&polygon=1&addressdetails=1
            let address = street+" "+zipcode+" "+city; //attention au format de la string mettre des espaces
        //envoyer la request GET en Ajax et récuperer les coordonnés dans les var "lat" et "lon"
            let lat;
            let lon;
        if (address !== "") {
            $.ajax({
                url: "https://nominatim.openstreetmap.org/search", //url de Nominatim
                type: 'get',
                data: "q="+address+"&format=json&addressdetails=1&limit=1&polygon_svg=1"
                // Données envoyées (q : adresse complète, format : format attendu pour la réponse, limit : nombre de réponses attendu,
                // polygon_svg : fournit les données de polygone de la réponse en svg)
            }).done(function (response) {//resupérer la response et extraire les données de l'objet Json
               console.log(response.length);
                if (response.length>0) {
                    lat = response[0]['lat'];
                    lon = response[0]['lon'];
                    $('#location_latitude').val(lat);
                    $('#location_longitude').val(lon);
                }else{
                    //affiche message d'erreur
                    alert('pas de coordonnées trouvées');
                }
            }).fail(function (error) {
                alert(error);
            });
        }
    });

    //methode pour l'affichage de la map dans display-event

        //récupérer l'adresse dans le dom :
        let street = $('#street').text();
        let zipcode = $('#zipcode').text();
        let city = $('#city').text();
        let address = street+" "+zipcode+" "+city;
        let lat;
        let lon;
        if (address !== "") {
            $.ajax({
                url: "https://nominatim.openstreetmap.org/search",
                type: 'get',
                data: "q="+address+"&format=json&addressdetails=1&limit=1&polygon_svg=1"
            }).done(function (response) {
                console.log(response);
                if (response !== "") {
                    lat = response[0]['lat'];
                    lon = response[0]['lon'];
                    //set lat et lon dans le twig display-event
                    $('#lat').val(lat);
                    $('#lon').val(lon);
                    initMap();
                }
            }).fail(function (error) {
                // alert(error);
            });
        }

    let maCarte = null;
    //function d'initialisation de la carte
    function initMap() {
        //Créer l'objet "macarte" et l'inserer dans l'élément HTML id="map"
        maCarte = L.map('map').setView([lat, lon], 12) //3ème argument est la taille du zoom par défaut
        //récupérer la carte (tile) via openstreetmap
        L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
            //laisser la source de données
            attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
            minZoom: 1,
            maxZoom: 20
        }).addTo(maCarte);
        //ajouter le marqueur de coordonnées
        let marker = L.marker([lat, lon]).addTo(maCarte);
        marker.bindPopup(lat, lon); //popup du marqueur
    }



//convertir les boutons de la navbar du header en dropdown
var heightAdmin = $("#dropAdmin").height();
var heightUser = $("#dropUser").height();

var dropUser = $("#dropUser .dropcontent");
var dropAdmin = $("#dropAdmin .dropcontent");


$("#dropAdmin").click(function () {
    if (dropUser.toggleClass('active', true)){
        dropUser.toggleClass('active');
        $("#dropUser").removeClass('dropdown-active');
    }
    dropAdmin.toggleClass('active');
    $(this).toggleClass('dropdown-active');
    $(this).height('auto');
    $("#dropUser").height(heightUser);
});

$("#dropUser").click(function () {
    if (dropAdmin.toggleClass('active', true)){
        dropAdmin.toggleClass('active');
        $("#dropAdmin").removeClass('dropdown-active');
    }
    dropUser.toggleClass('active');
    $(this).toggleClass('dropdown-active');
    $(this).height('auto');
    $("#dropAdmin").height(heightAdmin);
});
