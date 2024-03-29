<?php
/**
 * @var MailSecInfo $mailSecInfo
 * @var string $reponse_url
 * @var string $reponse_recuperation_fichier_url
 *
 */
?>
<div class="box">
    <h2>Votre message</h2>
	<?php
        $this->donneesFormulaire = $mailSecInfo->donneesFormulaire ;
        $this->fieldDataList = $mailSecInfo->fieldDataList;
        $this->render("DonneesFormulaireDetail");
    ?>
</div>

<?php if ($mailSecInfo->has_flux_reponse) : ?>
    <?php if($mailSecInfo->has_reponse) : ?>
        <div class="box">
            <h2>Votre réponse</h2>
            <?php
                $this->donneesFormulaire = $mailSecInfo->donneesFormulaireReponse ;
                $this->fieldDataList = $mailSecInfo->fieldDataListReponse;
			    $this->recuperation_fichier_url = $reponse_recuperation_fichier_url;
                $this->render("DonneesFormulaireDetail");
            ?>
        </div>
    <?php  else: ?>
        <a href="<?php echo $reponse_url; ?>" class="btn">
            Répondre
        </a>
    <?php endif; ?>
<?php endif; ?>
