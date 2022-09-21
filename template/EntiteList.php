<?php

/**
 * @var Gabarit $this
 */
/** @var $offset */
/** @var $search */
/** @var $nbCollectivite */
/** @var array $liste_collectivite */
/** @var array $id_e */
/** @var bool $cdg_feature */

$search = hecho($search);
?>
<div class="box">
<div style='width:100%;'>

<h2>Liste des entités</h2>

</div>
<?php if ($this->RoleUtilisateur->hasDroit($this->Authentification->getId(), "entite:edition", 0)) : ?>
    <a href="<?php $this->url("Entite/edition"); ?>"  class='btn btn-primary grow'><i class="fa fa-plus-circle"></i> Ajouter</a>
    <a class='btn btn-outline-primary' href='<?php $this->url("Entite/export?id_e={$id_e}&search={$search}"); ?>'><i class='fa fa-download'></i>&nbsp;Exporter (fichier CSV)</a>
    <a href="<?php $this->url("Entite/import"); ?>" class='btn btn-outline-primary'><i class="fa fa-upload"></i>&nbsp;Importer (fichier CSV)</a>

<?php endif;?>

    <form action='<?php $this->url("Entite/detail") ?>' method='get' class="form-inline form-search">
      <input type='text' name='search' id='search' value='<?php echo $search?>' class="form-control input-search col-md-3" placeholder="Rechercher par dénomination"/>
      <button type='submit' class='btn btn-primary btn-search' id="search-entite"><i class='fa fa-search'></i></button>
    </form>


<?php
$this->SuivantPrecedent($offset, 20, $nbCollectivite, "Entite/detail?search=$search");
?>
<h3 id="title-result"class="ls-off title-result">Résultat(s) de la recherche</h3>
<table class="table table-striped">
    <tr>
        <th class='w200'>Dénomination</th>
        <th>Siren</th>
        <?php if ($cdg_feature) : ?>
            <th>Type</th>
        <?php endif; ?>
        <th>État</th>
    </tr>
<?php foreach ($liste_collectivite as $i => $info) : ?>
    <tr>
        <td><a href='<?php $this->url("Entite/detail?id_e={$info['id_e']}") ?>'><?php hecho($info['denomination']) ?></a></td>
        <td><?php
        echo $info['siren'] ?></td>
        <?php if ($cdg_feature) : ?>
        <td>
            <?php echo EntiteSQL::getNom($info['type']) ?>
        </td>
        <?php endif; ?>
        <td>
            <?php if ($info['is_active']) :?>
                <p class="badge badge-info">
                    Activée
                </p>
            <?php else : ?>
                <p class="badge badge-danger">
                    Désactivée
                </p>
            <?php endif ?>
        </td>
    </tr>
<?php endforeach; ?>
</table>

<a class='btn btn-outline-primary' href='<?php $this->url("Entite/export?id_e={$id_e}&search={$search}"); ?>'><i class='fa fa-download'></i>&nbsp;Exporter (fichier CSV)</a>
</div>
