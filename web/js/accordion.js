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
