<?php

class ExtensionAPIController extends BaseAPIController
{
    private $extensions;
    private $extensionSQL;

    public function __construct(
        Extensions $extensions,
        ExtensionSQL $extensionSQL
    ) {
        $this->extensions = $extensions;
        $this->extensionSQL = $extensionSQL;
    }

    /**
     * @return array|bool|mixed
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function get()
    {
        $this->checkDroit(0, "system:lecture");
        $id_extension = $this->getFromQueryArgs(0);
        if ($id_extension) {
            if (! $this->extensionSQL->getInfo($id_extension)) {
                throw new NotFoundException("L'extension #{$id_extension} n'existe pas.");
            }
            return $this->extensions->getInfo($id_extension);
        }
        $result['result'] = $this->extensions->getAll();
        return $result;
    }

    /**
     * @return array
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws Exception
     */
    public function post()
    {
        $this->checkDroit(0, "system:edition");
        $path = $this->getFromRequest('path');
        if (! file_exists($path)) {
            throw new Exception("Le chemin « $path » n'existe pas sur le système de fichier");
        }
        $detail_extension = $this->extensions->getInfo(0, $path);
        $extension_list = $this->extensions->getAll();

        foreach ($extension_list as $id_e => $extension) {
            if (($extension['id'] == $detail_extension['id']) && !($extension['id_e'] == $detail_extension['id_e'])) {
                throw new ConflictException("L'extension #{$detail_extension['id']} est déja présente");
            }
        }
        $id_extension = $this->extensionSQL->edit(0, $path);
        return array('id_extension' => $id_extension,'detail' => $detail_extension);
    }

    /**
     * @return array
     * @throws NotFoundException
     * @throws Exception
     */
    public function patch()
    {
        $this->checkDroit(0, 'system:edition');
        $id_extension = $this->getFromQueryArgs(0);
        if (! $id_extension || ! $this->extensionSQL->getInfo($id_extension)) {
            throw new NotFoundException("Extension #$id_extension non trouvée");
        }
        $path = $this->getFromRequest('path');
        if (! file_exists($path)) {
            throw new Exception("Le chemin « $path » n'existe pas sur le système de fichier");
        }

        $detail_extension = $this->extensions->getInfo($id_extension, $path);
        $extension_list = $this->extensions->getAll();

        foreach ($extension_list as $id_e => $extension) {
            if (($extension['id'] == $detail_extension['id']) && !($extension['id_e'] == $detail_extension['id_e'])) {
                throw new Exception("L'extension #{$detail_extension['id']} est déja présente");
            }
        }
        $this->extensionSQL->edit($id_extension, $path); // ajout ou modification

        return array('id_extension' => $id_extension,'detail' => $detail_extension);
    }

    /**
     * @return mixed
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function delete()
    {
        $this->checkDroit(0, "system:edition");
        $id_extension = $this->getFromQueryArgs(0);
        if (! $id_extension || ! $this->extensionSQL->getInfo($id_extension)) {
            throw new NotFoundException("Extension #$id_extension non trouvée");
        }
        $this->extensionSQL->delete($id_extension);
        $result['result'] = self::RESULT_OK;
        return $result;
    }


    /**
     * @return mixed
     * @throws ForbiddenException
     * @throws Exception
     */
    public function compatV1Edition()
    {
        $this->checkDroit(0, "system:edition");

        $id_extension = $this->getFromRequest('id_extension');
        $path = $this->getFromRequest('path');

        if (! file_exists($path)) {
            throw new Exception("Le chemin « $path » n'existe pas sur le système de fichier");
        }
        if ($id_extension) {
            $info_extension = $this->extensionSQL->getInfo($id_extension);
            if (!$info_extension) {
                throw new Exception("L'extension #{$id_extension} est introuvable");
            }
        }

        $detail_extension = $this->extensions->getInfo($id_extension, $path);
        $extension_list = $this->extensions->getAll();

        foreach ($extension_list as $id_e => $extension) {
            if (($extension['id'] == $detail_extension['id']) && !($extension['id_e'] == $detail_extension['id_e'])) {
                throw new Exception("L'extension #{$detail_extension['id']} est déja présente");
            }
        }
        $this->extensionSQL->edit($id_extension, $path); // ajout ou modification

        $result['detail_extension'] = $detail_extension;
        $result['result'] = self::RESULT_OK;
        return $result;
    }
}
