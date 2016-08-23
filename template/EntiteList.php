<?php
/** @var $offset */
/** @var $search */
/** @var $nbCollectivite */
/** @var array $liste_collectivite */
/** @var array $id_e */
?>
<div class="box">
<table style='width:100%;'>
<tr>
<td>
<h2>Liste des collectivités</h2>
</td>
<?php if ($this->RoleUtilisateur->hasDroit($this->Authentification->getId(),"entite:edition",0)) : ?>
<td class='align_right'>
<a href="<?php $this->url("Entite/import"); ?>" class='btn'>Importer</a>
<a href="<?php $this->url("Entite/edition"); ?>"  class='btn'>Nouveau</a>
</td>
<?php endif;?>
</tr>
</table>



<form action='<?php $this->url("Entite/detail") ?>' method='get' class="form-inline">
	<input type='text' name='search' id='search' value='<?php echo $search?>'/>
	<button type='submit' class='btn'><i class='icon-search'></i>
		<label for="search">Rechercher</label></button>
</form>


<?php 
$this->SuivantPrecedent($offset,20,$nbCollectivite,"Entite/detail?search=$search");
?>
<table class="table table-striped">
	<tr>
		<th class='w200'>Dénomination</th>
		<th>Siren</th>
		<th>Type</th>
		<th>Active</th>
	</tr>
<?php foreach($liste_collectivite as $i => $info) : ?>
	<tr>
		<td><a href='<?php $this->url("Entite/detail?id_e={$info['id_e']}") ?>'><?php hecho($info['denomination']) ?></a></td>
		<td><?php 
		echo $info['siren'] ?></td>
		<td>
			<?php echo Entite::getNom($info['type']) ?>
		</td>
		<td>
			<?php if(! $info['is_active']) :?>
			<b>Désactivé</b>
			<?php endif;?>
		</td>
	</tr>
<?php endforeach; ?>
</table>

<a class='btn btn-mini' href='<?php $this->url("Entite/export?id_e={$id_e}&search={$search}"); ?>'><i class='icon-file'></i>Exporter (CSV)</a>
</div>