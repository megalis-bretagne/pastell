<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='<?php $this->url("Connecteur/editionModif?id_ce=$id_ce") ?>'><i class='icon-circle-arrow-left'></i>Retour à la définition du connecteur</a>

<div class="box">
<h2>Choix de la nomenclature CDG</h2>

<table class='table table-striped'>
	<?php 
	foreach ($classifCDG as $i => $info) : ?>
		<tr>
			<td class="w30">		
			<a href='Connecteur/doExternalData?id_ce=<?php echo $id_ce?>&field=<?php echo $field ?>&nomemclature_file=<?php hecho($info) ?>'><?php echo $info?></a>
			</td>		
		</tr>
	<?php endforeach;?>
	<tr>
		<td class="w30">		
			<a href='Connecteur/doExternalData?id_ce=<?php echo $id_ce?>&field=<?php echo $field ?>&nomemclature_file='>Supprimer le fichier </a>
		</td>
	</tr>
</table>

</div>