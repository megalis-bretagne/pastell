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
            ],
            [
                'Durand','{{ csvpath("test_csv_with_comma",1,1) }}'
            ],
            [
                'Durand','{{ csvpath("test_csv_with_semicolon",1,1,";") }}'
            ],
            [
                'Michel;Michele','{{ csvpath("test_csv_with_comma",0,3) }}'
            ],
            [
                '','{{ csvpath("test_csv_with_comma",42,0) }}'
            ],
            [
                '','{{ csvpath("test_csv_with_comma",0,42) }}'
            ],
            'csv_path_with_not_existing_file' => [
                '','{{ csvpath("not_existing_file",1,2) }}'
            ],
            'csvpath_in_expression' => [
                'true','{% if (csvpath("test_csv_with_semicolon",0,1,";")  == "Michel") %}true{% else %}false{% endif %}'
            ],
            'csvpath_in_false_expression' => [
                'false','{% if (csvpath("test_csv_with_semicolon",0,1,";")  == "Jean-Pierre") %}true{% else %}false{% endif %}'
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

        $donneesFormulaire->addFileFromCopy(
            'test_csv_with_comma',
            'test_csv_with_comma.csv',
            __DIR__ . "/fixtures/test-with-coma.csv"
        );

        $donneesFormulaire->addFileFromCopy(
            'test_csv_with_semicolon',
            'test_csv_with_semicolon.csv',
            __DIR__ . "/fixtures/test-with-semicolon.csv"
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
