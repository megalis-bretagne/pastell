<?php


class HeliosGeneriquePesAllerChangeTest extends PastellTestCase {



    public function testCreateDocument(){
        $info = $this->getInternalAPI()->post(
            "/entite/1/document",
            array('type'=>'helios-generique')
        );
       $info = $this->getInternalAPI()->post("/entite/1/document/{$info['id_d']}/file/fichier_pes",
            array(
                'file_name'=>'HELIOS_SIMU_ALR2_1496987735_826268894.xml',
                'file_content'=>
                    file_get_contents(__DIR__."/../fixtures/HELIOS_SIMU_ALR2_1496987735_826268894.xml")
            )
        );

      /*  $result = $this->getInternalAPI()->post("/entite/1/document/{$info['id_d']}");
        print_r($info);*/

    }


}
