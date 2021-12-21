<?php

class ExtensionCest
{
    public function listExtension(NoGuy $I)
    {
        $I->wantTo("lister les extensions");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGET("/extension");
        $I->verifyJsonResponseOK(array());
    }

    public function listExtensionV1(NoGuy $I)
    {
        $I->wantTo("lister les extensions");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("list-extension.php");
        $I->verifyJsonResponseOK(array());
    }

    public function ajouterExtension(NoGuy $I)
    {
        $I->wantTo("ajouter une extension");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/extension", array('path' => '/tmp/'));
        $id_extension = $I->grabDataFromResponseByJsonPath('$.id_extension')[0];
        $I->verifyJsonResponseOK(array("detail" => array('path' => '/tmp/')), \Codeception\Util\HttpCode::CREATED);
        $I->sendPATCH("/extension/$id_extension", array('path' => '/etc/'));
        $I->verifyJsonResponseOK(array("detail" => array('path' => '/etc/')));
        $I->sendDELETE("/extension/$id_extension");
        $I->verifyJsonResponseOK(array('result' => 'ok'));
    }

    public function ajouterExtensionV1(NoGuy $I)
    {
        $I->wantTo("ajouter une extension [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendGETV1("edit-extension.php?path=/tmp");
        $I->verifyJsonResponseOK(array("detail_extension" => array('path' => '/tmp')));
        $I->sendGET("/extension");
        $all = $I->grabDataFromResponseByJsonPath('$.result')[0];
        foreach ($all as $key => $properties) {
            if ($properties['path'] == '/tmp') {
                $id_extension = $key;
                $I->sendDELETE("/extension/$id_extension");
            }
        }
    }

    public function editAndDeleteExtensionV1(NoGuy $I)
    {
        $I->wantTo("editer une extension [V1]");
        $I->amHttpAuthenticatedAsAdmin();
        $I->sendPOST("/extension", array('path' => '/etc/'));
        $id_extension = $I->grabDataFromResponseByJsonPath('$.id_extension')[0];
        $I->sendGETV1("edit-extension.php?id_extension=$id_extension&path=/etc");
        $I->verifyJsonResponseOK(array("detail_extension" => array('path' => '/etc')));
        $I->sendGETV1("delete-extension.php?id_extension=$id_extension");
        $I->verifyJsonResponseOK(array('result' => 'ok'));
    }
}
