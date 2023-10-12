<?php

declare(strict_types=1);

namespace Pastell\Tests\Service\SimpleTwigRenderer;

use Exception;
use Pastell\Service\SimpleTwigRenderer;
use Pastell\Service\SimpleTwigRenderer\SimpleTwigJsonPathArray;
use Pastell\Service\SimpleTwigRenderer\SimpleTwigXpath;
use PastellTestCase;
use UnrecoverableException;

class SimpleTwigJsonPathArrayTest extends PastellTestCase
{
    private string $xml_file;
    private string $method;

    protected function setUp(): void
    {
        parent::setUp();
        $this->xml_file = file_get_contents(__DIR__ . '/test.json');
        $this->method = SimpleTwigJsonPathArray::JSONPATH_ARRAY_FUNCTION;
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
        $expression = "
        {% for element in $this->method('json', '$.phoneNumbers') %}
            {% for phoneNumber in element %}
                <p>Type: {{ phoneNumber.type }}</p>
                <p>Number: {{ phoneNumber.number }}</p>
                <hr>
            {% endfor %}
        {% endfor %}";
        self::assertStringContainsStringIgnoringCase('iPhone', $this->twigRenderer()->render($expression, $form));
        self::assertStringContainsStringIgnoringCase('home', $this->twigRenderer()->render($expression, $form));
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
