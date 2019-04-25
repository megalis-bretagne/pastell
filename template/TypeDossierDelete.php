<?php
/** @var Gabarit $this */
/** @var array $type_de_dossier_info */
?>

<div class="box" style="min-height: 500px;">

	<div class="alert-danger alert">
		Attention ! Vous êtes sur le point de supprimer définitivement le flux <b><?php hecho($type_de_dossier_info['id_type_dossier']) ?></b>
	</div>

	<form action='<?php $this->url("/TypeDossier/doDelete"); ?>' method='post' >
		<?php $this->displayCSRFInput() ?>
		<input type='hidden' name='id_t' value='<?php hecho($type_de_dossier_info['id_t'])?>' />

		<a class='btn btn-secondary' href='<?php $this->url("TypeDossier/list")?>'>
			<i class="fa fa-times-circle"></i>&nbsp;Annuler
		</a>
		<button type="submit" class="btn btn-danger">
			<i class="fa fa-trash"></i>&nbsp;Supprimer
		</button>

	</form>
</div>