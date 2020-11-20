<?php

require_once __DIR__ . "/../lib/FluxDataTestSedaGenerique.class.php";

class SedaGeneriqueDisplayTestBordereau extends ActionExecutor
{
    /**
     * @return bool
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function go()
    {
        /** @var SedaGenerique $sedaGenerique */
        $sedaGenerique = $this->getMyConnecteur();

        $fluxDataTest = new FluxDataTestSedaGenerique();

        $connecteurConfig = $this->getConnecteurConfig($this->id_ce);

        $data = $connecteurConfig->getFileContent('data');
        $data = json_decode($data, true);

        $file_list = [];
        $files_all = $data['files'] ?? "";
        foreach (explode("\n", $files_all) as $file_line) {
            $files = explode(",", $file_line);
            $file_list[] = trim($files[0]);
        }

        $fluxDataTest->addFileList($file_list);

        $date_list = [];
        if (isset($data['StartDate'])) {
            preg_match("#%(.*)%#", $data['StartDate'], $matches);
            $date_list[] = $matches[1] ?? "";
        }
        if (isset($data['EndDate'])) {
            preg_match("#%(.*)%#", $data['EndDate'], $matches);
            $date_list[] = $matches[1] ?? "";
        }
        $fluxDataTest->addDateList($date_list);

        $fakeDonneesFormulaire = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $sedaGenerique->setDocDonneesFormulaire($fakeDonneesFormulaire);
        $result = $sedaGenerique->getBordereauNG($fluxDataTest);

        if (!$result) {
            $this->setLastMessage($sedaGenerique->getLastValidationError());
            return false;
        }

        header_wrapper("Content-type: text/xml");
        header_wrapper("Content-disposition: inline; filename=bordereau.xml");

        echo $result;
        exit_wrapper();
        return true;
    }
}
