$(document).ready(function() {
  $.urlParamExists = function (name) {
      var results = new RegExp('[\?&]' + name + '=([^&#]*)')
                        .exec(window.location.search);

      return results !== null;
  }

  $.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null) {
       return null;
    }
    return decodeURI(results[1]) || 0;
  }

  if(window.location.href.search('/Aide/') !== -1 || window.location.href.search('/Utilisateur/moi') !== -1) {
    $("#main_droite").addClass("pa-one-block");
    $("#main_gauche").removeClass("ls-on");
    $("#main_gauche").addClass("ls-off")
  }
});
