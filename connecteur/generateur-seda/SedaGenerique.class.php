<?php

declare(strict_types=1);

use Pastell\Connector\AbstractSedaGeneratorConnector;

final class SedaGenerique extends AbstractSedaGeneratorConnector
{
    public const CONNECTEUR_TYPE_ID = 'generateur-seda';

    public const CONNECTEUR_GLOBAL_TYPE = 'Generateur SEDA';

    public function getPastellToSeda(): array
    {
        return array_merge(
            parent::getPastellToSeda(),
            [
                'version' => [
                    'seda' => 'version',
                    'libelle' => 'Version du SEDA',
                    'value' => ['1.0', '2.1'],
                ],
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
                    'commentaire' => "OriginatingAgencyArchiveIdentifier (seda 1.0) / " .
                        "OriginatingAgencyArchiveUnitIdentifier (seda 2.1)"
                ],
                'TransferringAgencyArchiveIdentifier' => [
                    'seda' => 'TransferringAgencyArchiveIdentifier',
                    'libelle' => "Identifiant donné à l'archive par le service versant",
                    'commentaire' => "TransferringAgencyArchiveIdentifier (seda 1.0) / " .
                        "TransferringAgencyArchiveUnitIdentifier (seda 2.1)"
                ],
            ]
        );
    }
}
