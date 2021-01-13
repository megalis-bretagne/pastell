<?php

/** @var $id_e_menu */
/** @var array $all_module */
/** @var $type_e_menu */
/** @var $menu_gauche_link */
?>
<div id="main_gauche" class="ls-on">

    <?php
    $i = 0;
    foreach ($all_module as $type_flux => $les_flux) : ?>
        <?php ++$i; ?>
        <h3
                data-toggle="collapse"
                data-target="#collapse-<?php hecho($i); ?>"
                aria-expanded="false"
                aria-controls="collapse-<?php hecho($i); ?>"
        >
            <?php hecho($type_flux); ?>
        </h3>
        <div
                class="menu collapse <?php hecho(array_key_exists($type_e_menu, $les_flux) ? "show" : ""); ?>"
                id="collapse-<?php hecho($i); ?>"
        >
            <ul>
                <?php foreach ($les_flux as $nom => $affichage) : ?>
                    <?php
                    $array_keys = array_keys($les_flux);
                    $last_key = end($array_keys);
                    ?>
                    <?php
                    $a_class = "";
                    if ($nom === $last_key) {
                        $a_class = "dernier";
                    }
                    if ($type_e_menu == $nom) {
                        $a_class = "actif";
                    }

                    if (($nom === $last_key) && ($type_e_menu == $nom)) {
                        $a_class = "actif dernier";
                    }
                    ?>

                    <li>
                        <a class="<?php echo $a_class ?>" href='<?php $this->url($menu_gauche_link . "&type=$nom"); ?>'>
                            <?php hecho($affichage); ?>

                        </a>

                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>


</div><!-- main_gauche  -->
