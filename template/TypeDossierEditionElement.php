<?php
/** @var Gabarit $this */
/** @var array $type_de_dossier_info */
/** @var array $element_info */

?>

<div class="box" style="min-height: 500px;">
	<h2>Configuration de l'élement du formulaire</h2>

	<form action='<?php $this->url("TypeDossier/doEditionElement"); ?>' method='post' >
		<?php $this->displayCSRFInput() ?>
		<input type='hidden' name='id_t' value='<?php hecho($type_de_dossier_info['id_t'])?>' />
		<input type='hidden' name='orig_element_id' value='<?php hecho($element_info['element_id'])?>' />
		<table class='table table-striped'>
			<tr>
				<th class="w400">
					<label for="element_id" >Identifiant de l'élément<span class="obl">*</span></label>
				</th>
				<td>
					<input class="form-control col-md-4" type='text' name='element_id' id="element_id" value='<?php hecho($element_info['element_id'])?>' />
				</td>
			</tr>
			<tr>
				<th class="w400">
					<label for="name" >Libellé</label>
				</th>
				<td>
					<input class="form-control col-md-4" type='text' name='name' id="name" value='<?php hecho($element_info['name'])?>' />
				</td>
			</tr>
			<tr>
				<th class="w400">
					<label for="type" >Type d'élément</label>
				</th>
				<td>
					<select id="type" name="type" class="form-control col-md-4">
						<?php foreach(TypeDossierDefinition::getAllTypeElement() as $type => $type_libelle) : ?>
							<option value="<?php echo $type; ?>" <?php echo $type==$element_info['type']?'selected="selected"':''; ?>><?php echo $type_libelle; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th class="w400">
					<label for="commentaire" >Commentaire</label>
				</th>
				<td>
					<textarea style="  height: 150px;" class="form-control col-md-4" name="commentaire" id="commentaire"><?php echo get_hecho($element_info['commentaire'])?></textarea>
				</td>
			</tr>
			<tr>
				<th class="w400">
					<label for="requis" >Champs obligatoire</label>
				</th>
				<td>
					<input name='requis' id='requis' class="" type="checkbox" <?php echo $element_info['requis']?"checked='checked'":""?>/>
				</td>
			</tr>
			<tr>
				<th class="w400">
					<label for="champs-affiches">Affiché dans une colonne de la liste des dossiers</label>
				</th>
				<td>
					<input name='champs-affiches' id='champs-affiches' class="" type="checkbox" <?php echo $element_info['champs-affiches']?"checked='checked'":""?>/>
				</td>
			</tr>
			<tr>
				<th class="w400">
					<label for="champs-recherche-avancee">Affiché dans la recherche avancée</label>
				</th>
				<td>
					<input name='champs-recherche-avancee' id='champs-recherche-avancee' class="" type="checkbox" <?php echo $element_info['champs-recherche-avancee']?"checked='checked'":""?>/>
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