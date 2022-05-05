<?php

class StatsTest extends PastellTestCase
{
    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testGetStatsFromRoot(): void
    {
        $this->createDocument('test');

        $document = $this->createDocument('test', self::ID_E_SERVICE);
        $this->getDonneesFormulaireFactory()
            ->get($document['id_d'])
            ->addFileFromData('fichier', 'file name', 'data');
        $connector = $this->createConnector('stats', 'Stats', 0);
        $connectorFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($connector['id_ce']);
        $connectorFormulaire->setTabData([
            'entity_id' => '0',
            'module_type' => 'test',
            'include_children' => true,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 day')),
        ]);

        $this->triggerActionOnConnector($connector['id_ce'], 'get_stats');

        $test = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($connector['id_ce']);

        $this->assertSame(
            date('Y-m-d'),
            $test->get('csv_generation_date')
        );
        $expectedCsvContent = <<<EOT
id_e,Entité,Nombre,Taille,État,"État label"
1,Bourg-en-Bresse,1,225B,creation,Créé
2,CCAS,1,260B,creation,Créé

EOT;
        $this->assertSame($expectedCsvContent, $test->getFileContent('csv_file'));
    }
}
