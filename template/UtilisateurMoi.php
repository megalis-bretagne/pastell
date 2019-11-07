<?php
/**
 * @var Gabarit $this
 * @var array $info
 * @var string $denominationEntiteDeBase
 * @var Certificat $certificat
 * @var array $notification_list
 * @var array $arbre
 * @var bool $utilisateur_edition
 * @var array $all_module
 */
?>
<div class="box">

<h2>Vos informations</h2>

<table class='table table-striped'>

<tr>
<th class="w140">Login</th>
<td><?php hecho($info['login']); ?></td>
</tr>

<tr>
<th>Prénom</th>
<td><?php hecho($info['prenom']); ?></td>
</tr>

<tr>
<th>Nom</th>
<td><?php hecho($info['nom']); ?></td>
</tr>

<tr>
<th>Email</th>
<td><?php hecho($info['email']); ?></td>
</tr>

<tr>
<th>Date d'inscription</th>
<td><?php echo time_iso_to_fr($info['date_inscription']) ?></td>
</tr>


<tr>
<th>Entité de base</th>
<td>
    <a href='Entite/detail?id_e=<?php echo $info['id_e']?>'>
        <?php if ($info['id_e']) : ?>
            <?php hecho($denominationEntiteDeBase); ?>
        <?php else : ?>
            Utilisateur global
        <?php endif;?>
    </a>
</td>
</tr>

<?php if ($certificat->isValid()) : ?>
<tr>
<th>Certificat</th>
<td><a href='Utilisateur/certificat?verif_number=<?php echo $certificat->getVerifNumber() ?>'><?php echo $certificat->getFancy() ?></a></td>
</tr>
<?php endif;?>

</table>


<a href='Utilisateur/modifPassword' class='btn btn-primary margin-right'><i class="fa fa-pencil"></i>&nbsp;Modifier mon mot de passe</a>
<a href='Utilisateur/modifEmail' class='btn btn-primary'><i class="fa fa-pencil"></i>&nbsp;Modifier mon email</a>

</div>




<div class="box">
<h2>Vos rôles sur Pastell : </h2>

<table class='table table-striped'>
<tr>
<th class="w140">Rôle</th>
<th>Entité</th>
<th>&nbsp;</th>
</tr>

<?php foreach ($this->RoleUtilisateur->getRole($id_u) as $infoRole) : ?>
<tr>
    <td><?php hecho($infoRole['role']); ?></td>
    <td>
        <?php if ($infoRole['id_e']) : ?>
            <a href='Entite/detail?id_e=<?php echo $infoRole['id_e']?>'><?php hecho($infoRole['denomination']); ?></a>
        <?php else : ?>
            Toutes les collectivités
        <?php endif;?>
    </td>
</tr>
<?php endforeach;?>
</table>

</div>

<div class="box">
<h2>Vos notifications</h2>
<table class='table table-striped'>
<tr>
<th class="w140">Entité</th>
<th>Type de dossier</th>
<th>Action</th>
<th>Type d'envoi</th>
<th>&nbsp;</th>
</tr>

<?php foreach ($notification_list as $infoNotification) : ?>
<tr>
    <td>
        <?php if ($infoNotification['id_e']) : ?>
            <a href='Entite/detail?id_e=<?php echo $infoNotification['id_e']?>'><?php hecho($infoNotification['denomination']); ?></a>
        <?php else : ?>
            Toutes les collectivités
        <?php endif;?>
    </td>
    <td>
        <?php if ($infoNotification['type']) : ?>
            <?php
                hecho($this->DocumentTypeFactory->getFluxDocumentType($infoNotification['type'])->getName());
            ?>
        <?php else : ?>
            Tous
        <?php endif; ?>
    </td>
    <td>
        <ul>
        <?php foreach ($infoNotification['action'] as $action) :?>
            <li><?php echo $action ? $action : 'Toutes' ?></li>
        <?php endforeach;?>
        </ul>

    </td>
    <td>
        <p>
        <?php echo $infoNotification['daily_digest'] ? "Résumé journalier" : "Envoi à chaque événement"?>
        </p>
    </td>

    <td>
        <?php if ($utilisateur_edition) : ?>
            <a
                    class="btn btn-primary"
                    href='Utilisateur/notification?from_me=true&id_u=<?php echo $infoNotification['id_u']?>&id_e=<?php echo $infoNotification['id_e']?>&type=<?php echo $infoNotification['type']?>&moi=true'
            >
                <i class="fa fa-pencil"></i>&nbsp;Modifier
            </a>

            <a class='btn btn-danger' href='Utilisateur/notificationSuppression?id_n=<?php echo $infoNotification['id_n'] ?>&moi=true'>
                <i class="fa fa-trash"></i>&nbsp;Supprimer
            </a>
        <?php endif;?>
    </td>
</tr>
<?php endforeach;?>
</table>
<?php if ($utilisateur_edition) : ?>
<h3>Ajouter une notification</h3>
    <form action='Utilisateur/notificationAjout' method='post' class='form-inline'>
        <?php $this->displayCSRFInput(); ?>
        <input type='hidden' name='id_u' value='<?php echo $id_u ?>' />
        <input type='hidden' name='moi' value='true' />

        <select name='id_e' class='select2_entite'>
            <option value='0'>Entité racine</option>
            <?php foreach ($arbre as $entiteInfo) : ?>
                <option value='<?php echo $entiteInfo['id_e']?>'>
                    <?php echo str_repeat("-", $entiteInfo['profondeur']); ?>
                    <?php hecho($entiteInfo['denomination']); ?>
                </option>
            <?php endforeach ; ?>
        </select>

        <?php $this->DocumentTypeHTML->displaySelectWithCollectivite($all_module); ?>
        <select name='daily_digest'  class="form-control col-md-2 mr-2">
            <option value=''>Envoi à chaque événement</option>
            <option value='1'>Résumé journalier</option>
        </select>

        <button type='submit' class='btn btn-primary'><i class="fa fa-plus-circle"></i>&nbsp;Ajouter</button>
    </form>
<?php endif;?>
</div>
