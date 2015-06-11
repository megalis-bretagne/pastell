
$(document).ready(function() {
	$(".noautocomplete").attr('autocomplete','off');
	
	$(".send_button").click(function(){
		$(this.form).append("<input type='hidden' name='" + this.name + "' value='true' />");
		$(this.form).append("<p>Sauvegarde en cours ...</p>");
		$(".send_button").attr('disabled', true);
		$(this.form).submit();
	})
	
	$('#select-all').click(function(event) {
		var result = this.checked;
		$(':checkbox').each(function() {
	            this.checked = result;                        
		});
	});
	
	$(".zselect_entite").pastell_zselect('S�lectionnez une entit�');
	$(".zselect_role").pastell_zselect('S�lectionnez un r�le');
	$(".zselect_document").pastell_zselect('S�lectionnez un type de document');
	
});

function split( val ) {
	return val.split( /,\s*/ );
}

function extractLast( term ) {
	return split( term ).pop();
}


(function ( $ ) {
	
$.fn.pastellAutocomplete = function(autocomplete_url,id_e,mail_only) {
	this.autocomplete({
		source: function( request, response ) {
			$.getJSON( autocomplete_url, {
				term: extractLast( request.term ), "id_e": id_e, "mail-only": mail_only
			}, response ); 
		},
		select: function( event, ui ) {
			var terms = split( this.value );
			terms.pop();
			terms.push( ui.item.value );
			terms.push( "" );
			this.value = terms.join( ", " );
			return false;
		} ,
	});
	return this;
}

$.fn.pastell_zselect = function(placeholder_str){
	this.each(function(){
		$(this).zelect({
			placeholder: $('<i>').text(placeholder_str),
			renderItem: function(item, term){
		    	return $('<span>').text(item.label);
			},
   	  		noResults: function(term){
   	  			return $('<span>').addClass('no-results').text("Pas de r�sultat pour " + term + ".")	
   	  		}	
		})
	})
	return this;
}

	
}(jQuery));
