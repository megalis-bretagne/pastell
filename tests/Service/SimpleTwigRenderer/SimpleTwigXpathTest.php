<?php

declare(strict_types=1);

namespace Pastell\Tests\Service\SimpleTwigRenderer;

use Exception;
use Pastell\Service\SimpleTwigRenderer;
use Pastell\Service\SimpleTwigRenderer\SimpleTwigXpath;
use UnrecoverableException;

class SimpleTwigXpathTest extends \PastellTestCase
{
    private string $xml_file;
    private string $method;

    protected function setUp(): void
    {
        parent::setUp();
        $this->xml_file = file_get_contents(__DIR__ . '/test.xml');
        $this->method = SimpleTwigXpath::XPATH_FUNCTION;
    }

    public function twigRenderer(): SimpleTwigRenderer
    {
        return $this->getObjectInstancier()->getInstance(SimpleTwigRenderer::class);
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testOK(): void
    {
        $form = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();

        $form->addFileFromData(
            'xml',
            'test.xml',
            $this->xml_file
        );

        $method = SimpleTwigXpath::XPATH_FUNCTION;
        $expression = "{{ $method('xml', '/universite/etudiant[1]/nom') }}";

        self::assertSame('John Doe', $this->twigRenderer()->render($expression, $form));
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testError(): void
    {
        $form = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();

        $form->addFileFromData(
            'xml',
            'test.xml',
            $this->xml_file
        );

        $method = SimpleTwigXpath::XPATH_FUNCTION;
        $expression = "{{ $method('xml', '/universite/etudiant/') }}";

        $this->expectException(\UnrecoverableException::class);
        $this->expectExceptionMessage('Erreur sur le template');
        $this->twigRenderer()->render($expression, $form);
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testNull(): void
    {
        $form = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();

        $form->addFileFromData(
            'xml',
            'test.xml',
            $this->xml_file
        );

        $expression = "{{ $this->method ('xml', '/universite/burger') }}";

        self::assertSame('', $this->twigRenderer()->render($expression, $form));
    }
}
