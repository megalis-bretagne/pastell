<?php

/**
 * @var Gabarit $this
 * @var array $liste
 * @var Certificate $certificat
 * @var string $verif_number
 */

use Pastell\Utilities\Certificate;

$this->SuivantPrecedent($offset, $limit, $count);
?>

<div class="box">

<h2>Utilisateurs utilisant ce certificat </h2>

<table class="table table-striped">
    <tr>
        <th>Nom Prénom</th>
        <th>Login</th>
        <th>Email</th>
    </tr>
<?php foreach ($liste as $i => $user) :
    ?>
    <tr>
        <td><a href='Utilisateur/detail?id_u=<?php hecho($user['id_u']); ?>'><?php hecho($user['nom']); ?>&nbsp;<?php hecho($user['prenom']); ?></a></td>
        <td><?php hecho($user['login']); ?></td>
        <td><?php hecho($user['email']); ?></td>

    </tr>
<?php endforeach; ?>
</table>
</div>

<div class="box">

<h2>Détail du certificat</h2>

<table  class="table table-striped">
    <tr>
        <th>Numéro de série</th>
        <td>
            <?php echo $certificat->getSerialNumber() ?>
        </td>
    </tr>
        <tr>
        <th>Nom</th>
        <td>
            <?php echo $certificat->getName() ?>
        </td>
    </tr>
    <tr>
        <th>Émis pour </th>
        <td>
        <ul>
        <?php foreach ($certificat->getSubjectAsArray() as $col => $value) : ?>
        <li><?php echo "$col : $value" ;?></li>     
        <?php endforeach;?>
        </ul>
    </td>
    </tr>

    <tr>
        <th>Émis par </th>
        <td>
        <ul>
        <?php foreach ($certificat->getIssuerAsArray() as $col => $value) : ?>
        <li><?php echo "$col : $value" ;?></li>     
        <?php endforeach;?>
        </ul>
    </td>
    </tr>
    <tr>
        <th>Validité </th>
        <td>
        <ul>
        
            <li><?php echo 'Émis le ' . date(Date::DATE_FR, $certificat->getValidFrom()) ;?></li>
            <li><?php echo 'Expire le ' . date(Date::DATE_FR, $certificat->getValidTo()) ;?></li>
        </ul>
    </td>
    </tr>
        <tr>
        <th>&nbsp; </th>
        <td>
            <a href='Utilisateur/getCertificat?verif_number=<?php echo $verif_number?>'>Télécharger le certificat</a>
        </td>
    </tr>
</table>

</div>
