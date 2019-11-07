<?php
/** @var Gabarit $this */
?>

<div class="box">
<h2>Connecteur <?php hecho($connecteur_entite_info['type']) ?> - <?php hecho($connecteur_entite_info['id_connecteur'])?> : <?php hecho($connecteur_entite_info['libelle']) ?>
</h2>

<form action='<?php $this->url("Connecteur/doEditionLibelle") ?>' method='post' >
    <?php $this->displayCSRFInput() ?>
    <input type='hidden' name='id_ce' value='<?php echo $connecteur_entite_info['id_ce'] ?>' />
<table class='table table-striped'>

<tr>
<th class='w200'>Libellé de l'instance</th>
<td><input class="form-control col-md-4" type='text' name='libelle' value='<?php hecho($connecteur_entite_info['libelle']) ?>'/>
</td>
</tr>

</table>

    <a class='btn btn-secondary' href='Connecteur/edition?id_ce=<?php echo $connecteur_entite_info['id_ce']?>'>
        <i class="fa fa-times-circle"></i>&nbsp;Annuler
    </a>

    <button type="submit" class="btn btn-primary">
        <i class="fa fa-pencil"></i>&nbsp;Modifier
    </button>
</form>

</div>
