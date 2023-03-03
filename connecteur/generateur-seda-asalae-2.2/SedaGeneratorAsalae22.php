<?php

declare(strict_types=1);

use Pastell\Connector\AbstractSedaGeneratorConnector;
use Pastell\Seda\SedaVersion;

final class SedaGeneratorAsalae22 extends AbstractSedaGeneratorConnector
{
    public function getVersion(): SedaVersion
    {
        return SedaVersion::VERSION_2_2_ASALAE;
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

        $pastellToSeda = \array_merge(
            $pastellToSeda,
            [
                'archival_agency_name' => [
                    'position' => 11,
                    'seda' => 'ArchivalAgency.Name',
                    'libelle' => "Nom du service d'archives",
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
                    'position' => 81,
                    'seda' => 'OriginatingAgencyArchiveIdentifier',
                    'libelle' => "Identifiant donné à l'archive par le service producteur",
                    'commentaire' => 'OriginatingAgencyArchiveUnitIdentifier'
                ],
                'TransferringAgencyArchiveIdentifier' => [
                    'position' => 82,
                    'seda' => 'TransferringAgencyArchiveIdentifier',
                    'libelle' => "Identifiant donné à l'archive par le service versant",
                    'commentaire' => 'TransferringAgencyArchiveUnitIdentifier',
                ],
                'ArchiveUnit_ExternalReference' => [
                    'position' => 210,
                    'seda' => 'ExternalReference',
                    'libelle' => 'Référence à une unité d\'archive',
                    'commentaire' => 'RepositoryArchiveUnitPID',
                ],
            ]
        );
        array_multisort(array_column($pastellToSeda, 'position'), SORT_ASC, $pastellToSeda);
        return $pastellToSeda;
    }
}
