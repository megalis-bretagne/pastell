<?php

class FrequenceConnecteurAPIController extends BaseAPIController
{
    public function __construct(private readonly ConnecteurFrequenceSQL $connecteurFrequenceSQL)
    {
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function get(): array
    {
        $this->checkDroit(0, 'system:lecture');

        $id_cf = $this->getFromQueryArgs(0);
        if ($id_cf) {
            return $this->detail($id_cf);
        }

        $frequencies = $this->connecteurFrequenceSQL->getAllArray();
        foreach ($frequencies as &$frequency) {
            $frequency['id_cf'] = (string)$frequency['id_cf'];
            $frequency['id_ce'] = (string)$frequency['id_ce'];
        }
        return $frequencies;
    }


    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function detail($id_cf): array
    {
        $this->checkDroit(0, 'system:lecture');
        $result = $this->connecteurFrequenceSQL->getInfo($id_cf);
        if (!$result) {
            throw new NotFoundException("Cette fréquence de connecteur n'existe pas");
        }
        $result['id_cf'] = (string)$result['id_cf'];
        $result['id_ce'] = (string)$result['id_ce'];
        return $result;
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function post(): array
    {
        $this->checkDroit(0, 'system:edition');
        $recuperateur = new Recuperateur($this->getRequest());
        $connecteurFrequence = new ConnecteurFrequence($recuperateur->getAll());
        $id_cf = $this->connecteurFrequenceSQL->edit($connecteurFrequence);
        return $this->detail($id_cf);
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function patch(): array
    {
        $this->checkDroit(0, 'system:edition');
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
    public function delete(): array
    {
        $this->checkDroit(0, 'system:edition');
        $id_cf = $this->getFromQueryArgs(0);
        $this->connecteurFrequenceSQL->delete($id_cf);
        $result['result'] = BaseAPIController::RESULT_OK;
        return $result;
    }
}
