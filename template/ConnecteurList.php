<?php
/** @var Gabarit $this */
?>
<div class="box">
<table style='width:100%;'>
<tr>
<td>
<h2>Listes des connecteurs <?php echo ($id_e?"":" globaux")?></h2>
</td>

<td class='align_right'>
<?php if ($droit_edition) : ?>

<a href="<?php $this->url("Connecteur/new?id_e=$id_e") ?>" class='btn'>
    <i class="fa fa-plus"></i>&nbsp; Créer
</a>
<?php endif;?>
</td>

</tr>
</table>


<table class="table table-striped">
<tr>
			<th>Instance</th>
			<th>Connecteur</th>
			<th>Famille de connecteur</th>
			<th>&nbsp;</th>
		</tr>
<?php foreach($all_connecteur as $i => $connecteur) : ?>
	<tr>
		<td><?php hecho($connecteur['libelle']);?></td>
		<td><?php echo $connecteur['id_connecteur'];?></td>
		<td><?php echo $connecteur['type'];?></td>
		<td>
			<a class='btn' href='<?php $this->url("Connecteur/edition?id_ce={$connecteur['id_ce']}") ?>'>
                <i class="fa fa-pencil"></i>
                Modifier
            </a>
		</td>
	</tr>
<?php endforeach;?>
</table>

</div>