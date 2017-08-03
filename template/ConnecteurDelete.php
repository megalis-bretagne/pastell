<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='Connecteur/edition?id_ce=<?php echo $connecteur_entite_info['id_ce']?>'>
	<i class='icon-circle-arrow-left'></i>Retour à la définition du connecteur
</a>

<div class="box">
<h2>Connecteur <?php hecho($connecteur_entite_info['type']) ?> - <?php hecho($connecteur_entite_info['id_connecteur'])?> : <?php hecho($connecteur_entite_info['libelle']) ?> 
</h2>

<div class='alert'>
Attention, la suppression du connecteur est irréversible !
</div>

<form action='<?php $this->url("Connecteur/doDelete") ?>' method='post' >
	<?php $this->getCSRFToken()->displayFormInput(); ?>
	<input type='hidden' name='id_ce' value='<?php echo $connecteur_entite_info['id_ce'] ?>' />
	<input type='submit' class='btn btn-danger' value='Supprimer le connecteur'/>
</form>

</div>

