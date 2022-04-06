<?php

class HeliosGeneriquePesAllerChangeTest extends PastellTestCase
{
    public const FILENAME = "HELIOS_SIMU_ALR2_1496987735_826268894.xml";
    public const OBJET = 'foo-bar';

    private function createHeliosgenerique()
    {
        $info = $this->getInternalAPI()->post(
            "/entite/1/document",
            ['type' => 'helios-generique']
        );

        return $info['id_d'];
    }

    private function postPES($id_d)
    {
        $this->getInternalAPI()->post(
            "/entite/1/document/$id_d/file/fichier_pes",
            [
                'file_name' => self::FILENAME,
                'file_content' =>
                    file_get_contents(__DIR__ . "/../fixtures/" . self::FILENAME)
            ]
        );
        $actionExecutorFactory = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class);
        $actionExecutorFactory->executeOnDocument(1, 0, $id_d, 'fichier_pes_change');
    }

    public function testObjetBecomeFilenameIfEmpty()
    {
        $id_d = $this->createHeliosgenerique();
        $this->postPES($id_d);

        $info = $this->getInternalAPI()->get("/entite/1/document/$id_d");
        $this->assertEquals(self::FILENAME, $info['data']['objet']);
        $this->assertEquals(self::FILENAME, $info['info']['titre']);
    }

    public function testObjetDidNotBecomeFilenameIfNotEmpty()
    {
        $id_d = $this->createHeliosgenerique();

        $this->getInternalAPI()->patch(
            "/entite/1/document/$id_d/",
            [
                    'objet' => self::OBJET
                ]
        );

        $this->postPES($id_d);

        $info = $this->getInternalAPI()->get("/entite/1/document/$id_d");
        $this->assertEquals(self::OBJET, $info['data']['objet']);
        $this->assertEquals(self::OBJET, $info['info']['titre']);
    }
}
