<?php

use Twig\Environment;

class ControlerTestCase extends PastellTestCase
{
    private Controler $controler;

    private $get_info = [];
    private $post_info = [];

    protected function setGetInfo(array $info)
    {
        if (isset($this->controler)) {
            $this->controler->setGetInfo(new Recuperateur($info));
        }
        $this->get_info = $info;
    }

    protected function setPostInfo(array $info)
    {
        if (isset($this->controler)) {
            $this->controler->setPostInfo(new Recuperateur($info));
        }
        $this->post_info = $info;
    }

    public function getControlerInstance($class_name)
    {
        $this->getObjectInstancier()->getInstance(Authentification::class)->connexion('admin', 1);
        $this->controler = $this->getObjectInstancier()->getInstance($class_name);
        $this->controler->setDontRedirect(true);
        $this->controler->setGetInfo(new Recuperateur($this->get_info));
        $this->controler->setPostInfo(new Recuperateur($this->post_info));
        $this->controler->setTwigEnvironment($this->getObjectInstancier()->getInstance(Environment::class));
        return $this->controler;
    }

    /**
     * @param array $permission
     * @param int $id_e
     * @return int
     */
    public function authenticateNewUserWithPermission(array $permission, int $id_e = self::ID_E_COL): int
    {
        $roleSql = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $roleSql->updateDroit("my_role", $permission);
        $id_u = $this->getObjectInstancier()->getInstance(UtilisateurCreator::class)
            ->create(
                'my_login',
                'D@iw3DDf41Nl$DXzMJL!Uc2Yo',
                'D@iw3DDf41Nl$DXzMJL!Uc2Yo',
                'foo@example.com'
            );

        $roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
        $roleUtilisateur->addRole($id_u, "my_role", $id_e);

        $this->getObjectInstancier()->getInstance(Authentification::class)->connexion('my_login', $id_u);

        return $id_u;
    }
}
