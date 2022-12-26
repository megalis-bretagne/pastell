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
        $pastellToSeda['archiveunits_title']['commentaire'] = 'Archive - Description';
        $pastellToSeda['StartDate']['commentaire'] = 'OldestDate (forme attendue Y-m-d)';
        $pastellToSeda['EndDate']['commentaire'] = 'LatestDate (forme attendue Y-m-d)';
        $pastellToSeda['CustodialHistory']['commentaire'] = 'Archive - CustodialHistoryItem';
        $pastellToSeda['AccessRule_Rule']['commentaire'] = 'Archive - AccessRestrictionRule - Code (forme attendue : de AR038 à AR062)';
        $pastellToSeda['AccessRule_StartDate']['commentaire'] = 'AccessRestrictionRule - StartDate (forme attentue Y-m-d)';
        $pastellToSeda['AppraisalRule_Rule']['commentaire'] = 'AppraisalRule - Duration (forme attendue encoder en xsd:duration, voir http://www.datypic.com/sc/xsd/t-xsd_duration.html)';
        $pastellToSeda['AppraisalRule_FinalAction']['commentaire'] = 'AppraisalRule - Code (forme attendue: Conserver OU Détruire)';

        return \array_merge(
            $pastellToSeda,
            [
                'archival_agency_name' => [
                    'seda' => 'ArchivalAgency.Name',
                    'libelle' => "Nom du service d'archive",
                    'commentaire' => 'ArchivalAgency - Name',
                ],
                'transferring_agency_name' => [
                    'seda' => 'TransferringAgency.Name',
                    'libelle' => 'Nom du service versant',
                    'commentaire' => 'TransferringAgency - Name',
                ],
                'originating_agency_identifier' => [
                    'seda' => 'OriginatingAgency.Identifier',
                    'libelle' => 'Identifiant du service producteur',
                    'commentaire' => 'OriginatingAgency - Identifier',
                ],
                'originating_agency_name' => [
                    'seda' => 'OriginatingAgency.Name',
                    'libelle' => 'Nom du service producteur',
                    'commentaire' => 'OriginatingAgency - Name',
                ],
                'OriginatingAgencyArchiveIdentifier' => [
                    'seda' => 'OriginatingAgencyArchiveIdentifier',
                    'libelle' => "Identifiant donné à l'archive par le service producteur",
                    'commentaire' => 'OriginatingAgencyArchiveIdentifier',
                ],
                'TransferringAgencyArchiveIdentifier' => [
                    'seda' => 'TransferringAgencyArchiveIdentifier',
                    'libelle' => "Identifiant donné à l'archive par le service versant",
                    'commentaire' => 'TransferringAgencyArchiveIdentifier',
                ],
            ]
        );
    }
}
