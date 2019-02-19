<?php
/** @var Gabarit $this */
/** @var array $type_de_dossier_info */
/** @var TypeDossierEtape $etapeInfo */

?>

<div class="box" style="min-height: 500px;">
	<h2>Ajout d'une étape dans le cheminement</h2>

	<form action='<?php $this->url("TypeDossier/doNewEtape"); ?>' method='post' >
		<?php $this->displayCSRFInput() ?>
		<input type='hidden' name='id_t' value='<?php hecho($type_de_dossier_info['id_t'])?>' />
		<table class='table table-striped'>
			<tr>
				<th class="w400">
					<label for="type" >Nature de l'étape<span class="obl">*</span></label>
				</th>
				<td>

					<select class="form-control col-md-4" name='type' id="type">
						<?php foreach(TypeDossierDefinition::getAllTypeEtape() as $type => $libelle_type) : ?>
							<option value="<?php echo $type ?>" <?php echo $type==$etapeInfo->type?'selected="selected"':''; ?>><?php hecho($libelle_type) ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th class="w400">
					<label for="requis" >Étape obligatoire</label>
				</th>
				<td>
					<input name='requis' id='requis' class="" type="checkbox" <?php echo $etapeInfo->requis?"checked='checked'":""?>/>
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
