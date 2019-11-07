<?php
class Job
{

    const TYPE_DOCUMENT = 1;
    const TYPE_CONNECTEUR = 2;
    const TYPE_TRAITEMENT_LOT = 3;

    const MAX_LAST_MESSAGE_LENGTH = 1024;

    public $type;
    public $id_e;
    public $id_d;
    public $id_u;
    public $etat_source;
    public $etat_cible;
    public $last_message;
    public $lock;
    public $id_verrou;
    public $is_lock;

    public $nb_try;
    public $first_try;
    public $last_try;
    public $next_try;

    public $id_job;


    public function __construct()
    {
        $this->id_u = 0;
        $this->id_d = "";
        $this->id_e = 0;
        $this->id_ce = 0;
        
        $this->etat_cible = false;
        $this->id_verrou = "";
        $this->next_try = date("Y-m-d H:i:s");
    }
    
    public function asString()
    {
        if ($this->type == self::TYPE_DOCUMENT) {
            return "id_e: {$this->id_e} - id_d: {$this->id_d} - id_u: {$this->id_u} - source: {$this->etat_source} - cible: {$this->etat_cible}";
        }
        if ($this->type == self::TYPE_CONNECTEUR) {
            return "id_e: {$this->id_e} - id_ce: {$this->id_ce} - id_u: {$this->id_u} - source: {$this->etat_source} - cible: {$this->etat_cible}";
        }
        return false;
    }
    
    public function isTypeOK()
    {
        return in_array($this->type, array(Job::TYPE_CONNECTEUR,Job::TYPE_DOCUMENT,self::TYPE_TRAITEMENT_LOT));
    }

    public function getLastMessage()
    {
        return substr($this->last_message, 0, self::MAX_LAST_MESSAGE_LENGTH);
    }
}
