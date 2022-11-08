<?php

class PieceMarcheParEtapeTypePieceTest extends PastellMarcheTestCase
{
    private const FILENAME_PIECE1 = "2018BPU.pdf";
    private const FILENAME_PIECE2 = "2018CCAP.pdf";

    private $id_d;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $result = $this->getInternalAPI()->post(
            "/Document/" . PastellTestCase::ID_E_COL,
            ['type' => 'piece-marche-par-etape']
        );
        $this->id_d = $result['id_d'];
    }

    /**
     * @throws Exception
     */
    private function postPiecesLot()
    {

        $this->getInternalAPI()->patch(
            "/entite/1/document/$this->id_d",
            [
            'libelle' => 'Test marché numéro 2018REF201810',
                'numero_marche' => '2018REF201810',
                'type_marche' => 'T',
                'numero_consultation' => 'Consultation 2018REF201810',
                'type_consultation' => 'MAPA',
                'etape' => 'ONR',
                'soumissionnaire' => 'entreprise xx',
                'date_document' => '2018-10-05',
            ]
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($this->id_d);
        $donneesFormulaire->addFileFromCopy('piece', self::FILENAME_PIECE1, __DIR__ . "/../fixtures/" . self::FILENAME_PIECE1, 0);
        $donneesFormulaire->addFileFromCopy('piece', self::FILENAME_PIECE2, __DIR__ . "/../fixtures/" . self::FILENAME_PIECE2, 1);
    }

    /**
     * @throws Exception
     */
    public function testDisplayAPI()
    {

        $this->postPiecesLot();
        $info = $this->getInternalAPI()->get("/entite/1/document/$this->id_d/externalData/type_piece");

        $expected =  [
            'pieces_type_pj_list' =>
                 [
                    "ARN" => "Accusé de Réception de Notification (ARN)",
                    "AE" => "Acte d'Engagement (AE)",
                    "AS" => "Acte de sous-traitance (AS)",
                    "AN" => "Annexes (AN)",
                    "AL" => "Annonces Légales (AL)",
                    "AU" => "Autres (AU)",
                    "APD" => "Avant Projet Détaillé (APD)",
                    "APS" => "Avant Projet Sommaire (APS)",
                    "AV" => "Avenant (AV)",
                    "AC" => "Avis d'appel à la Concurrence (AC)",
                    "BC" => "Bon de commande (BC)",
                    "BPU" => "Bordereau des Prix Unitaires (BPU)",
                    "CCAP" => "Cahier des Clauses Administratives Particulières (CCAP)",
                    "CCTP" => "Cahier des Clauses Techniques Particulières (CCTP)",
                    "CA" => "Courrier d'Attribution (CA)",
                    "CM" => "Courrier marché générique (CM)",
                    "CN" => "Courrier de notification (CN)",
                    "DS" => "Déclaration sans suite (DS)",
                    "DPGF" => "Décomposition du Prix Global et Forfaitaire (DPGF)",
                    "DG" => "Décompte général et définitif (DG)",
                    "DQE" => "Détail Quantitatif Estimatif (DQE)",
                    "DR" => "Dossier de Réponse (DR)",
                    "EC" => "Échange en cours de Consultation (EC)",
                    "EA" => "Etat d'acompte (EA)",
                    "E" => "Étude (E)",
                    "LR" => "Lettre de Rejet (LR)",
                    "LC" => "Liste des Candidatures (LC)",
                    "MP" => "Mise au point (MP)",
                    "OS" => "Ordre de service (OS)",
                    "P" => "Programme (P)",
                    "AO" => "Rapport d'Analyse des Offres (A0)",
                    "CAO" => "Rapport de Commission d'Appel d'Offres (CAO)",
                    "COP" => "Rapport de Commission d'Ouverture des Plis (COP)",
                    "RP" => "Rapport de Présentation (RP)",
                    "RDP" => "Récépissé de dépôt de pli (RDP)",
                    "RC" => "Règlement de la Consultation (RC)"
                ],
            'pieces' =>
                 [
                    0 => self::FILENAME_PIECE1,
                    1 => self::FILENAME_PIECE2,
                ],
        ];

        $this->assertEquals($expected, $info);
    }


    /**
     * @throws Exception
     */
    public function testGo()
    {

        $this->postPiecesLot();
        $info = $this->getInternalAPI()->patch("/entite/1/document/$this->id_d/externalData/type_piece", ['type_pj' => ['BPU','CCAP']]);
        $this->assertEquals('2018BPU.pdf : Bordereau des Prix Unitaires (BPU) ; 
2018CCAP.pdf : Cahier des Clauses Administratives Particulières (CCAP)', $info['data']['type_piece']);
    }
}
