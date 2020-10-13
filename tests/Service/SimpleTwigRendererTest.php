<?php

namespace Pastell\Tests\Service;

use DonneesFormulaireException;
use Pastell\Service\SimpleTwigRenderer;
use PastellTestCase;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

class SimpleTwigRendererTest extends PastellTestCase
{

    public function renderDataProvider()
    {
        return [
            ["",""],
            ["constante","constante"],
            ["foo","{{ variable }}"],
            [
                "Arrêté individuel Bond James (matricule 007)",
                "Arrêté individuel {{ nom_agent }} {{ prenom_agent}} (matricule {{ matricule_agent }})",
            ],
            [
                'foo 12 buz',
                "foo {{ xpath('pes_aller','//EnTetePES/CodBud/@V') }} buz"
            ],
            [
            'foo 19.95 buz',
                "foo {{ jsonpath('test_json','$.store.bicycle.price') }} buz"
            ],
            [
                '',"{{ not_existing_value }}"
            ],
            [
                'foo  bar','foo {{ xpath("pes_aller","//NotExistingPath") }} bar'
            ],
            [
                'foo  bar','foo {{ jsonpath("test_json","$.notExistingPath") }} bar'
            ],
            [
                '','{{ xpath("not_existing_file","//NotExistingPath")}}'
            ],
            [
                '','{{ jsonpath("not_existing_file","$.notExistingPath")}}'
            ]


        ];
    }

    /**
     * @param string $expected_result
     * @param $template
     * @throws DonneesFormulaireException
     * @throws LoaderError
     * @throws SyntaxError
     * @dataProvider renderDataProvider
     */
    public function testRender(string $expected_result, $template)
    {
        $simpleTwigRenderer = new SimpleTwigRenderer();

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $donneesFormulaire->setTabData(
            [
                'nom_agent' => 'Bond',
                'prenom_agent' => 'James',
                'matricule_agent' => '007',
                'variable' => 'foo'
            ]
        );
        $donneesFormulaire->addFileFromCopy(
            'pes_aller',
            'pes.xml',
            __DIR__ . "/fixtures/HELIOS_SIMU_ALR2_1595923133_1646706116.xml"
        );
        $donneesFormulaire->addFileFromCopy(
            'test_json',
            'test_json.json',
            __DIR__ . "/fixtures/test.json"
        );

        $this->assertEquals(
            $expected_result,
            $simpleTwigRenderer->render(
                $template,
                $donneesFormulaire
            )
        );
    }

    /**
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function testRenderWhenNotATwigExpression()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();

        $simpleTwigRenderer = new SimpleTwigRenderer();
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Unclosed "variable"');
        $simpleTwigRenderer->render("{{dsfdsf ", $donneesFormulaire);
    }
}
