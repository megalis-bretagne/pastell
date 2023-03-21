<?php

class GetModuleList extends ConnecteurTypeChoiceActionExecutor
{
    private const MODULE_TYPE_FIELD = 'module_type';
    private const MODULE_TYPE_LABEL_FIELD = 'module_type_label';
    private const PAGE_TITLE = 'page_title';

    /**
     * @return bool
     * @throws RecoverableException
     */
    public function go()
    {
        $moduleType = $this->getRecuperateur()->get(self::MODULE_TYPE_FIELD);
        $moduleList = $this->displayAPI();
        if ($moduleType && empty($moduleList[$moduleType])) {
            throw new RecoverableException("Ce type de dossier n'existe pas");
        }
        $this->getConnecteurProperties()->setData(
            $this->getMappingValue(self::MODULE_TYPE_FIELD),
            $moduleType
        );
        $this->getConnecteurProperties()->setData(
            $this->getMappingValue(self::MODULE_TYPE_LABEL_FIELD),
            $moduleList[$moduleType]['nom']
        );
        return true;
    }

    public function display()
    {
        $this->setViewParameter(
            'moduleType',
            $this->getConnecteurProperties()->get($this->getMappingValue(self::MODULE_TYPE_FIELD))
        );
        $modules = $this->displayAPI();

        $currentLocale = setlocale(LC_COLLATE, '0');
        setlocale(LC_COLLATE, 'fr_FR.utf8');
        uasort($modules, static function (array $a, array $b) {
            return strcoll($a['nom'], $b['nom']);
        });
        setlocale(LC_COLLATE, $currentLocale);

        $this->setViewParameter('moduleList', $modules);
        $this->renderPage(
            $this->getMappingValue(self::PAGE_TITLE),
            __DIR__ . '/template/GetModuleList.php'
        );
        return true;
    }

    public function displayAPI()
    {
        return $this->apiGet("/flux", []);
    }
}
