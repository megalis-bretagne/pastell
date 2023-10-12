<?php

declare(strict_types=1);

namespace Pastell\Tests\Service\SimpleTwigRenderer;

use Exception;
use Pastell\Service\SimpleTwigRenderer;
use Pastell\Service\SimpleTwigRenderer\SimpleTwigXpath;
use Pastell\Service\SimpleTwigRenderer\SimpleTwigXpathArray;
use PastellTestCase;
use UnrecoverableException;

class SimpleTwigXpathArrayTest extends PastellTestCase
{
    private string $xml_file;
    private string $method;

    protected function setUp(): void
    {
        parent::setUp();
        $this->xml_file = file_get_contents(__DIR__ . '/test.xml');
        $this->method = SimpleTwigXpathArray::XPATH_ARRAY_FUNCTION;
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

        $expression = "{% set result = $this->method('xml', '/universite/etudiant') %}
        {% for element in result %}
            <p>{{ element.nom }}</p>
            <p>{{ element.matricule }}</p>
            <p>{{ element.age }}</p>
        {% endfor %}";

        self::assertStringContainsStringIgnoringCase('John Doe', $this->twigRenderer()->render($expression, $form));
        self::assertStringContainsStringIgnoringCase('Jane Smith', $this->twigRenderer()->render($expression, $form));
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

        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage('Erreur sur le template');
        $this->twigRenderer()->render($expression, $form);
    }
}
