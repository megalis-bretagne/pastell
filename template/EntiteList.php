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
<h2>Liste des entités</h2>
</td>
<?php if ($this->RoleUtilisateur->hasDroit($this->Authentification->getId(),"entite:edition",0)) : ?>
<td class='align_right'>
<a href="<?php $this->url("Entite/import"); ?>" class='btn btn-secondary'><i class="fa fa-upload"></i>&nbsp;Importer</a>
    <a class='btn btn-secondary' href='<?php $this->url("Entite/export?id_e={$id_e}&search={$search}"); ?>'><i class='fa fa-download'></i>&nbsp;Exporter au format CSV</a>

    <a href="<?php $this->url("Entite/edition"); ?>"  class='btn btn-primary'><i class="fa fa-plus"></i> Créer</a>

</td>
<?php endif;?>
</tr>
</table>



<form action='<?php $this->url("Entite/detail") ?>' method='get' class="form-inline">
	<input type='text' name='search' id='search' value='<?php echo $search?>' class="form-control col-md-2 mr-2" placeholder="Rechercher par dénomination"/>
	<button type='submit' class='btn btn-primary' id="search-entite"><i class='fa fa-search'></i>&nbsp; Rechercher</button>
</form>


<?php 
$this->SuivantPrecedent($offset,20,$nbCollectivite,"Entite/detail?search=$search");
?>
<table class="table table-striped">
	<tr>
		<th class='w200'>Dénomination</th>
		<th>Siren</th>
		<th>Type</th>
		<th>État</th>
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
			<?php if($info['is_active']) :?>
                <p class="badge badge-info">
                    Activée
                </p>
            <?php else: ?>
			    <p class="badge badge-danger">
                    Désactivée
                </p>
            <?php endif ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>

<a class='btn btn-secondary' href='<?php $this->url("Entite/export?id_e={$id_e}&search={$search}"); ?>'><i class='fa fa-download'></i>&nbsp;Exporter au format CSV</a>
</div>