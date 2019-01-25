// Pour que l'accordéon se replie après la recherche

$(document).ready(function() {
  $.urlParamExists = function (name) {
      var results = new RegExp('[\?&]' + name + '=([^&#]*)')
                        .exec(window.location.search);

      return results !== null;
  }

    if($.urlParamExists("search")) {
      $("#headingOne").addClass("collapsed");
      $("#collapseOne").removeClass("show");
    }


});

// Pour afficher un titre après la recherche

$(document).ready(function() {
  $.urlParamExists = function (name) {
      var results = new RegExp('[\?&]' + name + '=([^&#]*)')
                        .exec(window.location.search);

      return results !== null;
  }

    if($.urlParamExists("search")) {
      $("#title-result").addClass("title-result-on");
    }

});
