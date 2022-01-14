<?php

class DefinitionFilesTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testAllFlux()
    {
        $this->getObjectInstancier()->getInstance(MemoryCache::class)->flushAll();
        $systemControler = $this->getObjectInstancier()->getInstance(SystemControler::class);

        $documentTypeFactory = $this->getObjectInstancier()->getInstance(DocumentTypeFactory::class);
        $all = $documentTypeFactory->getAllType();

        foreach ($all as $type) {
            foreach ($type as $id => $flux_name) {
                try {
                    $this->assertTrue(
                        $systemControler->isDocumentTypeValid($id)
                    );
                } catch (Exception $e) {
                    $this->fail("Le fichier definition.yml du flux $id présente des erreurs : " . $e->getMessage());
                }
            }
        }
    }
}
