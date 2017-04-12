<?php
/**
 * @var Gabarit $this
 * @var ConnecteurFrequence $connecteurFrequence
 */
?>
<a class='btn btn-mini' href='<?php $this->url("Daemon/config") ?>'>
	<i class='icon-circle-arrow-left'></i>
	Retour à la liste des fréquences</a>

<div class="box">
	<h2>Edition d'une fréquence</h2>
	<form action='<?php $this->url("Daemon/doEditFrequence") ?>' method='post' >
		<?php $this->displayCSRFInput() ?>
		<input type="hidden" name="id_cf" value="<?php echo $connecteurFrequence->id_cf?>"/>
		<table class='table table-striped'>
			<tr>
				<th class='w200'>
                    <label for="type_connecteur">Type de connecteur</label>
                </th>
				<td >
                    <select name="type_connecteur" id="type_connecteur" class="w300">
                        <option value="">Tous les types</option>
                        <option value="<?php echo ConnecteurFrequence::TYPE_GLOBAL ?>">Connecteurs globaux</option>
                        <option value="<?php echo ConnecteurFrequence::TYPE_ENTITE ?>">Connecteurs d'entité</option>
                    </select>
				</td>
			</tr>
            <tr id="tr_famille_connecteur" class="hide">
                <th class='w200'>
                    <label for="famille_connecteur">Famille de connecteur</label>
                </th>
                <td>
                    <select name="famille_connecteur" id="famille_connecteur" class="w300">
                    </select>
                </td>
            </tr>
			<tr id="tr_id_connecteur" class="hide">
				<th class='w200'>
					<label for="id_connecteur">Connecteur</label>
				</th>
				<td>
					<select name="id_connecteur" id="id_connecteur" class="w300">
					</select>
				</td>
			</tr>

			<tr id="tr_action_type" class="hide">
				<th class='w200'>
					<label for="action_type">Type d'action</label>
				</th>
				<td>
					<select name="action_type" id="action_type" class="w300">
						<option value="">Tous les types</option>
						<option value="<?php echo ConnecteurFrequence::TYPE_ACTION_CONNECTEUR ?>">Actions de connecteur</option>
						<option value="<?php echo ConnecteurFrequence::TYPE_ACTION_DOCUMENT ?>">Actions de document</option>
					</select>
				</td>
			</tr>

			<tr id="tr_type_document" class="hide">
				<th class='w200'>
					<label for="type_document">Type de document</label>
				</th>
				<td>
					<select name="type_document" id="type_document" class="w300">
					</select>
				</td>
			</tr>
			<tr id="tr_action" class="hide">
				<th class='w200'>
					<label for="action">Action</label>
				</th>
				<td>
					<select name="action" id="action" class="w300">
					</select>
				</td>
			</tr>
			<tr id="tr_expression" class="">
				<th class='w200'>
					<label for="expression">Expression</label>
				</th>
				<td>
					<textarea name="expression" id="expression" class="w500" rows="10"><?php hecho($connecteurFrequence->expression)?></textarea>
				</td>
			</tr>
			<tr id="tr_id_verrou" class="">
				<th class='w200'>
					<label for="id_verrou">Verrou</label>
				</th>
				<td>
					<input name="id_verrou" id="id_verrou" class="w500" value="<?php hecho($connecteurFrequence->id_verrou)?>"/>
				</td>
			</tr>

		</table>
		<input type="submit" value="Éditer" class="btn" />
	</form>

</div>

<script type="text/javascript">
$(document).ready(function() {

	var type_connecteur = $("#type_connecteur");

	var action_type = $("#action_type");

	type_connecteur.change(function(){

	   $("#tr_id_connecteur").hide();
		$("#tr_action_type").hide();
		$("#tr_action").hide();
       var type_connecteur = $("#type_connecteur").val();

	   if (type_connecteur == ''){
		   $("#tr_famille_connecteur").hide();
		   return;
	   }

	   var url = "Daemon/listFamilleAjax?global=" + getGlobalType();

	   addArrayToSelect(
	   		url,
		   "#famille_connecteur",
		   "Toutes les familles de connecteur",
		   "#tr_famille_connecteur",
		   function(){
			   $("#famille_connecteur").val("<?php echo $connecteurFrequence->famille_connecteur ?>").change();
		   }
	   );
   });

	$("#famille_connecteur").change(function(){
		var famille_connecteur = $("#famille_connecteur");

		if (famille_connecteur.val() == null){
			return famille_connecteur.val('').change();
		}
		if (famille_connecteur.val() == ''){
			$("#tr_id_connecteur").hide();
			return;
		}

		var url = "Daemon/listConnecteurAjax?famille_connecteur="+famille_connecteur.val()+"&global=" + getGlobalType();
		addArrayToSelect(
			url,
			"#id_connecteur",
			"Tous les connecteurs",
			"#tr_id_connecteur",
			function(){
				$("#tr_action_type").show();
				$("#id_connecteur").val("<?php echo $connecteurFrequence->id_connecteur ?>").change();
			}
		);
	});

	$("#id_connecteur").change(function(){
		var id_connecteur = $("#id_connecteur");
		if (id_connecteur.val() == null){
			return id_connecteur.val('').change();
		}
		if (id_connecteur.val() == ''){
			$("#tr_action").hide();
			action_type.val("").change()
		} else {
			action_type.val("<?php echo $connecteurFrequence->action_type ?>").change()
		}
	});

	action_type.change(function(){
		var famille_connecteur = $("#famille_connecteur");
		var id_connecteur = $("#id_connecteur");
		var action_type = $("#action_type");
		if (action_type.val() == null ){
			return action_type.val('').change();
		}
		$("#tr_type_document").hide();
		$("#tr_action").hide();
		if (action_type.val() == ''){

			//$("#tr_type_document").hide();
		}
		var url;
		if (action_type.val() == 'connecteur'){

			url = "Daemon/listActionAjax?famille_connecteur="+famille_connecteur.val()+"&id_connecteur="+id_connecteur.val()+"&global=" + getGlobalType();
			addArrayToSelect(
				url,
				"#action",
				"Toutes les actions",
				"#tr_action",
				function(){
					$("#action").val("<?php echo $connecteurFrequence->action ?>").change();
				}
			);
		}
		if (action_type.val() == 'document'){
			url = "Daemon/listFluxAjax";
			addArrayToSelect(
				url,
				"#type_document",
				"Tous les types de documents",
				"#tr_type_document",
				function(){
					$("#type_document").val("<?php echo $connecteurFrequence->type_document ?>").change();
				}
			);
		}
	});

	var type_document = $("#type_document");

	type_document.change(function(){

		var type_document = $("#type_document");

		if (type_document.val() == null){
			return type_document.val('').change();
		}
		if (type_document.val() == ''){
			return $("#tr_action").hide();
		}
		var famille_connecteur = $("#famille_connecteur");

		url = "Daemon/listFluxActionAjax?type_document=" + type_document.val() + "&famille_connecteur=" + famille_connecteur.val();
		console.log(url);
		addArrayToSelect(
			url,
			"#action",
			"Toutes les actions",
			"#tr_action",
			function(){
				$("#action").val("<?php echo $connecteurFrequence->action ?>").change();
			}
		);
	});



	var getGlobalType = function(){
		return ($("#type_connecteur").val() == 'global')?"1":"0";
	};

	var addArrayToSelect = function(url,select_jquery_selector,default_option,next_to_show,after_function = function(){}){
		$.get(url,function(data){
			$(select_jquery_selector).html("").append($("<option>",{
				value: "",
				text: default_option
			}));

			$.each($.parseJSON(data),function(index,value){
				$(select_jquery_selector).append($("<option>",{
					value: value,
					text: value
				}));
			});

			$(next_to_show).show();
			after_function();
		});
	};

	type_connecteur.val("<?php echo $connecteurFrequence->type_connecteur ?>").change();
});
</script>