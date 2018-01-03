<?php


class ActesGeneriqueTest extends PastellTestCase {

	const FLUX_ID = "actes-generique";

	public function testCasNominal(){

		$result= $this->getInternalAPI()->post("/Document/".PastellTestCase::ID_E_COL,array('type'=>self::FLUX_ID));
		$this->assertNotEmpty($result['id_d']);

		$info['id_d'] = $result['id_d'];
		$info['id_e'] = PastellTestCase::ID_E_COL;
		$info['acte_nature'] = 1;
		$info['numero_de_lacte'] = "TEST20131202A";
		$info['objet'] = "Test d'un actes soumis au contrôle de légalité";
		$info['date_de_lacte'] = "2013-12-02";
		$info['envoi_signature'] = 1;
		$info['envoi_tdt'] = 1;
		$info['envoi_sae'] = 1;
		$info['envoi_ged']  = 1;
		$info['classification'] =  '2.1 Documents d urbanisme';
		$info['iparapheur_type'] = 'Actes';
		$info['iparapheur_sous_type'] = 'Deliberation';

		$result = $this->getInternalAPI()->patch(
		    "/Document/{$info['id_e']}/actes-generique/{$info['id_d']}",
            $info
        );

		$this->assertEquals('Test d\'un actes soumis au contrôle de légalité',$result['content']['data']['objet']);

		$uploaded_file = $this->getEmulatedDisk()."/tmp/Delib Adullact.pdf";
		copy(__DIR__."/fixtures/Delib Adullact.pdf",$uploaded_file);
		$result = $this->getInternalAPI()->post(
		    "/Document/{$info['id_e']}/actes-generique/{$info['id_d']}/file/arrete",
            array('file_name'=>'Delib Adullact.pdf','file_content'=>file_get_contents($uploaded_file))
        );
        $this->assertEquals('Delib Adullact.pdf',$result['content']['data']['arrete'][0]);

        #$content = $this->getInternalAPI()->get("/Document/{$info['id_e']}/actes-generique/{$info['id_d']}/file/arrete");

		#$this->assertEquals(file_get_contents(__DIR__."/fixtures/Delib Adullact.pdf"),$content);
	}


}