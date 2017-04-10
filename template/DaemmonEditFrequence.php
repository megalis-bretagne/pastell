<?php
/**
 * @var Gabarit $this
 * @var array $connecteur_frequence_info
 */
?>
<a class='btn btn-mini' href='<?php $this->url("Daemon/config") ?>'>
	<i class='icon-circle-arrow-left'></i>
	Retour à la liste des fréquences</a>


<div class="box">
	<h2>Création d'une nouvelle fréquence</h2>
	<form action='<?php $this->url("Daemon/doEditFrequence") ?>' method='post' >
		<?php $this->displayCSRFInput() ?>
		<table class='table table-striped'>
			<tr>
				<th class='w200'>
                    <label for="type_connecteur">Type de connecteur</label>
                </th>
				<td >
                    <select name="type_connecteur" id="type_connecteur" class="w300">
                        <option value="">Tous les types</option>
                        <option value="global">Connecteurs globaux</option>
                        <option value="entite">Connecteurs d'entité</option>
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
		</table>
		<input type="submit" value="Créer" class="btn" />
	</form>

</div>

<script type="text/javascript">
$(document).ready(function() {


   $("#type_connecteur").change(function(){

	   $("#tr_id_connecteur").hide();
       var type_connecteur = $("#type_connecteur").val();

	   if (type_connecteur == ''){
		   $("#tr_famille_connecteur").hide();
		   return;
	   }

	   var url = "Daemon/listFamilleAjax?global=" + getGlobalType();

	   addArrayToSelect(url,"#famille_connecteur","Toutes les familles de connecteur","#tr_famille_connecteur");
   });

	$("#famille_connecteur").change(function(){

		var famille_connecteur = $("#famille_connecteur").val();

		if (famille_connecteur == ''){
			$("#tr_id_connecteur").hide();
			return;
		}

		var url = "Daemon/listConnecteurAjax?famille_connecteur="+famille_connecteur+"&global=" + getGlobalType();
		addArrayToSelect(url,"#id_connecteur","Tous les connecteurs","#tr_id_connecteur");

	});

	var getGlobalType = function(){
		return ($("#type_connecteur").val() == 'global')?"1":"0";
	};

	var addArrayToSelect = function(url,select_jquery_selector,default_option,next_to_show){
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
		});
	};

	<?php if ($connecteur_frequence_info): ?>
	TODO : Il faut renseigner après le bon type...

		$("#type_connecteur").val("<?php echo $connecteur_frequence_info['type_connecteur'] ?>").change();
	<?php endif; ?>

});
</script>