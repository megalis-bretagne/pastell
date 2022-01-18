<?php

require_once __DIR__ . "/../../../../connecteur-type/PortailFactureConnecteur.class.php";
require_once __DIR__ . "/../../../../connecteur/cpp/CPP.class.php";

class CPPListeFactureTravauxTest extends ExtensionCppTestCase
{
    /**
     * @return array
     */
    public function getFactureTravauxProvider()
    {
        return [
            'FactureWithPisteAndRole' =>
                [
                    false,
                    "MOA",
                    'Liste des factures de travaux ayant changé de statut entre le 2019-01-01 et le ' . date('Y-m-d') . ': ' .
                        '{"listeFactures":[{"identifiantDestinataire":"00000000013456","identifiantFournisseur":"00000000000727","dateDepot":"2019-07-11",' .
                        '"dateFactureTravaux":"2019-07-11","dateHeureEtatCourant":"2019-07-11T15:45:39.674+02:00","designationDestinataire":"TAA074DESTINATAIRE",' .
                        '"designationFournisseur":"TAA001DESTINATAIRE","devise":"EUR","factureTelechargeeParDestinataire":true,"idDestinataire":"25784152",' .
                        '"idFactureTravaux":"4100169","montantAPayer":"10","montantHT":"10","montantTTC":"20","numeroFactureTravaux":"20190711-1",' .
                        '"statutFactureTravaux":"A_ASSOCIER_MOA","typeDemandePaiement":"FACTURE_TRAVAUX","typeFactureTravaux":"PROJET_DECOMPTE_MENSUEL",' .
                        '"typeIdentifiantFournisseur":"SIRET"}]}',

                ],
            'FactureWithoutPiste' =>
                [
                    true,
                    "MOA",
                    "La récupération des factures de travaux nécessite un raccordement en mode Oauth PISTE",
                ],
            'FactureWithoutRole' =>
                [
                    false,
                    "",
                    "Il faut sélectionner le rôle de l'utilisateur pour la récupération des factures de travaux",
                ],
        ];
    }


    /**
     * @param $is_raccordement_certificat
     * @param $user_role
     * @param $last_message_expected
     * @dataProvider getFactureTravauxProvider
     */
    public function testCPPListeFactureTravaux($is_raccordement_certificat, $user_role, $last_message_expected)
    {
        $cppWrapper = $this->getMockBuilder(CPPWrapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapper->expects($this->any())
            ->method('rechercheFactureTravaux')
            ->willReturn($this->getrechercheFactureTravaux());
        $cppWrapper->expects($this->any())
            ->method('getIsRaccordementCertificat')
            ->willReturn($is_raccordement_certificat);

        $cppWrapperFactory = $this->getMockBuilder(CPPWrapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cppWrapperFactory->expects($this->any())->method('newInstance')->willReturn($cppWrapper);

        $this->getObjectInstancier()->setInstance(CPPWrapperFactory::class, $cppWrapperFactory);

        $id_ce_chorus = $this->createCppConnector("facture-cpp");
        $connecteurConfig = $this->getConnecteurFactory()->getConnecteurConfig($id_ce_chorus);
        $connecteurConfig->setData('user_role', $user_role);

        $this->triggerActionOnConnector($id_ce_chorus, 'list-facture-travaux');

        $this->assertLastMessage($last_message_expected);
    }
}
