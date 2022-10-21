<?php

use Pastell\Service\Droit\DroitService;
use Twig\Environment;

abstract class ChoiceActionExecutor extends ActionExecutor
{
    private array $viewParameter;
    protected string $field;
    protected int $page = 0;

    private $recuperateur;


    public function __construct(ObjectInstancier $objectInstancier)
    {
        parent::__construct($objectInstancier);
        $this->viewParameter = [];
        $this->setRecuperateur(new Recuperateur($_POST));
    }

    public function setRecuperateur(Recuperateur $recuperateur)
    {
        $this->recuperateur = $recuperateur;
    }

    public function getRecuperateur(): Recuperateur
    {
        return $this->recuperateur;
    }

    public function setField(string $field): void
    {
        $this->field = $field;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function setViewParameter($key, $value)
    {
        $this->viewParameter[$key] = $value;
    }

    public function getViewParameter(): array
    {
        $this->viewParameter['id_d'] = $this->id_d;
        $this->viewParameter['id_e'] = $this->id_e;
        $this->viewParameter['id_ce'] = $this->id_ce;
        $this->viewParameter['action'] = $this->action;
        $this->viewParameter['field'] = $this->field;
        $this->viewParameter['page'] = $this->page;
        return $this->viewParameter;
    }

    /**
     * @throws NotFoundException
     */
    public function renderPage(string $pageTitle, string $template): void
    {
        $this->displayMenuGauche();
        $this->setViewParameter('page_title', $pageTitle);
        $this->setViewParameter('template_milieu', $template);
        $pastellController = $this->objectInstancier->getInstance(PastellControler::class);
        $pastellController->setAllViewParameter($this->getViewParameter());
        $pastellController->setNavigationInfo($this->id_e, '/Entite/connecteur');
        $pastellController->setTwigEnvironment($this->objectInstancier->getInstance(Environment::class));
        $pastellController->renderDefault();
    }

    /**
     * @throws Exception
     */
    public function redirectToFormulaire(): never
    {
        $url = sprintf(
            '%sDocument/edition?id_d=%s&id_e=%s&page=%s',
            rtrim(SITE_BASE, '/') . '/',
            $this->id_d,
            $this->id_e,
            $this->page
        );
        header("Location: $url");
        exit_wrapper();
    }

    /**
     * @throws Exception
     */
    public function redirectToConnecteurFormulaire(): void
    {
        $url = sprintf(
            '%sConnecteur/editionModif?id_ce=%s',
            rtrim(SITE_BASE, '/') . '/',
            $this->id_ce
        );
        header_wrapper("Location: $url");
        exit_wrapper();
    }


    public function displayMenuGauche()
    {
        if (! $this->id_ce) {
            return;
        }
        $this->viewParameter['id_e_menu'] = $this->id_e;
        $this->viewParameter['type_e_menu'] = "";
        $this->viewParameter['menu_gauche_template'] = "EntiteMenuGauche";
        $this->viewParameter['menu_gauche_select'] = "Entite/connecteur";
        $this->viewParameter['droit_lecture_on_connecteur'] = $this->objectInstancier
            ->getInstance(DroitService::class)
            ->hasDroitConnecteurLecture($this->id_e, $this->id_u);
        $this->viewParameter['droitLectureOnUtilisateur'] = $this->objectInstancier
            ->getInstance(DroitService::class)
            ->hasDroitUtilisateurLecture($this->id_e, $this->id_u);
    }

    public function isEnabled()
    {
        return true;
    }

    protected function getConnecteurTypeActionExecutor()
    {
        $connecteurTypeActionExecutor =  parent::getConnecteurTypeActionExecutor();
        /**
         * bof...
         * @var ConnecteurTypeChoiceActionExecutor $connecteurTypeActionExecutor
         */
        $connecteurTypeActionExecutor->field = $this->field;
        $connecteurTypeActionExecutor->page = $this->page;
        $connecteurTypeActionExecutor->setRecuperateur($this->getRecuperateur());
        return $connecteurTypeActionExecutor;
    }

    abstract public function display();

    abstract public function displayAPI();

    /** Permet d'afficher une liste pour la recherche avancée */
    public function displayChoiceForSearch()
    {
        return [];
    }
}
