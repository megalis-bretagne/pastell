<?php
/** @var Gabarit $this */
/** @var array $type_de_dossier_info */
/** @var array $etape_info */

?>

<div class="box" style="min-height: 500px;">
	<h2>Configuration de l'étape du cheminement</h2>

	<form action='<?php $this->url("TypeDossier/doEditionEtape"); ?>' method='post' >
		<?php $this->displayCSRFInput() ?>
		<input type='hidden' name='id_t' value='<?php hecho($type_de_dossier_info['id_t'])?>' />
		<input type='hidden' name='num_etape' value='<?php hecho($etape_info['num_etape'])?>' />
		<table class='table table-striped'>
			<tr>
				<th class="w400">
					<label for="type" >Nature de l'étape<span class="obl">*</span></label>
				</th>
				<td>

					<select class="form-control col-md-4" name='type' id="type">
						<?php foreach(TypeDossierDefinition::getAllTypeEtape() as $type => $libelle_type) : ?>
							<option value="<?php echo $type ?>" <?php echo $type==$etape_info['type']?'selected="selected"':''; ?>><?php hecho($libelle_type) ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th class="w400">
					<label for="requis" >Étape obligatoire</label>
				</th>
				<td>
					<input name='requis' id='requis' class="" type="checkbox" <?php echo $etape_info['requis']?"checked='checked'":""?>/>
				</td>
			</tr>

        </table>

        <table class='table table-striped table-specific' id="table-signature">
            <tr>
                <th class="w400">
                    <label for="choix_type_parapheur" >Choix de la typologie parapheur<span class="obl">*</span></label>
                </th>
                <td>
                    <select class="form-control col-md-4" name='choix_type_parapheur' id="choix_type_parapheur">
						<?php foreach(['connecteur'=>'Typologie fixé dans le connecteur','mixte'=>'Type fixé dans le connecteur, sous type choisi dans le dossier','dossier'=>'Typologie choisie dans le dossier'] as $element_id => $element_libelle) : ?>
                            <option value="<?php echo $element_id ?>" <?php echo $element_id==$etape_info['choix_type_parapheur']?'selected="selected"':''; ?>><?php hecho($element_libelle) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th class="w400">
                    <label for="document_a_signer" >Document à envoyé à la signature<span class="obl">*</span></label>
                </th>
                <td>
                    <select class="form-control col-md-4" name='document_a_signer' id="document_a_signer">
                        <?php foreach($file_field_list as $element_id => $element_info) : ?>
                            <option value="<?php echo $element_id ?>" <?php echo $element_id==$etape_info['document_a_signer']?'selected="selected"':''; ?>><?php hecho($element_info['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th class="w400">
                    <label for="document_a_signer" >Annexe(s) à envoyée(s) à la signature<span class="obl">*</span></label>
                </th>
                <td>
                    <select class="form-control col-md-4" name='annexe' id="annexe">
						<?php foreach($multi_file_field_list as $element_id => $element_info) : ?>
                            <option value="<?php echo $element_id ?>" <?php echo $element_id==$etape_info['annexe']?'selected="selected"':''; ?>><?php hecho($element_info['name']) ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>

		<a class='btn btn-secondary' href='<?php $this->url("TypeDossier/detail?id_t={$type_de_dossier_info['id_t']}")?>'>
			<i class="fa fa-times-circle"></i>&nbsp;Annuler
		</a>
		<button type="submit" class="btn btn-primary">
			<i class="fa fa-floppy-o"></i>&nbsp;Enregistrer
		</button>

	</form>
</div>
<script>
    $(document).ready(function(){
        var selected = $("#type");

        $(".table-specific").hide();
        $("#table-" + selected.val()).show();

        selected.change(function(){
            $(".table-specific").hide();
            $("#table-" + $(this).val()).show();
        });
    });
</script>