<?php

class GetModuleList extends ChoiceActionExecutor
{

    const MODULE_TYPE_FIELD = 'module_type';
    const MODULE_TYPE_LABEL_FIELD = 'module_type_label';
    const PAGE_TITLE = 'page_title';

    /**
     * @return bool
     * @throws RecoverableException
     */
    public function go()
    {
        $moduleType = $this->getRecuperateur()->get(self::MODULE_TYPE_FIELD);
        $moduleList = $this->displayAPI();
        if (empty($moduleList[$moduleType])) {
            throw new RecoverableException("Ce type de dossier n'existe pas");
        }
        $this->getConnecteurProperties()->setData(
            self::MODULE_TYPE_FIELD,
            $moduleType
        );
        $this->getConnecteurProperties()->setData(
            self::MODULE_TYPE_LABEL_FIELD,
            $moduleList[$moduleType]['nom']
        );
        return true;
    }

    public function display()
    {
        $this->moduleType = $this->getConnecteurProperties()->get(self::MODULE_TYPE_FIELD);
        $modules = $this->displayAPI();

        $currentLocale = setlocale(LC_COLLATE, 0);
        setlocale(LC_COLLATE, 'fr_FR.utf8');
        uasort($modules, static function (array $a, array $b) {
            return strcoll($a['nom'], $b['nom']);
        });
        setlocale(LC_COLLATE, $currentLocale);

        $this->moduleList = $modules;
        $this->renderPage(
            self::PAGE_TITLE,
            __DIR__ . '/template/GetModuleList.php'
        );
        return true;
    }

    public function displayAPI()
    {
        return $this->apiGet("/flux", []);
    }
}
