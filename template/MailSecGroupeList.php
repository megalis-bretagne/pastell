<?php

/**
 * @var Gabarit $this
 * @var array $infoEntite
 * @var array $listGroupe
 * @var AnnuaireGroupe $annuaireGroupe
 * @var bool $can_edit
 * @var bool $groupe_herited
 */
?>
<a class='btn btn-link' href='MailSec/annuaire?id_e=<?php echo $id_e ?>'><i class="fa fa-arrow-left"></i>&nbsp;Voir la liste des contacts</a>


<div class="box">
<h2>Liste des groupes de contacts de <?php hecho($infoEntite['denomination']); ?></h2>

<form action='MailSec/delGroupe' method='post' >
    <?php $this->displayCSRFInput() ?>
    <input type='hidden' name='id_e' value='<?php echo $id_e ?>' />

<table  class="table table-striped">
    <tr>

        <th>Nom</th>
        <th>Contact</th>
        <th>Partagé ?</th>
    </tr>
<?php foreach ($listGroupe as $groupe) :
    $nbUtilisateur = $annuaireGroupe->getNbUtilisateur($groupe['id_g']);
    $utilisateur = $annuaireGroupe->getUtilisateur($groupe['id_g'], 0, 3);
    $r = [];
    foreach ($utilisateur as $u) {
        $r[] = htmlentities('"' . $u['description'] . '"' . " <" . $u['email'] . ">", ENT_QUOTES, "utf-8");
    }
    $utilisateur = implode(",<br/>", $r);


    ?>
    <tr>
        <td><input type='checkbox' name='id_g[]' value='<?php echo $groupe['id_g'] ?>'/>
            <a href='MailSec/groupe?id_e=<?php echo $id_e?>&id_g=<?php echo $groupe['id_g']?>'><?php hecho($groupe['nom']); ?></a></td>
        <td><?php if ($nbUtilisateur) : ?>
                <?php echo $utilisateur;?>
                <?php if ($nbUtilisateur > 3) :?>
                <br/> et <a href='MailSec/groupe?id_e=<?php echo $id_e?>&id_g=<?php echo $groupe['id_g']?>'><?php echo ($nbUtilisateur - 3) ?> autres</a>

                <?php endif;?>
            <?php else : ?>
                Ce groupe est vide
            <?php endif;?>  
        </td>
        <td>
            <?php echo $groupe['partage'] ? "OUI" : "NON";?>    
        </td>
    </tr>
<?php endforeach;?>

</table>
<?php if ($can_edit) : ?>
    <button type="submit" class="btn btn-danger">
        <i class="fa fa-trash"></i>&nbsp;Supprimer
<?php endif; ?>

</form>
</div>

<?php if ($roleUtilisateur->hasDroit($authentification->getId(), "annuaire:edition", $id_e)) : ?>
<div class="box">
<h2>Créer un groupe</h2>
<form action='MailSec/addGroupe' method='post' >
    <?php $this->displayCSRFInput() ?>
    <input type='hidden' name='id_e' value='<?php echo $id_e ?>' />

    <table class='table table-striped'>

            <tr>
                <th>Nom</th>
                <td><input class="form-control col-md-4" type='text' name='nom' value='<?php echo $this->LastError->getLastInput('nom') ?>' /></td>
            </tr>

    </table>
    <button type="submit" class="btn btn-primary">
        <i class="fa fa-plus"></i>&nbsp;Créer
    </button></form>
</div>
<?php endif;?>

<?php if ($groupe_herited) : ?>
<div class="box">
<h2>Liste des groupes hérités</h2>

<table  class="table table-striped">
    <tr>
        <th style="width: 25%">Entité</th>
        <th style="width: 25%">Nom</th>
        <th style="width: 50%">Contact</th>
    </tr>

    <?php foreach ($groupe_herited as $groupe) :
        $nbUtilisateur = $annuaireGroupe->getNbUtilisateur($groupe['id_g']);
        $utilisateur = $annuaireGroupe->getUtilisateur($groupe['id_g'], 0, 3);
        $utilisateurPlus = $annuaireGroupe->getUtilisateur($groupe['id_g'], 4);
        $r = [];
        foreach ($utilisateur as $u) {
            $r[] = htmlentities('"' . $u['description'] . '"' . " <" . $u['email'] . ">", ENT_QUOTES, "utf-8");
        }
        $utilisateur = implode(",<br/>", $r);
        $r = [];
        foreach ($utilisateurPlus as $u) {
            $r[] = htmlentities('"' . $u['description'] . '"' . " <" . $u['email'] . ">", ENT_QUOTES, "utf-8");
        }
        $utilisateurPlus = implode(",<br/>", $r);

    ?>
    <tr>
        <td><?php hecho($groupe['denomination']); ?></td>
        <td>
            <?php hecho($groupe['nom']); ?></td>
        <td data-toggle="collapse" data-target="#collapse-more-contacts" aria-expanded="false"
            aria-controls="collapse-more-contacts" onclick="hideMoreUsers()">
            <?php if ($nbUtilisateur) : ?>
                <?php echo $utilisateur;?>

                <?php if ($nbUtilisateur > 3) :?>
                <br>
                    <div id="more-contacts">
                        et <span style="color:#53599a"
                                 onmouseover="this.style.color='#7076b8'; this.style.cursor='pointer';"
                                 onmouseout="this.style.color='#53599a';"> <?php echo ($nbUtilisateur - 3) ?> autres
                        </span>
                    </div>
                    <div id="collapse-more-contacts" class="collapse">
                            <?php echo $utilisateurPlus;?>
                        <p style="color:#53599a"
                           onmouseover="this.style.color='#7076b8'; this.style.cursor='pointer';"
                           onmouseout="this.style.color='#53599a';"
                        >
                            afficher moins
                        </p>
                    </div>
                <?php endif;?>

            <?php else : ?>
                Ce groupe est vide
            <?php endif;?>
        </td>
    </tr>
    <?php endforeach;?>

</table>
</div>

<?php endif;?>

<script>
    function hideMoreUsers() {
        var collapseDiv = document.querySelector('#collapse-more-contacts');
        var moreUsersLink = document.querySelector('#more-contacts');
        if (collapseDiv.classList.contains('show')) {
            moreUsersLink.style.display = 'inline';
        } else {
            moreUsersLink.style.display = 'none';
        }
    }
</script>
