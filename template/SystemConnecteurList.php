<?php
/** @var $all_connecteur_globaux array */
/** @var $all_connecteur_entite array */
?>
<div class="box">
	<table class='table table-striped'>
		<tr>
			<th class="w200">Nom symbolique</th>
			<th class="w200">Libellé</th>
			<th>Description</th>
		</tr>
		<?php foreach($all_connecteur_globaux as $id_connecteur => $connecteur) : ?>
			<tr>
                <td><a href="<?php $this->url("/System/connecteurDetail?id_connecteur=$id_connecteur&scope=global")?>"><?php hecho($id_connecteur); ?></a></td>
				<td><?php hecho($connecteur[ConnecteurDefinitionFiles::NOM]); ?></td>
				<td><?php echo nl2br(htmlentities(isset($connecteur['description'])?$connecteur['description']:''),ENT_QUOTES); ?></td>
				<td>

				</td>
			</tr>
		<?php endforeach;?>
	</table>
</div>


<div class="box">
	<h2>Connecteurs d'entité disponibles sur la plateforme</h2>
	<table class='table table-striped'>
		<tr>
			<th class="w200">Nom symbolique</th>
			<th class="w200">Libellé</th>
			<th>Description</th>
		</tr>
		<?php foreach($all_connecteur_entite as $id_connecteur => $connecteur) : ?>
			<tr>
                <td><a href="<?php $this->url("/System/connecteurDetail?id_connecteur=$id_connecteur&scope=entite")?>"><?php hecho($id_connecteur); ?></a></td>
				<td><?php hecho($connecteur['nom']); ?></td>
                <td><?php echo nl2br(htmlentities(isset($connecteur['description'])?$connecteur['description']:''),ENT_QUOTES); ?></td>
				<td>

				</td>
			</tr>
		<?php endforeach;?>
	</table>
</div>
