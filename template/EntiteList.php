<?php
/** @var $offset */
/** @var $search */
/** @var $nbCollectivite */
/** @var array $liste_collectivite */
/** @var array $id_e */
?>
<div class="box">
<div style='width:100%;'>

<h2>Liste des entités</h2>

</div>
<?php if ($this->RoleUtilisateur->hasDroit($this->Authentification->getId(),"entite:edition",0)) : ?>

    <a href="<?php $this->url("Entite/edition"); ?>"  class='btn btn-primary grow'><i class="fa fa-plus"></i> Créer</a>
    <a class='btn btn-secondary' href='<?php $this->url("Entite/export?id_e={$id_e}&search={$search}"); ?>'><i class='fa fa-download'></i>&nbsp;Exporter au format CSV</a>
    <a href="<?php $this->url("Entite/import"); ?>" class='btn btn-secondary'><i class="fa fa-upload"></i>&nbsp;Importer</a>

    <?php endif;?>

    <form action='<?php $this->url("Entite/detail") ?>' method='get' class="form-inline form-search">
      <input type='text' name='search' id='search' value='<?php echo $search?>' class="form-control input-search col-md-2" placeholder="Rechercher par dénomination"/>
      <button type='submit' class='btn btn-primary btn-search' id="search-entite"><i class='fa fa-search'></i></button>
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
