<?php

class TestConnexionTest extends ExtensionCppTestCase
{
    /**
     * @return array
     */
    public function getConnexionProvider(): array
    {
        return [
            'OauthOK' =>
                [
                    "cpp url token",
                    "61cde1ef-41ab-441c-b23f-95991f9d919g",
                    "bd307b18-298e-45a7-a4ef-9169200fad63",
                    "cpp url api",
                    "DEV_DESTTAA074@cpp2017.fr",
                    "Riuxdnup64167[",
                    "La connexion est réussie"
                ],
            'OauthKONeedElement' =>
                [
                    "cpp url token",
                    "",
                    "bd307b18-298e-45a7-a4ef-9169200fad63",
                    "cpp url api",
                    "DEV_DESTTAA074@cpp2017.fr",
                    "Riuxdnup64167[",
                    "Il manque des éléments pour l'authentification PISTE, le connecteur global est-il bien associé ?"
                ],
            'OauthKONeedUser' =>
                [
                    "cpp url token",
                    "61cde1ef-41ab-441c-b23f-95991f9d919g",
                    "bd307b18-298e-45a7-a4ef-9169200fad63",
                    "cpp url api",
                    "",
                    "Riuxdnup64167[",
                    "Erreur: Utilisateur sans Login/Mot de passe"
                ],
        ];
    }

    /**
     * @param $url_piste_get_token
     * @param $client_id
     * @param $client_secret
     * @param $url_piste_api
     * @param $user_login
     * @param $user_password
     * @param $last_message_expected
     * @throws Exception
     * @dataProvider getConnexionProvider
     */
    public function testTestConnexion(
        $url_piste_get_token,
        $client_id,
        $client_secret,
        $url_piste_api,
        $user_login,
        $user_password,
        $last_message_expected
    ) {
        $curlWrapper = $this->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapper->expects($this->any())->method('get')->willReturn(
            json_encode(
                [
                    'token_type' => 'foo',
                    'access_token' => 'bar',
                    'expires_in' => 42
                ]
            )
        );
        $curlWrapper->expects($this->any())->method('getLastHttpCode')->willReturn(200);

        $curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $curlWrapperFactory->expects($this->any())->method('getInstance')->willReturn($curlWrapper);

        $this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);

        $id_ce_chorus = $this->createCppConnector("facture-cpp");
        $connecteurDonneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce_chorus);
        $connecteurDonneesFormulaire->setData('url_piste_get_token', $url_piste_get_token);
        $connecteurDonneesFormulaire->setData('client_id', $client_id);
        $connecteurDonneesFormulaire->setData('client_secret', $client_secret);
        $connecteurDonneesFormulaire->setData('url_piste_api', $url_piste_api);
        $connecteurDonneesFormulaire->setData('user_login', $user_login);
        $connecteurDonneesFormulaire->setData('user_password', $user_password);

        $this->triggerActionOnConnector($id_ce_chorus, 'test-cpp');

        $this->assertLastMessage($last_message_expected);
    }
}
