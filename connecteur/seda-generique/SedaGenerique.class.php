<?php

use Pastell\Service\SimpleTwigRenderer;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

require_once(PASTELL_PATH . "/connecteur/seda-ng/lib/FluxData.class.php");
require_once(PASTELL_PATH . "/connecteur/seda-ng/lib/FluxDataTest.class.php");

class SedaGenerique extends SEDAConnecteur
{
    /** @var DonneesFormulaire */
    private $connecteurConfig;
    private $curlWrapperFactory;

    private const SEDA_GENERATOR_GENERATE_PATH = "/generate";
    private const SEDA_GENERATOR_GENERATE_PATH_WITH_TEMPLATE = "/generateWithTemplate";

    public function __construct(CurlWrapperFactory $curlWrapperFactory)
    {
        $this->curlWrapperFactory = $curlWrapperFactory;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    /**
     * @param array $transactionsInfo
     * @return string|void
     * @throws UnrecoverableException
     */
    public function getBordereau(array $transactionsInfo)
    {
        throw new UnrecoverableException("Le connecteur SEDA n'est pas supporté par ce flux...");
    }

    public static function getPastellToSeda()
    {
        $result = [
            'version' => [
                'seda' => 'version',
                'libelle' => 'Version du SEDA',
                'value' => ['1.0','2.1']
            ],
            'archival_agency_identifier' => [
                'seda' => 'ArchivalAgency.Identifier',
                'libelle' => "Identifiant du service d'archive",
            ],
            'archival_agency_name' => [
                'seda' => 'ArchivalAgency.Name',
                'libelle' => "Nom du service d'archive",
            ],
            'transferring_agency_identifier' => [
                'seda' => 'TransferringAgency.Identifier',
                'libelle' => "Identifiant du service versant",
            ],
            'transferring_agency_name' => [
                'seda' => 'TransferringAgency.Name',
                'libelle' => "Nom du service versant",
            ],
            'originating_agency_identifier' => [
                'seda' => 'OriginatingAgency.Identifier',
                'libelle' => "Identifiant du service producteur",
            ],
            'originating_agency_name' => [
                'seda' => 'OriginatingAgency.Name',
                'libelle' => "Nom du service producteur",
            ],

            'commentaire' => [
                'seda' => 'Comment',
                'libelle' => 'Commentaire',
            ],
            'titre' => [
                'seda' => 'Title',
                'libelle' => 'Titre',
            ],
            'archival_agreement' => [
                'seda' => 'ArchivalAgreement',
                'libelle' => 'Accord de versement'
            ],
            'ArchivalProfile' => [
                'seda' => 'ArchivalProfile',
                'libelle' => "Profil d'archivage"
            ],
            'CustodialHistory' => [
                'seda' => 'CustodialHistory',
                'libelle' => "Historique de conservation"
            ],
            'Language' => [
                'seda' => 'Language',
                'libelle' => "Langue du contenu"
            ],
            'DescriptionLanguage' => [
                'seda' => 'DescriptionLanguage',
                'libelle' => "Langue du la description"
            ],
            'StartDate' => [
                'seda' => 'StartDate',
                'libelle' => "Date de début (Y-m-d)"
            ],
            'EndDate' => [
                'seda' => 'EndDate',
                'libelle' => "Date de fin (Y-m-d)"
            ],
            'AccessRule' => [
                'seda' => 'AccessRule',
                'libelle' => "Règle de restriction d'accès",
            ],
            'AppraisalRule_Rule' => [
                'seda' => 'AppraisalRule.Rule',
                'libelle' => "Règle du sort final ",
                'commentaire' => "Encoder en xsd:duration, voir http://www.datypic.com/sc/xsd/t-xsd_duration.html"
            ],
            'AppraisalRule_FinalAction' => [
                'seda' => 'AppraisalRule.FinalAction',
                'libelle' => "Action finale",
                'value' => ['Conserver','Détruire']
            ],
        ];

        foreach (range(38, 62) as $nb) {
            $result['AccessRule']['value'][] = "AR0$nb";
        }

        return $result;
    }

    /**
     * @param array $data_file_content
     * @param FluxData $fluxData
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getInputDataElement(array $data_file_content, FluxData $fluxData)
    {
        $data = [];
        foreach (self::getPastellToSeda() as $pastell_id => $element_info) {
            if (empty($data_file_content[$pastell_id])) {
                continue;
            }
            $element_id_list = explode(".", $element_info['seda']);
            $the_data = &$data;
            foreach ($element_id_list as $i => $element_id) {
                if ($i < count($element_id_list) - 1) {
                    if (!isset($the_data[$element_id])) {
                        $the_data[$element_id] = [];
                    }
                    $the_data = &$the_data[$element_id];
                } else {
                    $the_data[$element_id] = $this->getStringWithMetatadaReplacement(
                        $fluxData,
                        $data_file_content[$pastell_id]
                    );
                }
            }
        }
        return $data;
    }

    /**
     * @param string $keywords_data
     * @param FluxData $fluxData
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getInputDataKewords(string $keywords_data, FluxData $fluxData)
    {
        $result = [];
        $keywords = explode("\n", $keywords_data);
        foreach ($keywords as $keyword_line) {
            $seda_keywords = [];
            $keyword_line = trim($keyword_line);
            if (! $keyword_line) {
                continue;
            }
            $keyword_properties = explode(",", $keyword_line, 3);
            $seda_keywords["KeywordContent"] =  $this->getStringWithMetatadaReplacement($fluxData, $keyword_properties[0]);
            if (! empty($keyword_properties[1])) {
                $seda_keywords["KeywordReference"] = $this->getStringWithMetatadaReplacement($fluxData, $keyword_properties[1]);
            }
            if (! empty($keyword_properties[2])) {
                $seda_keywords["KeywordType"] = $this->getStringWithMetatadaReplacement($fluxData, $keyword_properties[2]);
            }
            $result[] = $seda_keywords;
        }
        return $result;
    }

    private function getInputDataFiles(string $files_data, FluxData $fluxData)
    {
        $result = [];
        $files = explode("\n", $files_data);
        foreach ($files as $file_line) {
            $seda_archive_units = [];
            $file_line = trim($file_line);
            if (! $file_line) {
                continue;
            }
            $file_properties = explode(",", $file_line, 2);

            $file_id = $file_properties[0];
            if (! empty($file_properties[1])) {
                $seda_archive_units['Title'] = trim($file_properties[1]);
            }
            $seda_archive_units['Id'] = "id_" . $file_id;

            if ($fluxData->getData($file_id)) {
                foreach ($fluxData->getData($file_id) as $filenum => $filename) {
                    $file_unit = [];
                    $file_unit['Filename'] = $filename;
                    $file_unit['MessageDigest'] = $fluxData->getFileSHA256($file_id);
                    $file_unit['Size'] = $fluxData->getFilesize($file_id);
                    $file_unit['MimeType'] = $fluxData->getContentType($file_id);
                    $seda_archive_units['Files']['id_' . $file_id . "_" . $filenum] = $file_unit;
                    $fluxData->setFileList($file_id, $filename, $fluxData->getFilePath($file_id));
                }
            }
            $result[] = $seda_archive_units;
        }
        return $result;
    }

    /**
     * @param FluxData $fluxData
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getInputData(FluxData $fluxData): array
    {
        $data_file_content = json_decode($this->connecteurConfig->getFileContent('data'), true);
        if (!$data_file_content) {
            $data_file_content = [];
        }

        $data = $this->getInputDataElement($data_file_content, $fluxData);
        $data['Keywords'] = $this->getInputDataKewords($data_file_content['keywords'] ?? "", $fluxData);
        $data['ArchiveUnits'] = $this->getInputDataFiles($data_file_content['files'] ?? "", $fluxData);

        $appraisailRuleFinalAction = [
            '1.0' => [
                'Conserver' => 'conserver',
                'Détruire' => 'detruire'
            ],
            '2.1' => [
                'Conserver' => 'Keep',
                'Détruire' => 'Destroy'
            ],
        ];
        if (! empty($data['AppraisalRule']['FinalAction'])) {
            $data['AppraisalRule']['FinalAction'] = $appraisailRuleFinalAction[$data['version'] ?? '2.1'][$data['AppraisalRule']['FinalAction']];
        }
        return $data;
    }

    /**
     * @param FluxData $fluxData
     * @param $expression
     * @return string
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getStringWithMetatadaReplacement(FluxData $fluxData, $expression)
    {
        $simpleTwigRenderer = new SimpleTwigRenderer();
        return $simpleTwigRenderer->render(
            $expression,
            $this->getDocDonneesFormulaire()
        );
    }

    /**
     * @param FluxData $fluxData
     * @return bool|string
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function getBordereauNG(FluxData $fluxData)
    {
        $curlWrapper = $this->curlWrapperFactory->getInstance();

        if ($this->connecteurConfig->get('template')) {
            $curlWrapper->addPostFile('template', $this->connecteurConfig->getFilePath('template'));
            $data = $this->getInputData($fluxData);
            $tmpFolder = new TmpFolder();
            $tmp_folder = $tmpFolder->create();
            file_put_contents($tmp_folder . "/data.json", json_encode($data));
            $curlWrapper->addPostFile('json_data', $tmp_folder . "/data.json");
            $url = sprintf(
                "%s%s",
                $this->connecteurConfig->get('seda_generator_url'),
                self::SEDA_GENERATOR_GENERATE_PATH_WITH_TEMPLATE
            );
        } else {
            $curlWrapper->setJsonPostData(
                $this->getInputData($fluxData),
                0
            );

            $url = sprintf(
                "%s%s",
                $this->connecteurConfig->get('seda_generator_url'),
                self::SEDA_GENERATOR_GENERATE_PATH
            );
        }

        $result = $curlWrapper->get($url);
        if (! $result) {
            throw new UnrecoverableException($curlWrapper->getLastError());
        }
        if (json_decode($result, true)) {
            $json = json_decode($result, true);
            if (isset($json['message'])) {
                throw new UnrecoverableException($json['message']);
            }
        }

        return $result;
    }

    public function validateBordereau(string $bordereau)
    {
        return true;
    }

    public function getLastValidationError()
    {
        return false;
    }

    /**
     * @param FluxData $fluxData
     * @param string $archive_path
     * @return false|void
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function generateArchive(FluxData $fluxData, string $archive_path)
    {
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        $files_list = "";

        foreach ($fluxData->getFilelist() as $file_id) {
            $filename = $file_id['filename'];
            $filepath = $file_id['filepath'];

            if (! $filepath) {
                break;
            }
            $dirname = dirname($tmp_folder . "/" . $filename);
            if (! file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }
            copy($filepath, "$tmp_folder/$filename");
            $files_list .= escapeshellarg($filename) . " ";
        }

        $command = "tar cvzf $archive_path --directory $tmp_folder -- $files_list 2>&1";

        exec($command, $output, $return_var);

        if ($return_var != 0) {
            $output = implode("\n", $output);
            throw new UnrecoverableException("Impossible de créer le fichier d'archive $archive_path - status : $return_var - output: $output");
        }

        $tmpFolder->delete($tmp_folder);
    }
}
