<?php

class IParapheurEmptyWSDLCacheTest extends PastellTestCase
{
    public function testGo()
    {
        $wsdl_cache_file = ini_get("soap.wsdl_cache_dir") . "/wsdl-phpunit-test";
        file_put_contents($wsdl_cache_file, "test");
        $this->assertFileExists($wsdl_cache_file);

        $result = $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur",
            array('libelle' => 'I parapheur' , 'id_connecteur' => 'iParapheur')
        );

        $id_ce = $result['id_ce'];

        $this->getInternalAPI()->post("/entite/1/connecteur/$id_ce/action/iparapheur-empty-cache");

        $this->assertEquals(
            "executeOnConnecteur - fin - id_ce=$id_ce,id_u=1,action_name=iparapheur-empty-cache : OK - \"Le cache WSDL a \u00e9t\u00e9 supprim\u00e9\"",
            $this->getLogRecords()[1]['message']
        );
        $this->assertFileNotExists($wsdl_cache_file);
    }

    public function testNoDeleteInSubDirectory()
    {
        $dir = ini_get("soap.wsdl_cache_dir") . "/foo/";

        if (! file_exists($dir)) {
            mkdir($dir);
        }

        $wsdl_cache_file = "$dir/wsdl-phpunit-test";
        file_put_contents($wsdl_cache_file, "test");
        $this->assertFileExists($wsdl_cache_file);

        $result = $this->getInternalAPI()->post(
            "/entite/" . self::ID_E_COL . "/connecteur",
            array('libelle' => 'I parapheur' , 'id_connecteur' => 'iParapheur')
        );

        $id_ce = $result['id_ce'];

        $this->getInternalAPI()->post("/entite/1/connecteur/$id_ce/action/iparapheur-empty-cache");

        $this->assertEquals(
            "executeOnConnecteur - fin - id_ce=$id_ce,id_u=1,action_name=iparapheur-empty-cache : OK - \"Le cache WSDL a \u00e9t\u00e9 supprim\u00e9\"",
            $this->getLogRecords()[1]['message']
        );
        $this->assertFileExists($wsdl_cache_file);
    }
}
