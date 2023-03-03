<?php

declare(strict_types=1);

use Pastell\Connector\AbstractSedaGeneratorConnector;
use Pastell\Seda\SedaVersion;

final class SedaGeneratorAsalae10 extends AbstractSedaGeneratorConnector
{
    public function getVersion(): SedaVersion
    {
        return SedaVersion::VERSION_1_0;
    }
    public function getPastellToSeda(): array
    {
        $pastellToSeda = parent::getPastellToSeda();
        $pastellToSeda['titre']['commentaire'] = 'Archive - Name';
        $pastellToSeda['Language']['commentaire'] = 'Language (forme attendue: fra)';
        $pastellToSeda['DescriptionLanguage']['commentaire'] = 'DescriptionLanguage (forme attendue: fra)';
        $pastellToSeda['DescriptionLevel']['commentaire'] =
            'DescriptionLevel (attendue : class, collection, file, fonds, item,
                recordgrp, series, subfonds, subgrp, subseries)';
        $pastellToSeda['archiveunits_title']['commentaire'] = 'Archive - Description';
        $pastellToSeda['StartDate']['commentaire'] = 'OldestDate (forme attendue Y-m-d)';
        $pastellToSeda['EndDate']['commentaire'] = 'LatestDate (forme attendue Y-m-d)';
        $pastellToSeda['CustodialHistory']['commentaire'] = 'Archive - CustodialHistoryItem';
        $pastellToSeda['AccessRule_Rule']['commentaire'] =
            'Archive - AccessRestrictionRule - Code (forme attendue : de AR038 à AR062)';
        $pastellToSeda['AccessRule_StartDate']['commentaire'] =
            'AccessRestrictionRule - StartDate (forme attentue Y-m-d)';
        $pastellToSeda['AppraisalRule_Rule']['commentaire'] =
            'AppraisalRule - Duration (forme attendue encoder en xsd:duration, 
            voir http://www.datypic.com/sc/xsd/t-xsd_duration.html)';
        $pastellToSeda['AppraisalRule_FinalAction']['commentaire'] =
            'AppraisalRule - Code (forme attendue: Conserver OU Détruire)';
        $pastellToSeda['keywords']['commentaire'] =
            "Un mot clé par ligne de la forme : 'Contenu du mot-clé','KeywordReference','KeywordType'
              <br/><br/>Attention, si un élément contient une virgule, il est nécessaire d'entourer l'expression par des 'guillemets'
              <br/><br/>L'ensemble du champ est analysé avec Twig, puis les lignes sont lues comme des lignes CSV
              ( , comme séparateur de champs, \" comme clôture de champs et \ comme caractère d'échappement)
              <br/><br/>Les mots clés sont mis dans le bordereau au niveau Archive - Keyword";

        $pastellToSeda = \array_merge(
            $pastellToSeda,
            [
                'archival_agency_name' => [
                    'position' => 11,
                    'seda' => 'ArchivalAgency.Name',
                    'libelle' => "Nom du service d'archive",
                    'commentaire' => 'ArchivalAgency - Name',
                ],
                'transferring_agency_name' => [
                    'position' => 21,
                    'seda' => 'TransferringAgency.Name',
                    'libelle' => 'Nom du service versant',
                    'commentaire' => 'TransferringAgency - Name',
                ],
                'originating_agency_identifier' => [
                    'position' => 22,
                    'seda' => 'OriginatingAgency.Identifier',
                    'libelle' => 'Identifiant du service producteur',
                    'commentaire' => 'OriginatingAgency - Identifier',
                ],
                'originating_agency_name' => [
                    'position' => 23,
                    'seda' => 'OriginatingAgency.Name',
                    'libelle' => 'Nom du service producteur',
                    'commentaire' => 'OriginatingAgency - Name',
                ],
                'OriginatingAgencyArchiveIdentifier' => [
                    'position' => 71,
                    'seda' => 'OriginatingAgencyArchiveIdentifier',
                    'libelle' => "Identifiant donné à l'archive par le service producteur",
                    'commentaire' => 'OriginatingAgencyArchiveIdentifier',
                ],
                'TransferringAgencyArchiveIdentifier' => [
                    'position' => 72,
                    'seda' => 'TransferringAgencyArchiveIdentifier',
                    'libelle' => "Identifiant donné à l'archive par le service versant",
                    'commentaire' => 'TransferringAgencyArchiveIdentifier',
                ],
            ]
        );
        array_multisort(array_column($pastellToSeda, 'position'), SORT_ASC, $pastellToSeda);
        return $pastellToSeda;
    }
}
