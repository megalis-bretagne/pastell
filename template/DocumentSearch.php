<?php 

function dateInput($name,$value=''){
	?>
	<input 	type='text' 	
		id='<?php echo $name?>' 
		name='<?php echo $name?>' 
		value='<?php echo $value?>' 
		class='date'
		/>
	<script type="text/javascript">
   		 jQuery.datepicker.setDefaults(jQuery.datepicker.regional['fr']);
		$(function() {
			$("#<?php echo $name?>").datepicker( { dateFormat: 'dd/mm/yy' });
			
		});
	</script>
	<?php 
}



?>
<div class="box">



<form action='Document/search' method='get' >
	<input type='hidden' name='go' value='go' />
	<input type='hidden' name='date_in_fr' value='true' />

	<?php  $this->RechercheAvanceFormulaireHTML->display(); ?>
	<a class='btn' href='Document/search?id_e=<?php echo $id_e?>&type=<?php echo $type?>'>
        <i class="fa fa-undo"></i>&nbsp;
        Réinitialiser
    </a>

    <button type="submit" class="btn">
        <i class="fa fa-search"></i>&nbsp;Rechercher
    </button>
</form>
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
		$this->render("DocumentListBox");

		
		?>


        <a
                href="Document/traitementLot?<?php echo $url ?>"
                class="btn"
        >
            <i class='fa fa-cogs'></i>&nbsp;Traitement par lot
        </a>

			<a class='btn' href='Document/export?<?php echo $url?>'><i class='fa fa-download'></i>&nbsp;Exporter les informations (CSV)</a>
		<?php 
	} else {
		?>
		<div class="alert alert-info">
			Les critères de recherches ne correspondent à aucun document
		</div>
		<?php 
	}
}

