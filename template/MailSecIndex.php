
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
		<?php if($info_reponse) : ?>
			<?php $this->render("DonneesFormulaireDetail"); ?>
		<?php  else: ?>
			<?php $this->render("DonneesFormulaireEdition"); ?>
			<script>
				$(document).ready(function(){
					$("#donnees_formulaire_edition_enregister").click(function(){
						if(confirm("Le choix est définitif, vous ne pourrez pas revenir dessus. Êtes-vous sûr ? ")){
							return true;
						} else {
							return false;
						}
					});
				});
			</script>
		<?php endif; ?>
	</div>

<?php endif; ?>

