<?php

declare(strict_types=1);

use Pastell\Connector\AbstractSedaGeneratorConnector;
use Pastell\Seda\SedaVersion;

final class SedaGeneratorAsalae21 extends AbstractSedaGeneratorConnector
{
    public function getVersion(): SedaVersion
    {
        return SedaVersion::VERSION_2_1;
    }

    public function getPastellToSeda(): array
    {
        $pastellToSeda = parent::getPastellToSeda();
        $pastellToSeda['titre']['commentaire'] = 'ArchiveUnit - Title';
        $pastellToSeda['Language']['commentaire'] = 'Language (forme attendue: fr)';
        $pastellToSeda['DescriptionLanguage']['commentaire'] = 'DescriptionLanguage (forme attendue: fr)';
        $pastellToSeda['archiveunits_title']['commentaire'] = 'ArchiveUnit - Description';
        $pastellToSeda['StartDate']['commentaire'] = 'StartDate (forme attendue Y-m-d)';
        $pastellToSeda['EndDate']['commentaire'] = 'EndDate (forme attendue Y-m-d)';
        $pastellToSeda['CustodialHistory']['commentaire'] = 'ArchiveUnit - CustodialHistoryItem';
        $pastellToSeda['AccessRule_Rule']['commentaire'] = 'AccessRule - Rule (forme attendue : de AR038 à AR062)';
        $pastellToSeda['AccessRule_StartDate']['commentaire'] = 'AccessRule - StartDate (forme attentue Y-m-d)';
        $pastellToSeda['AppraisalRule_Rule']['commentaire'] = 'AppraisalRule - Rule (forme attendue encoder en xsd:duration, voir http://www.datypic.com/sc/xsd/t-xsd_duration.html)';
        $pastellToSeda['AppraisalRule_FinalAction']['commentaire'] = 'AppraisalRule - FinalAction (forme attendue: Conserver OU Détruire)';

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
                    'commentaire' => 'OriginatingAgencyArchiveUnitIdentifier'
                ],
                'TransferringAgencyArchiveIdentifier' => [
                    'seda' => 'TransferringAgencyArchiveIdentifier',
                    'libelle' => "Identifiant donné à l'archive par le service versant",
                    'commentaire' => 'TransferringAgencyArchiveUnitIdentifier',
                ],
            ]
        );
    }
}
