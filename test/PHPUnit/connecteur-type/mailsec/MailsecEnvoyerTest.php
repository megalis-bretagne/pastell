<?php

require_once __DIR__ . "/../../../../connecteur/mailsec/MailSec.class.php";

class MailsecEnvoyerTest extends PastellTestCase
{

    private const MAILSEC_FLUX_ID = 'mailsec';

    /**
     * @throws NotFoundException
     */
    public function testWithVariousData()
    {
        $this->createAnnuaireGroup(1, 'mother_group', ['foo','bar','baz']);
        $this->createAnnuaireGroup(2, 'empty_groupe', []);
        $this->createAnnuaireGroup(2, 'one_groupe', ['pim','pam','poum']);

        $annuaireRoleSQL = $this->getObjectInstancier()->getInstance(AnnuaireRoleSQL::class);

        $annuaireRoleSQL->add("admin - toutes les collectivités", 2, 0, "admin");

        $id_r = $annuaireRoleSQL->add("test - toutes les collectivités", 1, 0, "test");
        $annuaireRoleSQL->partage($id_r);

        $id_d = $this->prepareAndEnvoiMail(
            'groupe: "one_groupe", ' .
            'foo@bar.com, ' .
            'groupe hérité de Bourg-en-Bresse: "mother_group", ' .
            'role: "admin - toutes les collectivités", ' .
            'rôle hérité de Bourg-en-Bresse: "test - toutes les collectivités"'
        );

        $documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);

        $this->assertEquals(
            [
                '"bar" <bar@test.com>',
                '"baz" <baz@test.com>',
                '"Eric Pommateau" <eric2@sigmalis.com>',
                '"Eric Pommateau" <eric@sigmalis.com>',
                '"foo" <foo@test.com>',
                '"pam" <pam@test.com>',
                '"pim" <pim@test.com>',
                '"poum" <poum@test.com>',
                'foo@bar.com',
            ],
            $documentEmail->getAllEmail($id_d)
        );
    }

    private function createAnnuaireGroup(int $id_e, string $group_name, array $group_member): void
    {
        $annuaireGroupe = new AnnuaireGroupe($this->getSQLQuery(), $id_e);
        $id_g = $annuaireGroupe->add($group_name);
        $annuaireGroupe->tooglePartage($id_g);
        $annuaireSQL = $this->getObjectInstancier()->getInstance(AnnuaireSQL::class);
        foreach ($group_member as $member) {
            $id_a = $annuaireSQL->add(1, "$member", "$member@test.com");
            $annuaireGroupe->addToGroupe($id_g, $id_a);
        }
    }

    /**
     * @throws NotFoundException
     */
    public function testGoWithEmptyGroup()
    {
        $this->createAnnuaireGroup(2, 'empty_groupe', []);
        $this->prepareAndEnvoiMail('groupe: "my_group"');
        $this->assertLastMessage(
            "Impossible d'envoyer le document car il n'y a pas de destinataires (groupe ou role vide)"
        );
    }

    /**
     * @param string $to
     * @return string
     * @throws NotFoundException
     */
    private function prepareAndEnvoiMail(string $to): string
    {
        $id_ce = $this->createConnector(
            MailSec::CONNECTEUR_ID,
            "Mail sécurisé de test",
            2
        )['id_ce'];
        $this->associateFluxWithConnector(
            $id_ce,
            self::MAILSEC_FLUX_ID,
            MailsecConnecteur::CONNECTEUR_TYPE_ID,
            2
        );
        $id_d = $this->createDocument(self::MAILSEC_FLUX_ID, 2)['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->setTabData([
            'to' => $to
        ]);

        $this->triggerActionOnDocument($id_d, 'envoi', 2);
        return $id_d;
    }
}
