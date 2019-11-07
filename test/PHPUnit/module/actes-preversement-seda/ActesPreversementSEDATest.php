<?php

class ActesPreversementSEDATest extends PastellTestCase
{

    const FLUX_ID = 'actes-preversement-seda';

    public function testCasNominal()
    {

        $result = $this->getInternalAPI()->post(
            "/Document/" . PastellTestCase::ID_E_COL,
            array('type' => self::FLUX_ID)
        );
        $this->assertNotEmpty($result['id_d']);

        $info['id_d'] = $result['id_d'];
        $info['id_e'] = PastellTestCase::ID_E_COL;
        $info['titre'] = "Test d'un versement";

        $this->getInternalAPI()->patch(
            "/Document/{$info['id_e']}/actes-preversement-seda/{$info['id_d']}",
            $info
        );


        $this->upload(
            $info,
            'enveloppe_metier',
            __DIR__ . "/fixtures/034-491011698-20171207-CL20171227_06-DE-1-1_0.xml"
        );

        $this->upload(
            $info,
            'document',
            __DIR__ . "/fixtures/034-491011698-20171207-CL20171227_06-DE-1-1_1.pdf"
        );
        $this->upload(
            $info,
            'document',
            __DIR__ . "/fixtures/034-491011698-20171207-CL20171227_06-DE-1-1_2.pdf",
            1
        );

        $this->upload(
            $info,
            'aractes',
            __DIR__ . "/fixtures/034-491011698-20171207-CL20171227_06-DE-1-2.xml"
        );


        $result = $this->getInternalAPI()->post("/entite/{$info['id_e']}/document/{$info['id_d']}/action/create-acte");

        preg_match("#Création du document Pastell (.*)#", $result['message'], $matches);
        $id_d = $matches[1];

        $result = $this->getInternalAPI()->get("/entite/{$info['id_e']}/document/$id_d");

        $this->assertEquals("3.2", $result['data']['classification']);
        $this->assertEquals("importation", $result['last_action']['action']);
    }


    private function upload($info, $field, $filepath, $filenum = 0)
    {
        $filename = basename($filepath);
        $uploaded_file = $this->getEmulatedDisk() . "/tmp/$filename";
        copy($filepath, $uploaded_file);
        $result = $this->getInternalAPI()->post(
            "/Document/{$info['id_e']}/actes-preversement-seda/{$info['id_d']}/file/$field/$filenum",
            array('file_name' => $filename,
                'file_content' => file_get_contents($uploaded_file))
        );
        return $result;
    }
}
