<?php

namespace Pastell\Service\Pack;

class PackService
{
    private $list_pack = [];

    /**
     * PackService constructor.
     * @param array $list_pack
     */
    public function __construct(
        array $list_pack = []
    ) {
        $this->list_pack = $list_pack;
    }

    /**
     * @param array $list_pack
     */
    public function setListPack(array $list_pack = [])
    {
        $this->list_pack = array_replace($this->list_pack, $list_pack);
    }

    /**
     * @return array
     */
    public function getListPack(): array
    {
        return $this->list_pack;
    }

    /**
     * @param array $restriction_pack
     * @return bool
     */
    public function hasOneOrMorePackEnabled(array $restriction_pack = []): bool
    {
        if (empty($restriction_pack)) {
            return true;
        }
        foreach ($restriction_pack as $pack) {
            if (array_key_exists($pack, $this->list_pack) && ($this->list_pack[$pack] === true)) {
                return true;
            }
        }
        return false;
    }
}
