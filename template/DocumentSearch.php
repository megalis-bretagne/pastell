<?php
/**
 * @var Gabarit $this
 */
?>

    <a class='btn btn-link' href='Document/list?id_e=<?php echo $id_e?>&type=<?php echo $type?>'><i class="fa fa-arrow-left"></i>&nbsp;Retour à la liste des dossiers </a>


	<div class="accordion" id="accordionExample">
	  <div class="card">
	    <!-- <div class="card-header" id="headingOne">
	      <h2 class="mb-0">
	        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
	         <i class="fa fa-search"></i> Recherche avancée <i class="fa fa-plus-square-o"></i>
	        </button>
	      </h2>
	    </div> -->
	        <a id="headingOne" class="card-header ls-accordion" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
	         <i class="fa fa-search"></i> Recherche avancée <i class="fa fa-plus-square plier"></i><i class="fa fa-minus-square deplier"></i>
				 </a>

	    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
	      <div class="card-body">
	        <div>


<form action='Document/search' method='get' >
	<input type='hidden' name='go' value='go' />
	<input type='hidden' name='date_in_fr' value='true' />

	<?php  $this->RechercheAvanceFormulaireHTML->display(); ?>

    <a class='btn btn-secondary' href='<?php $this->url("Document/list?id_e=$id_e&type=$type"); ?>'>
        <i class="fa fa-times-circle"></i>
        Annuler
    </a>

	<a class='btn btn-secondary' href='Document/search?id_e=<?php echo $id_e?>&type=<?php echo $type?>'>
        <i class="fa fa-undo"></i>&nbsp;
        Réinitialiser
    </a>

    <button type="submit" class="btn btn-primary">
        <i class="fa fa-search"></i>&nbsp;Rechercher
    </button>
</form>
</div>
</div>
</div>
</div>
</div>

    <script type="text/javascript">
        var type = $('select[name="type"]');
        $(type.get(0)).on('change', function () {
            var selectedType = $(this).val();
            var fields = ['lastetat', 'etatTransit'];
            for(var i = 0; i < fields.length; ++i) {
                var field = $('[name=' + fields[i] + ']');
                var optionGroups = field.find('optgroup');
                var optionGroupOfSelectedType = field.find('[label="' + selectedType + '"]');
                optionGroups.hide();
                optionGroupOfSelectedType.show();
            }
        }).trigger('change');
    </script>

<?php

$url = "id_e=$id_e&search=$search&type=$type&lastetat=$lastEtat&last_state_begin=$last_state_begin_iso&last_state_end=$last_state_end_iso&etatTransit=$etatTransit&state_begin=$state_begin_iso&state_end=$state_end_iso&tri=$tri&sens_tri=$sens_tri&date_in_fr=true&";

if ($type){
	foreach($indexedFieldValue as $indexName => $indexValue){
		$url.="&".urlencode($indexName)."=".urlencode($indexValue);
	}
}


if ($go = 'go'){

	$listDocument = $documentActionEntite->getListBySearch($id_e,$type,$offset,$limit,$search,$lastEtat,$last_state_begin_iso,$last_state_end_iso,$tri,$allDroitEntite,$etatTransit,$state_begin_iso,$state_end_iso);
	$count = $documentActionEntite->getNbDocumentBySearch($id_e,$type,$search,$lastEtat,$last_state_begin_iso,$last_state_end_iso,$allDroitEntite,$etatTransit,$state_begin_iso,$state_end_iso,$indexedFieldValue);
	if ($count) {
		$this->SuivantPrecedent($offset,$limit,$count,"Document/search?$url");
		$this->url = $url;
		$this->render("DocumentListBox");


		?>


        <a href="Document/traitementLot?<?php echo $url ?>" class="btn btn-primary">
            <i class='fa fa-cogs'></i>&nbsp;Traitement par lot
        </a>

			<a class='btn btn-primary' href='Document/export?<?php echo $url?>'><i class='fa fa-download'></i>&nbsp;Exporter</a>
		<?php
	} else {
		?>
		<div class="alert alert-info">
			Les critères de recherches ne correspondent à aucun document
		</div>
		<?php
	}
}
