<?php

class ManifestReader
{
    public const ID = 'id';
    public const VERSION = 'version';
    public const REVISION = 'revision';
    public const NOM = 'nom';
    public const DESCRIPTION = 'description';
    public const PASTELL_VERSION = 'pastell-version';
    public const EXTENSIONS_VERSION_ACCEPTED = 'extensions_versions_accepted';
    public const EXTENSION_NEEDED = 'extension_needed';

    public const VERSION_COMPLETE = 'version-complete';
    public const AUTRE_VERSION_COMPATIBLE = 'autre-version-compatible';
    public const LAST_CHANGED_DATE = 'last_changed_date';

    private $manifest_info;

    public function __construct(array $manifest_info)
    {
        foreach ([self::VERSION,self::REVISION,self::NOM,self::DESCRIPTION,self::PASTELL_VERSION,self::ID,self::LAST_CHANGED_DATE] as $key) {
            if (! isset($manifest_info[$key])) {
                $manifest_info[$key] = false;
            }
        }
        foreach ([self::EXTENSIONS_VERSION_ACCEPTED,self::EXTENSION_NEEDED] as $key) {
            if (empty($manifest_info[$key])) {
                $manifest_info[$key] = [];
            }
        }
        if (preg_match('#^\$Rev: (\d*) \$#u', $manifest_info[self::REVISION], $matches)) {
            $manifest_info[self::REVISION] = $matches[1];
        }
        $manifest_info[self::VERSION_COMPLETE] =  "Version {$manifest_info[self::VERSION]} - Révision  {$manifest_info[self::REVISION]}" ;

        $manifest_info[self::AUTRE_VERSION_COMPATIBLE] = [];
        foreach ($manifest_info[self::EXTENSIONS_VERSION_ACCEPTED] as $version) {
            if ($version != $manifest_info[self::VERSION]) {
                $manifest_info[self::AUTRE_VERSION_COMPATIBLE][] = $version;
            }
        }

        $this->manifest_info = $manifest_info;
    }

    public function getInfo()
    {
        return $this->manifest_info;
    }

    private function getElement($element_name)
    {
        $info = $this->getInfo();
        return $info[$element_name];
    }

    public function getRevision()
    {
        return $this->getElement(self::REVISION);
    }

    public function getVersion()
    {
        return $this->getElement(self::VERSION);
    }

    public function getId()
    {
        return $this->getElement(self::ID);
    }

    public function getLastChangedDate()
    {
        return $this->getElement(self::LAST_CHANGED_DATE);
    }

    /**
     * Teste si une version attendue correspond à une des versions acceptées par le fichier manifest
     * @param string $version_attendue
     * @return boolean
     */
    public function isVersionOK(string $version_attendue): bool
    {
        $expected_versions = explode('.', $version_attendue);
        $accepted_versions = explode('.', $this->getInfo()[self::VERSION]);
        $expected_version_format = count($expected_versions);
        $expected_minor_version = $expected_version_format >= 2 ? $expected_versions[1] : '0';
        $expected_patch_version = $expected_version_format >= 3 ? $expected_versions[2] : '0';
        $accepted_version_format = count($accepted_versions);
        $accepted_minor_version = $accepted_version_format >= 2 ? $accepted_versions[1] : '0';
        $accepted_patch_version = $accepted_version_format >= 3 ? $accepted_versions[2] : '0';
        return ($expected_versions[0] === $accepted_versions[0]
            && ($expected_minor_version < $accepted_minor_version
                || ($expected_minor_version === $accepted_minor_version
                    && $expected_patch_version <= $accepted_patch_version)));
    }

    public function getExtensionNeeded()
    {
        return $this->getElement(self::EXTENSION_NEEDED);
    }
}
