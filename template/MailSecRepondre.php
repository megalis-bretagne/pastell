<?php

/**
 * @var Gabarit $this
 * @var MailSecInfo $mailSecInfo
 * @var string $reponse_recuperation_fichier_url
 */
?>
<div class="box">
    <h2>Votre message</h2>
    <?php
        $this->setViewParameter('donneesFormulaire', $mailSecInfo->donneesFormulaire);
        $this->setViewParameter('fieldDataList', $mailSecInfo->fieldDataList);
        $this->render("DonneesFormulaireDetail");
    ?>
</div>

<div class="box">
    <h2>Votre réponse</h2>
    <?php
    $this->setViewParameter('donneesFormulaire', $mailSecInfo->donneesFormulaireReponse);
    $this->setViewParameter('fieldDataList', $mailSecInfo->fieldDataListReponse);
    $this->setViewParameter('recuperation_fichier_url', $reponse_recuperation_fichier_url);

    $this->render("DonneesFormulaireEdition");
    ?>
</div>

