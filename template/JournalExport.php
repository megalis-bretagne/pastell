<?php

/**
 * @var Gabarit $this
 * @var array $entite_info
 * @var array $utilisateur_info
 * @var array $document_info
 * @var string $recherche
 *
 */
?>

<div class="box">

<h2>Filtre du journal</h2>

<form action='Journal/doExport' method='post'>
    <?php $this->displayCSRFInput() ?>
    <input type='hidden' name='id_e' value='<?php hecho($id_e)?>'>
    <input type='hidden' name='id_d' value='<?php hecho($id_d)?>'>
    <input type='hidden' name='id_u' value='<?php hecho($id_u)?>'>
    <input type='hidden' name='type' value='<?php hecho($type)?>'>
    <table class='table table-striped'>
        <tr>
            <th class='w200'>Entité</th>
            <td><?php hecho($id_e ? $entite_info['denomination'] : "Toutes")?></td>
        </tr>
        <tr>
            <th>Utilisateur</th>
            <td><?php hecho($id_u ? $utilisateur_info['login'] : "Tous")?></td>
        </tr>
        <tr>
            <th>Dossier</th>
            <td><?php hecho($id_d ? $document_info['titre'] : "Tous")?></td>
        </tr>
        <tr>
            <th>Type</th>
            <td><?php hecho($type ?: "Tous")?></td>
        </tr>
        <tr>
            <th><label for='input_recherche'>Recherche</label> </th>
             <td> <input type='text' name='recherche' id='input_recherche' class="col-md-3 form-control" value='<?php hecho($recherche) ?>' /></td>
        </tr>
        <tr>
            <th><label for='date_debut'>
            Date de début
            </label> </th>
             <td>
                 <div class="input-group">
                     <input type='text' id='date_debut' class="col-md-3 form-control ls-box-input" name='date_debut' value='<?php hecho(date_iso_to_fr($date_debut))?>'/>
                     <div class="input-group-append">
                         <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                     </div>
                 </div>
             </td>
        </tr>
        <tr>
            <th><label for='date_fin'>
            Date de fin
            </label> </th>
             <td>
                 <div class="input-group">
                     <input type='text' id='date_fin' class="col-md-3 form-control ls-box-input" name='date_fin' value='<?php hecho(date_iso_to_fr($date_fin))?>' />
                     <div class="input-group-append">
                         <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                     </div>
                 </div>

             </td>
        </tr>
        <tr>
            <th><label for="en_tete_colonne">Ajouter une ligne d'entête</label> </th>
            <td>
                <input type="checkbox" id="en_tete_colonne" name="en_tete_colonne" checked="checked"/>
            </td>
        </tr>


    </table>
    <a class='btn btn-secondary' href='Journal/index?id_e=<?php hecho($id_e); ?>&id_d=<?php hecho($id_d); ?>&id_u=<?php hecho($id_u); ?>&type=<?php hecho($type); ?>&recherche=<?php hecho($recherche)?>'>
        <i class="fa fa-times-circle"></i>&nbsp;Annuler
    </a>

    <button type='submit' class='btn btn-primary'><i class="fa fa-download"></i>&nbsp;Récupérer le journal</button>

</form>
</div>

<script type="text/javascript">
jQuery.datepicker.setDefaults(jQuery.datepicker.regional['fr']);
$(function() {
    $("#date_debut").datepicker( { dateFormat: 'dd/mm/yy' });
    $("#date_fin").datepicker( { dateFormat: 'dd/mm/yy' });
});
</script>
