<?php

namespace Pastell\Service\Pack;

class PackService
{
    public const PACK_CHORUS_PRO = "pack_chorus_pro";
    public const PACK_MARCHE = "pack_marche";

    private $list_enabled_pack = [];

    /**
     * PackService constructor.
     * @param bool $enable_pack_chorus_pro
     * @param bool $enable_pack_marche
     */
    public function __construct(
        bool $enable_pack_chorus_pro = false,
        bool $enable_pack_marche = false
    ) {
        if ($enable_pack_chorus_pro) {
            $this->list_enabled_pack[] = self::PACK_CHORUS_PRO;
        }
        if ($enable_pack_marche) {
            $this->list_enabled_pack[] = self::PACK_MARCHE;
        }
    }

    /**
     * @return array
     */
    public function getListPack(): array
    {
        $list_pack[] = self::PACK_CHORUS_PRO;
        $list_pack[] = self::PACK_MARCHE;
        return $list_pack;
    }

    /**
     * @return array
     */
    public function getListEnabledPack(): array
    {
        return $this->list_enabled_pack;
    }

    /**
     * @param array $restriction_pack
     * @return bool
     */
    public function restrictionHasEnabledPack(array $restriction_pack = []): bool
    {
        if (empty($restriction_pack)) {
            return true;
        }
        foreach ($restriction_pack as $pack) {
            if (in_array($pack, $this->list_enabled_pack)) {
                return true;
            }
        }
        return false;
    }
}
