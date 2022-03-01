<?php

class PESRetourVisionneuse extends Visionneuse
{
    private function getDomaineLibelle($libelle_numero)
    {
        $libelle_list = array("technique","technique","technique","validité du certificat","pièce justificative","dépense","recette","budget");
        return $libelle_list[$libelle_numero];
    }

    public function display($filename, $filepath)
    {

        if (! file_exists($filepath)) {
            echo "Le fichier n'est pas encore disponible";
            return;
        }

        $xml = simplexml_load_file($filepath);

        $nomFic = $xml->Enveloppe->Parametres->NomFic['V'];

        $nb_erreur = 0;
        if (!empty($xml->ACQUIT->ElementACQUIT)) {
            foreach ($xml->ACQUIT->ElementACQUIT as $elementACQUIT) {
                if ($elementACQUIT->EtatAck['V'] != 1) {
                    $nb_erreur++;
                }
            }
        }

        ?>
<style>
.pes_retour{
    border-style: solid;
    border-width: thin;
    padding: 5px;
}
</style>
<div class='pes_retour'>
    <h1>Rapport acquittement</h1>
    <p>
        <b>Identification du flux : </b><?php echo $nomFic?>
    </p>
    <p class="libelleControl">
        <?php echo count($xml->ACQUIT->ElementACQUIT)?> éléments
        <?php if ($nb_erreur > 0) : ?>
             &nbsp;-&nbsp;<b style='color:red'><?php echo $nb_erreur?> erreur<?php echo $nb_erreur > 1 ? 's' : ''?></b>
        <?php endif;?>
    </p>
        <?php if (!empty($xml->ACQUIT->ElementACQUIT)) :?>
    <table>
        <tr>
            <th>Domaine</th>
            <th>Exercice</th>
            <th>Numéro de bordereau</th>
            <th>Acquitté</th>
            <th>Erreur</th>
        </tr>
            <?php foreach ($xml->ACQUIT->ElementACQUIT as $elementACQUIT) : ?>
        <tr>
            <td>
                <?php echo $this->getDomaineLibelle(strval($elementACQUIT->DomaineAck['V']))?>
            </td>
            <td><?php hecho($elementACQUIT->ExerciceBord['V'])?></td>
            <td><?php hecho($elementACQUIT->NumBord['V'])?></td>
            <td>
                <?php if ($elementACQUIT->EtatAck['V'] == 1) : ?>
                    <b style='color:green'>OUI</b>
                <?php else : ?>
                    <b style='color:red'>NON</b>
                <?php endif;?>
            </td>
            <td>
                <?php if ($elementACQUIT->EtatAck['V'] == 1) : ?>
                    &nbsp;
                <?php else : ?>
                    <?php if ($elementACQUIT->Erreur) : ?>
                         <b>Erreur <?php hecho($elementACQUIT->Erreur->NumAnoAck['V']) ?> :
                        <?php hecho($elementACQUIT->Erreur->LibelleAnoAck['V'])?>
                    <?php endif; ?>
                    <?php foreach ($elementACQUIT->DetailPiece as $detailPiece) : ?>
                        <?php if ($detailPiece->Erreur) : ?>
                            <?php if ($detailPiece->Erreur->NumAnoAck['V']) : ?>
                                <br>
                                Sur pièce n° <?php hecho($detailPiece->NumPiece['V'])?>
                                , <?php hecho($detailPiece->Erreur->NumAnoAck['V']) ?> : <?php hecho($detailPiece->Erreur->LibelleAnoAck['V'])?>
                            <?php endif;?>
                        <?php endif; ?>
                        <?php foreach ($detailPiece->DetailLigne as $detailLigne) : ?>
                            <?php if ($detailLigne->Erreur->NumAnoAck['V']) : ?>
                                <br>
                                Sur pièce n° <?php hecho($detailPiece->NumPiece['V'])?>
                                , ligne n° <?php hecho($detailLigne->NumLigne['V'])?>
                                , <?php hecho($detailLigne->Erreur->NumAnoAck['V']) ?> : <?php hecho($detailLigne->Erreur->LibelleAnoAck['V'])?>
                            <?php endif;?>
                        <?php endforeach;?>
                    <?php endforeach;?></b>
                <?php endif;?>
            </td>
        
        </tr>
            <?php endforeach;?>
    </table>
        <?php endif;?>
</div>
        <?php
    }
}