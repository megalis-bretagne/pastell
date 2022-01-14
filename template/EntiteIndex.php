<?php

if ($id_e && $has_many_collectivite) : ?>
<a class='btn btn-mini' href='Entite/detail'><i class='icon-cog'></i>Administration</a>
<?php endif; ?>


<ul class="nav nav-pills" style="margin-top:10px;">
    <?php foreach ($formulaire_tab as $page_num => $name) : ?>
        <li <?php echo ($page_num == $tab_number) ? 'class="active"' : '' ?>>
        <a href='Entite/detail?id_e=<?php echo $id_e ?>&page=<?php echo $page_num?>'>
            <?php echo $name?>
        </a>
    <?php endforeach;?>
</ul>
<?php if ($info && ! $info['is_active']) : ?>
<div class='alert alert-warning'>
Cette entité n'est pas active !
</div>
<?php endif;?>

<div class="box">

<?php if ($tab_number != 5) :?>
    <?php $this->render($tableau_milieu)?>
<?php else :?>
<a href='MailSec/annuaire?id_e=<?php echo $id_e?>'>Annuaire »</a>   
<?php endif;?>


</div>

