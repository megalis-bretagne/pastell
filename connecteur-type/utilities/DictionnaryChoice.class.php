<?php

abstract class DictionnaryChoice extends ChoiceActionExecutor
{
    abstract public function getElementId(): string;

    abstract public function getElementName(): string;

    abstract public function getTitle(): string;

    /**
     * @throws RecoverableException
     */
    public function go()
    {
        $element_id = $this->getRecuperateur()->get($this->getElementId());
        $dictionnary = $this->displayAPI();
        if (! isset($dictionnary[$element_id])) {
            throw new RecoverableException("Cet élément n'existe pas");
        }
        $emementName = $dictionnary[$element_id];
        $this->getConnecteurProperties()->setData($this->getElementName(), $emementName);
        $this->getConnecteurProperties()->setData($this->getElementId(), $element_id);
        return true;
    }

    public function display()
    {
        $this->setViewParameter('dictionnary', $this->displayAPI());
        $this->setViewParameter('element_id', $this->getElementId());
        $this->setViewParameter('selected_id', $this->getConnecteurProperties()->get($this->getElementId()));
        $this->renderPage(
            $this->getTitle(),
            __DIR__ . '/template/DictionnaryChoice.php'
        );
        return true;
    }
}
