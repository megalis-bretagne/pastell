<?php

/**
 * @var MailSecInfo $mailSecInfo
 * @var string $reponse_url
 * @var string $validation_url;
 * @var string $reponse_recuperation_fichier_url
 * @var string $download_all_link
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

<div class="box">
    <h2>Votre réponse</h2>
    <div class="alert alert-info">Votre réponse ne sera pas envoyée tant que vous ne l'avez pas validée</div>
    <?php
    $this->donneesFormulaire = $mailSecInfo->donneesFormulaireReponse ;
    $this->fieldDataList = $mailSecInfo->fieldDataListReponse;
    $this->recuperation_fichier_url = $reponse_recuperation_fichier_url;
    $this->download_all_link = $download_all_link . "&fichier_reponse=true";

    $this->render("DonneesFormulaireDetail");
    ?>
    <a href="<?php echo $reponse_url; ?>" class="btn btn-outline-primary">
        Modifier
    </a>
    <a href="<?php echo $validation_url; ?>" class="btn btn-primary">
        Valider
    </a>
</div>
