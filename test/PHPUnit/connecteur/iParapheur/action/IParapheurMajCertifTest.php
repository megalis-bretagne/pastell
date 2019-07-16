<?php

class IParapheurMajCertifTest extends PastellTestCase
{

    public function testCertificateUpdatePasswordNeeded() {
        $_POST = [
            'id_ce_list' => '123',
        ];
        $_FILES = [
            'user_certificat' => [
                'error' => UPLOAD_ERR_OK,
                'tmp_name' =>  __DIR__ . '/../../../fixtures/vide.pdf'
            ]
        ];

        $globalIparapheur = $this->createConnector('iParapheur', 'iParapheur GLOBAL', 0);

        $this->triggerActionOnConnector($globalIparapheur['id_ce'], 'mise-a-jour-certif-i-parapheur');

        $lastError = $this->getObjectInstancier()->getInstance(LastError::class)->getLastError();

        $this->assertSame(
            'Il faut renseigner le mot de passe',
            $lastError
        );
    }


}