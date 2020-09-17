<?php

/**
 * @var bool $display_entite_racine
 * @var string $navigation_url
 * @var array $navigation
 */
?>

<ul class="breadcrumb">

    <?php if ($display_entite_racine) : ?>
        <li>
            <a href='<?php echo $navigation_url ?>'>Entité Racine</a>
        </li>
        <li>
            <span class="divider">/</span>
        </li>
    <?php endif; ?>

    <?php if (isset($navigation)) : ?>
        <?php foreach ($navigation as $nav) :
            $formId = 'bc_form_' . $nav['id_e'];
            $idSelect = 'select2_id_e_bc_' . $nav['id_e'];
            $idSelectSubmit = $idSelect . '_submit';
            ?>

            <?php if ($nav['is_last'] && $nav['is_root']) : ?>
            <li>
                <strong><?php hecho($nav['name']) ?></strong>
            </li>
            <?php endif; ?>

            <?php if (!$nav['is_root']) : ?>
            <li>
                <form action='<?php echo $navigation_url ?>' method='get' id="<?php hecho($formId); ?>">

                    <input type='hidden' name='type' value='<?php hecho($type ?? ''); ?>'/>
                    <select name='id_e' class='select2_breadcrumb' id='<?php hecho($idSelect); ?>'>
                        <?php foreach ($nav['same_level_entities'] as $fille) : ?>
                            <option
                                    value='<?php echo $fille['id_e'] ?>'
                                <?php echo $nav['id_e'] == $fille['id_e'] ? 'selected' : '' ?>
                            >
                                <?php hecho($fille['denomination']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type='submit' value='go' id='<?php hecho($idSelectSubmit); ?>'/>
                </form>

            </li>
                <?php if ($nav['has_children']) : ?>
            <li>
                <span class="divider">/</span>
            </li>
                <?php endif; ?>
            <script>
                $(document).ready(function () {
                    $("#<?php hecho($idSelectSubmit); ?>").hide();
                    $("#<?php hecho($idSelect); ?>").change(function () {
                        $(this).parents("form").submit();
                    });
                });
            </script>
            <?php endif; ?>


            <?php if ($nav['is_last'] && !empty($nav['children'])) : ?>
            <li>
                <span class="divider">/</span>
            </li>
            <li>
                <form action='<?php echo $navigation_url ?>' method='get' id="bc_form">
                    <input type='hidden' name='type' value='<?php hecho($type ?? ''); ?>'/>
                    <select name='id_e' class='select2_breadcrumb' id='select2_id_e_bc'>
                        <option></option>
                        <?php foreach ($nav['children'] as $fille) : ?>
                            <option value='<?php echo $fille['id_e'] ?>'><?php hecho($fille['denomination']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type='submit' value='go' id='select2_id_e_bc_submit'/>
                </form>
            </li>

            <script>
                $(document).ready(function () {
                    $("#select2_id_e_bc_submit").hide();
                    $("#select2_id_e_bc").change(function () {
                        $(this).parents("form").submit();
                    });
                });
            </script>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

</ul>

