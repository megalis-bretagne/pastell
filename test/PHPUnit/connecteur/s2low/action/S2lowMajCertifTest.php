<?php

namespace PHPUnit\connecteur\s2low\action;

use ActionExecutorFactory;
use LastError;
use PastellTestCase;

class S2lowMajCertifTest extends PastellTestCase
{
    public function testCertificateUpdatePasswordNeeded()
    {
        $_FILES = [
            'user_certificat' => [
                'error' => UPLOAD_ERR_OK,
                'tmp_name' => __DIR__ . '/../../../fixtures/vide.pdf'
            ]
        ];
        $globalS2low = $this->createConnector('s2low', 's2low GLOBAL', 0);
        $id_ce = $globalS2low['id_ce'];
        $this->expectOutputRegex("/Location: (.*)editionModif\?id_ce=$id_ce/");

        $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class)->goChoiceOnConnecteur(
            $id_ce,
            self::ID_U_ADMIN,
            'mise-a-jour-certif-s2low',
            'changement-certificat',
            false,
            ['id_ce_list' => '123']
        );

        $lastError = $this->getObjectInstancier()->getInstance(LastError::class)->getLastError();

        $this->assertSame(
            'Il faut renseigner le mot de passe',
            $lastError
        );
    }
}
