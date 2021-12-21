<?php

class PdfGeneriqueRelanceConnecteur extends Connecteur
{
    public const DEFAULT_NB_DAY_RELANCE = 30;
    public const DEFAULT_NB_DAY_NEXT_STATE = 60;

    /** @var  DonneesFormulaire */
    private $connecteurConfig;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    public function mustRelance($first_mail_date)
    {
        return time() > $this->geTimeRelance($first_mail_date);
    }

    public function mustGoToNextState($first_mail_date)
    {
        return time() > $this->geTimeNextState($first_mail_date);
    }

    public function getDateRelance($first_mail_date)
    {
        return date("Y-m-d H:i:s", $this->geTimeRelance($first_mail_date));
    }

    public function getDateNextState($first_mail_date)
    {
        return date("Y-m-d H:i:s", $this->geTimeNextState($first_mail_date));
    }

    public function geTimeRelance($first_mail_date)
    {
        return strtotime($first_mail_date) +
            ($this->connecteurConfig->get('nb_day_relance') ?: self::DEFAULT_NB_DAY_RELANCE) * 86400;
    }

    public function geTimeNextState($first_mail_date)
    {
        return strtotime($first_mail_date) +
            ($this->connecteurConfig->get('nb_day_next_state') ?: self::DEFAULT_NB_DAY_NEXT_STATE) * 86400;
    }
}
