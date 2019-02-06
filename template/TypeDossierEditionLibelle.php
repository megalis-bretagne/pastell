<?php
/** @var Gabarit $this */
/** @var array $flux_info */
?>

<div class="box" style="min-height: 500px;">

	<form action='<?php $this->url("TypeDossier/doEditionLibelle"); ?>' method='post' >
		<?php $this->displayCSRFInput() ?>
		<input type='hidden' name='id_t' value='<?php hecho($type_de_dossier_info['id_t'])?>' />
		<table class='table table-striped'>
			<tr>
				<th class="w400">
					<label for="id_type_dossier" >Identifiant</label>
				</th>
				<td>
					<b><?php hecho($type_de_dossier_info['id_type_dossier'])?></b>
				</td>
			</tr>
			<tr>
				<th class="w400">
						<label for="nom" >Libellé</label>
				</th>
				<td>
					<input class="form-control col-md-4" type='text' name='nom' id="nom" value='<?php hecho($type_dossier_definition['nom'])?>' />
				</td>
			</tr>
			<tr>
				<th class="w400">
					<label for="type" >Libellé du classement</label>
				</th>
				<td>
					<input class="form-control col-md-4"  type='text' name='type' id="type" value='<?php hecho($type_dossier_definition['type'])?>' />
				</td>
			</tr>
			<tr>
				<th class="w400">
					<label for="description" >Description</label>
				</th>
				<td>
					<textarea style="  height: 150px;" class="form-control col-md-4" name="description" id="description"><?php echo get_hecho($type_dossier_definition['description'])?></textarea>

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