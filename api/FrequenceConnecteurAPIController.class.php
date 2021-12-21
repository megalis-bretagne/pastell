<?php

class FrequenceConnecteurAPIController extends BaseAPIController
{
    private $connecteurFrequenceSQL;

    public function __construct(ConnecteurFrequenceSQL $connecteurFrequenceSQL)
    {
        $this->connecteurFrequenceSQL = $connecteurFrequenceSQL;
    }

    /**
     * @return array|bool|mixed
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function get()
    {
        $this->checkDroit(0, "system:lecture");

        $id_cf = $this->getFromQueryArgs(0);
        if ($id_cf) {
            return $this->detail($id_cf);
        }

        return $this->connecteurFrequenceSQL->getAllArray();
    }


    /**
     * @param $id_cf
     * @return array|bool|mixed
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function detail($id_cf)
    {
        $this->checkDroit(0, "system:lecture");
        $result = $this->connecteurFrequenceSQL->getInfo($id_cf);
        if (! $result) {
            throw new NotFoundException("Cette fréquence de connecteur n'existe pas");
        }
        return $result;
    }

    /**
     * @return array|bool|mixed
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function post()
    {
        $this->checkDroit(0, "system:edition");
        $recuperateur = new Recuperateur($this->getRequest());
        $connecteurFrequence = new ConnecteurFrequence($recuperateur->getAll());
        $id_cf = $this->connecteurFrequenceSQL->edit($connecteurFrequence);
        return $this->detail($id_cf);
    }

    /**
     * @return array|bool|mixed
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function patch()
    {
        $this->checkDroit(0, "system:edition");
        $id_cf = $this->getFromQueryArgs(0);
        $recuperateur = new Recuperateur($this->getRequest());
        $connecteurFrequence = new ConnecteurFrequence($recuperateur->getAll());
        $connecteurFrequence->id_cf = $id_cf;
        $this->connecteurFrequenceSQL->edit($connecteurFrequence);
        return $this->detail($id_cf);
    }

    /**
     * @throws ForbiddenException
     */
    public function delete()
    {
        $this->checkDroit(0, "system:edition");
        $id_cf = $this->getFromQueryArgs(0);
        $this->connecteurFrequenceSQL->delete($id_cf);
        $result['result'] = BaseAPIController::RESULT_OK;
        return $result;
    }
}
