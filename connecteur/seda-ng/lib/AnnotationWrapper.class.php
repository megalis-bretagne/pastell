<?php

class AnnotationWrapper
{
    public const SHA256_URI = "http://www.w3.org/2001/04/xmlenc#sha256";

    private const DEFAULT_FILENAME_TRANSLIT_REGEXP = '/[^\x20-\x7E]/';

    private $connecteurInfo = array();
    private $compteur_jour;

    private $array_index = array();

    /** @var  FluxData */
    private $fluxData;

    private $translitFilenameRegExp = self::DEFAULT_FILENAME_TRANSLIT_REGEXP;

    public function setConnecteurInfo(array $connecteurInfo)
    {
        $this->connecteurInfo = $connecteurInfo;
    }

    public function setCompteurJour($compteur_jour)
    {
        $this->compteur_jour = $compteur_jour;
    }

    public function setFluxData(FluxData $fluxData)
    {
        $this->fluxData = $fluxData;
    }

    public function setTranslitFilenameRegExp(string $translitFilenameRegExp)
    {
        $this->translitFilenameRegExp = $translitFilenameRegExp ?: self::DEFAULT_FILENAME_TRANSLIT_REGEXP;
    }

    public function getCommand($string)
    {
        $info = $this->extractInfo($string);
        if (empty($info[0][0])) {
            return false;
        }
        return $info[0][0];
    }

    public function getNbRepeat($string)
    {
        $info = $this->extractInfo($string);
        if (empty($info[0][0])) {
            return false;
        }
        if ($info[0][0] != 'repeat') {
            return false;
        };

        $data = $this->fluxData->getData($info[0][1]);

        if (is_array($data)) {
            return  count($data);
        }

        return $data ? 1 : 0;
    }


    public function testIf($string)
    {
        $info = $this->extractInfo($string);
        if (empty($info[0][0])) {
            return false;
        }
        if ($info[0][0] != 'if') {
            return false;
        };

        return (bool) $this->fluxData->getData($info[0][1]);
    }



    public function extractInfo($string)
    {
        $result = array();
        preg_match_all("#{{pastell:([^:]*):?((?:(?!}}).)*)}}#", $string, $matches);
        foreach ($matches[0] as $i => $one_match) {
            $command = $matches[1][$i];
            $data = $matches[2][$i];
            $result[] = array($command,$data);
        }
        return $result;
    }

    /**
     * @param $string
     * @return AnnotationReturn
     * @throws Exception
     */
    public function wrap($string)
    {
        $command_list = $this->extractInfo($string);

        $return = $this->getAnnotationReturn(AnnotationReturn::EMPTY_RETURN);
        foreach ($command_list as $command_info) {
            list($command,$data) = $command_info;
            $function = "{$command}Command";

            if (!method_exists($this, $function)) {
                throw new Exception("La commande « $command » est inconnue sur ce Pastell");
            }

            /** @var AnnotationReturn $return_i */
            $return_i =  $this->$function($data);
            $return->type = $return_i->type;
            $return->data = $return_i->data;
            $return->node_attributes = $return_i->node_attributes;
            if (is_array($return_i->string)) {
                //TODO pb des file multiple a traiter avec une commande repeat
                $return_i->string = $return_i->string[0];
            }
            $return->string .= $return_i->string;
        }
        return $return;
    }

    protected function getAnnotationReturn($type, $data = false, $node_attributes = array())
    {
        $return = new AnnotationReturn();
        $return->type = $type;
        $return->string = $data;
        $return->node_attributes = $node_attributes;
        return $return;
    }

    protected function nowCommand($format)
    {
        return $this->getAnnotationReturn(
            AnnotationReturn::STRING,
            date($format ?: 'c')
        );
    }

    protected function timestampCommand()
    {
        return $this->getAnnotationReturn(AnnotationReturn::STRING, time());
    }

    protected function stringCommand($data)
    {
        return $this->getAnnotationReturn(AnnotationReturn::STRING, $data);
    }

    protected function integrityCommand()
    {
        $data = "";
        foreach ($this->fluxData->getFilelist() as $file_id) {
            $integrityXML = new SimpleXMLElement("<Integrity></Integrity>");
            $containsXML = $integrityXML->addChild('Contains', $this->fluxData->getFileSHA256($file_id['key']));
            $containsXML->addAttribute('algorithme', "http://www.w3.org/2001/04/xmlenc#sha256");
            $integrityXML->addChild('UnitIdentifier', $file_id['filename']);
            $dom = dom_import_simplexml($integrityXML);
            $data .= $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
        }

        return $this->getAnnotationReturn(AnnotationReturn::XML_REPLACE, $data);
    }

    protected function sha256Command($data)
    {
        $sha256 = $this->fluxData->getFileSHA256($data);

        $node_attributes = array(
            'algorithme' => self::SHA256_URI
        );

        return $this->getAnnotationReturn(AnnotationReturn::STRING, $sha256, $node_attributes);
    }

    protected function connecteurCommand($data)
    {
        return $this->getStringInfo($this->connecteurInfo, $data);
    }

    protected function fluxCommand($data)
    {
        $value = $this->fluxData->getData($data);
        return $this->getAnnotationReturn(AnnotationReturn::STRING, $value);
    }

    protected function compteurJourCommand()
    {
        return $this->getAnnotationReturn(AnnotationReturn::STRING, $this->compteur_jour);
    }

    protected function getStringInfo(array $source, $key)
    {
        if (isset($source[$key])) {
            $value = $source[$key];
        } else {
            $value = "";
        }
        return $this->getAnnotationReturn(AnnotationReturn::STRING, $value);
    }

    protected function fileCommand($data)
    {
        $value = $this->fluxData->getFilename($data);
        $value = preg_replace($this->translitFilenameRegExp, '-', $value);
        if (empty($value)) {
            return $this->getAnnotationReturn(AnnotationReturn::EMPTY_RETURN);
        }
        $filepath = $this->fluxData->getFilepath($data);
        $this->fluxData->setFileList($data, $value, $filepath);
        $annotationReturn = $this->getAnnotationReturn(AnnotationReturn::ATTACHMENT_INFO, $value);
        $annotationReturn->data = array('content-type' => $this->fluxData->getContentType($data));
        return $annotationReturn;
    }

    public function arrayCommand($data)
    {
        if (! isset($this->array_index[$data])) {
            $this->array_index[$data] = 0;
        }
        $array = $this->fluxData->getData($data);
        if (empty($array)) {
            $this->getAnnotationReturn(AnnotationReturn::EMPTY_RETURN);
        }
        if (! is_array($array)) {
            return $this->getAnnotationReturn(AnnotationReturn::STRING, $array);
        }
        $result =  $this->getAnnotationReturn(AnnotationReturn::STRING, $array[$this->array_index[$data]]);
        $this->array_index[$data]++;

        return $result;
    }

    public function repeatCommand()
    {
        return $this->getAnnotationReturn(AnnotationReturn::EMPTY_RETURN);
    }

    public function connecteurInfoCommand()
    {
        return $this->getAnnotationReturn(AnnotationReturn::STRING, "");
    }

    public function sizeCommand($data)
    {
        return $this->getAnnotationReturn(AnnotationReturn::STRING, $this->fluxData->getFilesize($data));
    }

    /**
     * @param $data
     * @throws Exception
     */
    public function extractZipCommand($data)
    {
        $info = $this->extractInfo($data);
        if (empty($info[0][1])) {
            throw new Exception("Impossible d'appliquer la commande extract_zip pour l'annotation $data");
        }
        $this->fluxData->addZipToExtract($info[0][1]);
    }
}
