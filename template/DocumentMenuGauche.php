<?php
/** @var $id_e_menu */
/** @var array $all_module */
/** @var $type_e_menu */
/** @var $menu_gauche_link */
?>
<div id="main_gauche"  class="ls-on">

    <?php
    foreach ($all_module as $type_flux => $les_flux) : ?>
        <h3><?php hecho($type_flux); ?></h3>
        <div class="menu">
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
                        <i class="fa fa-chevron-right"></i></a>

                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    <?php endforeach;?>


</div><!-- main_gauche  -->
