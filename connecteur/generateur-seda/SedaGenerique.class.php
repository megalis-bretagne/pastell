<?php

declare(strict_types=1);

use Pastell\Connector\AbstractSedaGeneratorConnector;

final class SedaGenerique extends AbstractSedaGeneratorConnector
{
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
            ]
        );
    }
}
