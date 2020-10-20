<?php

/**
 * @var Gabarit $this
 * @var bool $checkWorkspace
 * @var array $free_space_data
 * @var string $journal_nb_lines
 * @var string $journal_nb_lines_historique
 * @var string $journal_first_line_date
 * @var float $journal_first_line_age
 * @var bool $redis_status
 * @var string $redis_last_error
 * @var array $check_ini
 * @var array $checkExtension
 * @var array $check_value
 * @var array $expected_elements
 * @var array $commandeTest
 * @var array $database_sql_command
 * @var int $tables_collation
 * @var array $connecteur_manquant
 * @var array $document_type_manquant
 */

/** @var VersionAPIController $versionController */
$versionController = $this->getAPIController('Version');
$manifest_info = $versionController->get();

/**
 * @var int $tables_marked_as_crashed
 */

?>
<div class="box">

<h2>Information de version</h2>
<table class='table table-striped'>

<tr>
    <th class="w140">Version</th>
    <td><?php echo $manifest_info['version']; ?></td>
</tr>
<tr>
    <th class="w140">Révision</th>
    <td><?php echo $manifest_info['revision']; ?></td>
</tr>
<tr>
    <th class="w140">Date du commit</th>
    <td><?php echo $manifest_info['last_changed_date']; ?></td>
</tr>
<tr>
    <th class="w140">Versions compatibles pour les modules</th>
    <td>
        <ul>
        <?php foreach ($manifest_info['extensions_versions_accepted'] as $version_module) : ?>
            <li><?php hecho($version_module)?></li>
        <?php endforeach;?>
        </ul>
    </td>
</tr>
</table>
</div>

<div class="box">
    <h2>Liste des Packs</h2>
    <table class='table table-striped'>
        <?php foreach ($listPack as $pack => $enabled) : ?>
            <tr>
                <th class="w140"><?php hecho($pack)?></th>
                <td>
                    <?php if ($enabled) : ?>
                        <p class="alert alert-success">
                            <b>Activé</b>
                        </p>
                    <?php else : ?>
                        <p class="alert alert-warning">
                            <b>Inactif</b>
                        </p>
                    <?php endif;?>
                </td>
            </tr>
        <?php endforeach;?>
    </table>
</div>

<div class="box">
    <h2>Workspace</h2>
    <table class='table table-striped'>
    <tr>
        <th class='w400'><?php echo WORKSPACE_PATH ?> accessible en lecture/écriture ?</th>
        <td><?php echo $checkWorkspace ? "<b style='color:green'>ok</b>" : "<b style='color:red'>NON</b>"?></td>
    </tr>
    <tr>
        <th class="w400">Taille totale de la partition</th>
        <td><?php echo $free_space_data['disk_total_space']; ?></td>
    </tr>
    <tr>
        <th class="w400">Taille des données</th>
        <td><?php echo $free_space_data['disk_use_space']; ?></td>
    </tr>
    <tr>
        <th class="w400">Taux d'occupation</th>
        <td>
            <b style="color:<?php echo $free_space_data['disk_use_too_big'] ? 'red' : 'green'?>">
                <?php echo $free_space_data['disk_use_percent']; ?>
            </b>
        </td>
    </tr>
    </table>

</div>

<div class="box">
    <h2>Journal</h2>
    <table class='table table-striped'>

        <tr>
            <th class="w400">Nombre d'enregistrements dans la table journal</th>
            <td>
                <?php hecho($journal_nb_lines); ?>
            </td>
        </tr>
        <tr>
            <th class="w400">Nombre d'enregistrements dans la table journal_historique</th>
            <td>
                <?php hecho($journal_nb_lines_historique); ?>
            </td>
        </tr>
        <tr>
            <th class="w400">Date du premier enregistrement de la table journal</th>
            <td>
                <?php hecho($journal_first_line_date); ?>
            </td>
        </tr>
        <tr>
            <th class="w400">Nombre de mois de conservation du journal</th>
            <td>
                <?php hecho(JOURNAL_MAX_AGE_IN_MONTHS) ?>
            </td>
        </tr>
        <tr>
            <th class="w400">Age du premier enregistrement de la table journal</th>
            <td>
                <?php if ($journal_first_line_age > JOURNAL_MAX_AGE_IN_MONTHS * 31) : ?>
                    <p class="badge badge-danger"><?php hecho($journal_first_line_age); ?> jours</p>
                <?php else : ?>
                    <p class="badge badge-success"> ><?php hecho($journal_first_line_age); ?> jours</p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>


<div class="box">
    <h2>Redis</h2>
    <table class='table table-striped'>

        <tr>
            <th class="w400">Statuts</th>
            <td>
                <?php if ($redis_status) :?>
                    <b style='color:green'>OK</b>
                <?php else : ?>
                    <b style='color:red'>KO</b> - <?php hecho($redis_last_error) ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th class="w140">Temps de mise en cache (définition des flux, des connecteurs, ...)</th>
            <td>
                <?php echo CACHE_TTL_IN_SECONDS ?> seconde(s)
            </td>
        </tr>
    </table>
</div>

<div class="box">
    <h2>Configuration PHP</h2>
    <table class='table table-striped'>
        <tr>
            <th class="w300">clé</th>
            <th class="w300">Valeurs minimums attendues</th>
            <th>Valeurs présentes</th>
        </tr>
        <?php foreach ($check_ini as $key => $data) : ?>
            <tr>
                <td><?php echo $key ?></td>
                <td><?php echo $data['expected']?></td>
                <td >
                    <b style='color:<?php echo $data['is_ok'] ? 'green' : 'red' ?>'>
                        <?php echo $data['actual']?>
                    </b>
                </td>
            </tr>
        <?php endforeach;?>
        <tr>
            <th>phpinfo()</th>
            <td><a href="<?php $this->url("/System/phpinfo"); ?>">Voir le phpinfo()</a></td>
            <th>&nbsp;</th>
        </tr>
    </table>
</div>



<div class="box">
<h2>Extensions PHP</h2>

    <?php foreach ($checkExtension as $extension => $is_ok) : ?>
        <?php if ($is_ok) : ?>
            <p class="badge badge-success"><?php hecho($extension); ?></p>
        <?php else : ?>
            <p class="badge badge-danger"><?php hecho($extension); ?></p>
        <?php endif; ?>
    <?php endforeach;?>

</div>


<div class="box">
    <h2>Elements attendus</h2>

    <table class='table table-striped'>
        <tr>
            <th class="w300">Élément</th>
            <th class="w300">Attendu</th>
            <th>Trouvé</th>
        </tr>
        <?php foreach ($check_value as $name => $value) : ?>
            <tr>
                <th><?php echo $name?></th>
                <td><?php echo $value[0] ?></td>
                <td>
                    <?php if (preg_match($value[0], $value[1])) : ?>
                        <b style='color:green'><?php echo $value[1] ?></b>
                    <?php else : ?>
                        <b style='color:red'><?php echo $value[1] ?></b>
                    <?php endif; ?>

                </td>
            </tr>
        <?php endforeach;?>
        <?php foreach ($expected_elements as $name => $value) : ?>
            <tr>
                <th><?php hecho($name); ?></th>
                <td><?php hecho($value['expected']); ?></td>
                <td>
                    <?php if ($value['result']) : ?>
                        <b style='color:green'><?php hecho($value['current']); ?></b>
                    <?php else : ?>
                        <b style='color:red'><?php hecho($value['current']); ?></b>
                    <?php endif; ?>

                </td>
            </tr>
        <?php endforeach;?>
    </table>
</div>


<div class="box">
<h2>Commande présente</h2>

<table class='table table-striped'>
    <tr>
        <th class="w140">Commande</th>
        <th>Résultat</th>
    </tr>
    <?php foreach ($commandeTest as $commande => $emplacement) : ?>
    <tr>
        <th><?php echo $commande?></th>
        <td><?php echo $emplacement ?: "<b style='color:red'>La commande n'est pas disponible</b>"; ?></td>
    </tr>
    <?php endforeach;?>
</table>
</div>

<div class="box">
<h2>Constante</h2>
<table class='table table-striped'>
    <tr>
        <th class="w140">Élément</th>
        <th>Valeur</th>
    </tr>
    <tr>
        <th>OPENSSL_PATH</th>
        <td><?php echo OPENSSL_PATH ?></td>
    </tr>
    <tr>
        <th>WORKSPACE_PATH</th>
        <td><?php echo WORKSPACE_PATH ?></td>
    </tr>
</table>
</div>
<div class="box">
<h2>Auto test</h2>
<table class='table table-striped'>
    <tr>
        <th>Schéma de la base de données</th>
        <td>
            <?php if ($database_sql_command) : ?>
                <b style='color:red'>
                    Le schéma de la base n'est pas conforme au schéma attendu par le code !
                </b>
                <?php foreach ($database_sql_command as $sql_command) : ?>
                    <p><?php hecho($sql_command); ?></p>
                <?php endforeach; ?>
            <?php else : ?>
                <b style='color:green'>
                    Le schéma de la base est conforme au schéma attendu par le code.
                </b>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Encodage de la base de données</th>
        <td>
            <?php if (count($tables_collation) > 1) : ?>
                <b style='color:red'>
                    Les tables n'utilisent pas toutes le même encodage !
                </b>
                <table>
                <?php foreach ($tables_collation as $encodage => $table_list) : ?>
                    <tr>
                        <td><?php hecho($encodage)?></td>
                        <td>
                            <?php echo(implode(", ", $table_list)); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </table>
            <?php elseif (array_keys($tables_collation)[0] != SQLQuery::PREFERRED_TABLE_COLLATION) : ?>
                <b style='color:orange'>
                    L'encodage trouvé (<?php echo  array_keys($tables_collation)[0]; ?>)
                    ne correspond pas à l'encodage prévu (<?php echo SQLQuery::PREFERRED_TABLE_COLLATION; ?>).
                </b>
            <?php else : ?>
                <b style='color:green'>
                    L'encodage de la base est conforme à l'encodage attendu.
                </b>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Table(s) crashée(s)</th>
        <td>
            <?php if (count($tables_marked_as_crashed) === 0) : ?>
            <b style='color:green'>
                Aucune
            </b>
            <?php else : ?>
                <b style='color:red'>
                    <?php echo implode(", ", $tables_marked_as_crashed); ?>
                </b>
            <?php endif; ?>
        </td>
    </tr>

    <tr>
        <th>Connecteur(s) manquant(s)</th>
        <td>
        <?php if ($connecteur_manquant) : ?>
            <b style='color:red'>
                <?php echo  implode(", ", $connecteur_manquant) ?>
            </b>
            <br/><br/>
        <a href="<?php $this->url("System/missingConnecteur"); ?>" class="btn btn-primary"><i class="fa fa-eye"></i>&nbsp;Voir</a>
        <?php else : ?>
            <b style='color:green'>
                aucun
            </b>
        <?php endif;?>
        </td>
    </tr>
    <tr>
        <th>Type(s) de dossier manquant(s)</th>
        <td>
        <?php if ($document_type_manquant) : ?>
            <b style='color:red'>
                <?php echo  implode(", ", $document_type_manquant) ?>
            </b>
        <?php else : ?>
            <b style='color:green'>
                aucun
            </b>
        <?php endif;?>
        </td>
    </tr>
</table>
</div>


<div class='box'>
<h2>Envoi de mail</h2>
<div class='alert alert-info'>Permet d'envoyer un <b>email de test</b></div>
<form action='<?php $this->url("System/mailTest"); ?>' method='post' >
    <?php $this->displayCSRFInput() ?>
<table class='table table-striped'>
    <tr>
        <th class="w200"><label for='email'>
                ADMIN_EMAIL</label></th>
        <td> <?php echo ADMIN_EMAIL ?></td>
    </tr>

    <tr>
    <th class="w200"><label for='email'>
    Email</label></th>
    <td> <input class="form-control col-md-4" style='width:200px' type='text' name='email' value='' /></td>
</tr>
</table>
    <button type="submit" class="btn btn-primary">
        <i class="fa fa-paper-plane"></i>&nbsp;Envoyer
    </button>

</form>
</div>

<div class='box'>
    <h2>Gestion des erreurs</h2>
    <a href="<?php $this->url("System/sendWarning") ?>" class="btn btn-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        &nbsp;Provoquer un warning</a>
    <a href="<?php $this->url("System/sendFatalError") ?>" class="btn btn-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;Provoquer une erreur fatale</a>
</div>
