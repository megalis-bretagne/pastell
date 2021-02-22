<?php

namespace Pastell\Tests\Service;

use DonneesFormulaireException;
use Pastell\Service\SimpleTwigRenderer;
use Pastell\Service\SimpleTwigRendererExemple;
use PastellTestCase;
use TransformationGenerique;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use UnrecoverableException;

class SimpleTwigRendererTest extends PastellTestCase
{

    public function renderDataProvider()
    {
        $xpath = '//*[local-name()="ActeRecu"]/@*[local-name()="Date"]';

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
            ],
            'xpath_with_namespaces' => [
                '2017-12-07',"{{ xpath( 'aractes' , '$xpath' ) }}"
            ],
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
        $donneesFormulaire->addFileFromCopy(
            'aractes',
            'aractes.xml',
            __DIR__ . "/fixtures/aractes.xml"
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
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage('Erreur sur le template {{dsfdsf  : Unclosed "variable"');
        $simpleTwigRenderer->render("{{dsfdsf ", $donneesFormulaire);
    }

    public function testRenderWhenNotAXPathExpression()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $donneesFormulaire->addFileFromCopy(
            'pes_aller',
            'pes.xml',
            __DIR__ . "/fixtures/HELIOS_SIMU_ALR2_1595923133_1646706116.xml"
        );
        $simpleTwigRenderer = new SimpleTwigRenderer();
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage('Expression xpath incorrect');
        $simpleTwigRenderer->render("{{ xpath('pes_aller','/////EnTetePES/CodBud/@V') }}", $donneesFormulaire);
    }

    public function testXPathOnNonXMLFile()
    {
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $donneesFormulaire->addFileFromData(
            'pes_aller',
            'pes.xml',
            "toto"
        );
        $simpleTwigRenderer = new SimpleTwigRenderer();
        $this->expectException(UnrecoverableException::class);
        $this->expectExceptionMessage("Le fichier pes_aller n'est pas un fichier XML");
        $simpleTwigRenderer->render("{{ xpath('pes_aller','//EnTetePES/CodBud/@V') }}", $donneesFormulaire);
    }

    public function testRenderWithFormulaire()
    {
        $id_d = $this->createDocument('actes-generique')['id_d'];

        $template1 = "Conseil municipal de la ville TRUC - Titre : {{ titre }} - Date : {{ date_de_lacte }} - select {{ acte_nature }}";

        $this->configureDocument($id_d, [
            'titre' => "toto",
            'acte_nature' => '3',
            'date_de_lacte' => '2020-12-25',
            'classification' => '8.2',
            'objet' => $template1
        ]);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $simpleTwigRenderer = new SimpleTwigRenderer();
        $this->assertEquals(
            3,
            $simpleTwigRenderer->render("{{ acte_nature }}", $donneesFormulaire)
        );
        $this->assertEquals(
            "Actes individuels",
            $simpleTwigRenderer->render("{{ select_value('acte_nature') }}", $donneesFormulaire)
        );

        $this->assertEquals(
            '2020-12-25',
            $simpleTwigRenderer->render("{{ date_de_lacte }}", $donneesFormulaire)
        );
        $this->assertEquals(
            "Conseil municipal de la ville TRUC - Titre :  - Date : 2020-12-25 - select 3",
            $simpleTwigRenderer->render($donneesFormulaire->get('objet'), $donneesFormulaire)
        );
    }

    public function exempleProvider()
    {
        $simpleTwigRendererExemple = new SimpleTwigRendererExemple();
        return  $simpleTwigRendererExemple->getExemple();
    }

    /**
     * @dataProvider exempleProvider
     */
    public function testExemple(string $expression, string $explication, array $data)
    {
            $donneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
            $donneesFormulaire->setTabData($data[0]);
            $simpleTwigRenderer = new SimpleTwigRenderer();
            $this->assertEquals(
                $data[1],
                $simpleTwigRenderer->render($expression, $donneesFormulaire)
            );
    }
}
