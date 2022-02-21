<?php

class ZenMailTest extends PastellTestCase
{
    /**
     * @var ZenMail
     */
    private $zenMail;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zenMail = $this->getObjectInstancier()->getInstance(ZenMail::class);
    }

    public function testSetSujet()
    {
        $this->zenMail->setSujet("Sujet");
        $this->assertEquals($this->zenMail->getSujet(), "=?UTF-8?Q?Sujet?=");
    }

    public function testSetSujetAccent()
    {
        $this->zenMail->setSujet("Sujet à accent");
        $this->assertEquals($this->zenMail->getSujet(), "=?UTF-8?Q?Sujet=20=C3=A0=20accent?=");
    }

    public function testSetSujetLong()
    {
        $this->zenMail->setSujet("ceci est un très long sujet de mail envoyé par Pastell. De plus ce sujet contient aussi un accent");
        $this->assertEquals(
            "=?UTF-8?Q?ceci=20est=20un=20tr=C3=A8s=20long=20sujet=20de=20mail=20envoy?=
 =?UTF-8?Q?=C3=A9=20par=20Pastell.=20De=20plus=20ce=20sujet=20contient=20a?=
 =?UTF-8?Q?ussi=20un=20accent?=",
            $this->zenMail->getSujet()
        );
    }

    public function emetteurProvider(): iterable
    {
        yield 'setEmetteurWithReply' => [
            'PASTELL',
            'mail@example.org',
            'mail_reply@example.org',
            'UEFTVEVMTA==?=<mail@example.org>',
            'mail_reply@example.org'
        ];
        yield 'setEmetteurWithoutReply' => [
            'ma_collectivite',
            'mail_collectivite@example.org',
            '',
            'bWFfY29sbGVjdGl2aXRl?=<mail_collectivite@example.org>',
            'mail_collectivite@example.org'
        ];
    }

    /**
     * @dataProvider emetteurProvider
     * @throws Exception
     */
    public function testSend(string $nom, string $mail, string $reply_to, string $expected_from, string $expected_reply_to)
    {
        $this->zenMail->setDestinataire('baz@baz.com');
        $this->zenMail->setSujet("mon sujet");
        $this->zenMail->setContenuText("test");
        $this->zenMail->setEmetteur($nom, $mail, $reply_to);
        $this->zenMail->setReturnPath('return-path@bar.com');
        $this->zenMail->send();

        $info = $this->zenMail->getAllInfo();

        $this->assertEquals(array (
            0 =>
                array (
                    'destinataire' => 'baz@baz.com',
                    'sujet' => '=?UTF-8?Q?mon=20sujet?=',
                    'contenu' => 'test',
                    'entete' => 'From: =?utf-8?B?' . $expected_from . '
Reply-To: ' . $expected_reply_to . '
Content-Type: text/plain; charset="UTF-8"
Return-Path: return-path@bar.com',
                    'return_path' => '-f return-path@bar.com'
                ),
        ), $info);
    }

    public function testGetContenu()
    {
        $this->zenMail->setContenuText('foo');
        $this->assertEquals('foo', $this->zenMail->getContenu());
    }

    public function recipientsProvider(): iterable
    {
        yield ['test@example.org', 'test@example.org'];
        yield ['"Test" <test@example.org>', '=?utf-8?B?VGVzdA==?=<test@example.org>'];
        yield ['"Test éé&è" <test@example.org>', '=?utf-8?B?VGVzdCDDqcOpJsOo?=<test@example.org>'];
    }

    /**
     * @dataProvider recipientsProvider
     */
    public function testRecipients(string $recipient, string $expected): void
    {
        $this->zenMail->setDestinataire($recipient);
        $this->assertSame($expected, $this->zenMail->getDestinataire());
    }
}
