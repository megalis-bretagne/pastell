
<h2 >Message</h2>

<div class="box">
	<?php $this->render("DonneesFormulaireDetail"); ?>
</div>

<?php if (isset($donneesFormulaireReponse)) :

	$this->donneesFormulaire = $donneesFormulaireReponse ;
	$this->fieldDataList = $fieldDataListResponse

?>

	<h2 >Réponse</h2>

	<div class="box">
		<?php $this->render("DonneesFormulaireEdition"); ?>
	</div>

<?php endif; ?>

