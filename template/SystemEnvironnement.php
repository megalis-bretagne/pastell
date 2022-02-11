<?php

use Pastell\System\HealthCheckItem;

/**
 * @var Gabarit $this
 * @var array $listPack
 * @var HealthCheckItem[] $checkPhpExtensions
 * @var HealthCheckItem[] $checkWorkspace
 * @var HealthCheckItem[] $checkJournal
 * @var HealthCheckItem[] $checkRedis
 * @var HealthCheckItem[] $checkPhpConfiguration
 * @var HealthCheckItem[] $checkExpectedElements
 * @var HealthCheckItem[] $checkCommands
 * @var HealthCheckItem[] $checkConstants
 * @var HealthCheckItem $checkDatabaseSchema
 * @var HealthCheckItem $checkDatabaseEncoding
 * @var HealthCheckItem $checkCrashedTables
 * @var HealthCheckItem $checkMissingConnectors
 * @var HealthCheckItem $checkMissingModules
 * @var array $feature_toggle
 * @var bool $display_feature_toggle_in_test_page
 */

/** @var VersionAPIController $versionController */
$versionController = $this->getAPIController('Version');
$manifest_info = $versionController->get();
?>

<div class="box">
    <h2 id="desc-version-info-table">Information de version</h2>
    <table class='table table-striped' aria-labelledby="desc-version-info-table">
        <tr>
            <th class="w140" scope="row">Version</th>
            <td><?php echo $manifest_info['version']; ?></td>
        </tr>
        <tr>
            <th class="w140" scope="row">Révision</th>
            <td><?php echo $manifest_info['revision']; ?></td>
        </tr>
        <tr>
            <th class="w140" scope="row">Date du commit</th>
            <td><?php echo $manifest_info['last_changed_date']; ?></td>
        </tr>
        <tr>
            <th class="w140" scope="row">Versions compatibles pour les modules</th>
            <td>
                <ul>
                    <?php foreach ($manifest_info['extensions_versions_accepted'] as $version_module) : ?>
                        <li><?php hecho($version_module) ?></li>
                    <?php endforeach; ?>
                </ul>
            </td>
        </tr>
    </table>
</div>

<div class="box">
    <h2 id="desc-pack-list-table">Liste des Packs</h2>
    <table class='table table-striped' aria-labelledby="desc-pack-list-table">
        <?php foreach ($listPack as $pack => $enabled) : ?>
            <tr>
                <th class="w140" scope="row"><?php hecho($pack); ?></th>
                <td>
                    <?php if ($enabled) : ?>
                        <p class="alert alert-success">
                            <strong>Activé</strong>
                        </p>
                    <?php else : ?>
                        <p class="alert alert-warning">
                            <strong>Inactif</strong>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2 id="desc-workspace-table">Workspace</h2>
    <table class='table table-striped' aria-labelledby="desc-workspace-table">
        <?php foreach ($checkWorkspace as $workSpace) : ?>
            <tr>
                <th class='w400' scope="row"><?php hecho($workSpace->label); ?></th>
                <td>
                    <?php if ($workSpace->isInfo()) : ?>
                        <?php hecho($workSpace->result); ?>
                    <?php elseif ($workSpace->isSuccess()) : ?>
                        <strong style='color:green'><?php hecho($workSpace->result); ?></strong>
                    <?php else : ?>
                        <strong style='color:red'><?php hecho($workSpace->result); ?></strong>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2 id="desc-journal-table">Journal</h2>
    <table class='table table-striped' aria-labelledby="desc-journal-table">
        <?php foreach ($checkJournal as $journal) : ?>
            <tr>
                <th class='w400' scope="row"><?php hecho($journal->label); ?></th>
                <td>
                    <?php if ($journal->isInfo()) : ?>
                        <?php hecho($journal->result); ?>
                    <?php elseif ($journal->isSuccess()) : ?>
                        <p class="badge badge-success"><?php hecho($journal->result); ?></p>
                    <?php else : ?>
                        <p class="badge badge-danger"><?php hecho($journal->result); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2 id="desc-redis-table">Redis</h2>
    <table class='table table-striped' aria-labelledby="desc-redis-table">
        <?php foreach ($checkRedis as $redis) : ?>
            <tr>
                <th class='w400' scope="row"><?php hecho($redis->label); ?></th>
                <td>
                    <?php if ($redis->isInfo()) : ?>
                        <?php hecho($redis->result); ?>
                    <?php elseif ($redis->isSuccess()) : ?>
                        <strong style='color:green'><?php hecho($redis->result); ?></strong>
                    <?php else : ?>
                        <strong style='color:red'><?php hecho($redis->result); ?></strong>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <form action='<?php $this->url("System/emptyCache"); ?>' method='post'>
        <?php $this->displayCSRFInput() ?>
        <button type="submit" class="btn btn-warning">
            <em class="fa fa-trash"></em>&nbsp;Vider le cache
        </button>
    </form>
</div>

<div class="box">
    <h2 id="desc-php-conf-table">Configuration PHP</h2>
    <table class='table table-striped' aria-labelledby="desc-php-conf-table">
        <tr>
            <th class="w300" scope="col">clé</th>
            <th class="w300" scope="col">Valeurs minimums attendues</th>
            <th scope="col">Valeurs présentes</th>
        </tr>
        <?php foreach ($checkPhpConfiguration as $phpConf) : ?>
            <tr>
                <td><?php hecho($phpConf->label); ?></td>
                <td><?php hecho($phpConf->expectedValue); ?></td>
                <td>
                    <strong style='color:<?php echo $phpConf->isSuccess() ? 'green' : 'red' ?>'>
                        <?php hecho($phpConf->result); ?>
                    </strong>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <th scope="col">phpinfo()</th>
            <td><a href="<?php $this->url("/System/phpinfo"); ?>">Voir le phpinfo()</a></td>
        </tr>
    </table>
</div>

<div class="box">
    <h2>Extensions PHP</h2>
    <?php foreach ($checkPhpExtensions as $extension) : ?>
        <?php if ($extension->isSuccess()) : ?>
            <p class="badge badge-success"><?php hecho($extension->result); ?></p>
        <?php else : ?>
            <p class="badge badge-danger"><?php hecho($extension->result); ?></p>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<div class="box">
    <h2 id="desc-expected-elements-table">Éléments attendus</h2>

    <table class='table table-striped' aria-labelledby="desc-version-info-table">
        <tr>
            <th class="w300" scope="col">Élément</th>
            <th class="w300" scope="col">Attendu</th>
            <th scope="col">Trouvé</th>
        </tr>
        <?php foreach ($checkExpectedElements as $phpConf) : ?>
            <tr>
                <th scope="row"><?php hecho($phpConf->label); ?></th>
                <td><?php hecho($phpConf->expectedValue); ?></td>
                <td>
                    <strong style='color:<?php echo $phpConf->isSuccess() ? 'green' : 'red' ?>'>
                        <?php hecho($phpConf->result); ?>
                    </strong>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2 id="desc-existing-commands-table">Commande présente</h2>

    <table class='table table-striped' aria-labelledby="desc-existing-commands-table">
        <tr>
            <th class="w140" scope="col">Commande</th>
            <th scope="col">Résultat</th>
        </tr>
        <?php foreach ($checkCommands as $command) : ?>
            <tr>
                <th scope="row"><?php hecho($command->label); ?></th>
                <td>
                    <?php if ($command->isSuccess()) : ?>
                        <?php hecho($command->result); ?>
                    <?php else : ?>
                        <strong style='color:red'><?php hecho($command->result); ?></strong>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2 id="desc-constants-table">Constante</h2>
    <table class='table table-striped' aria-labelledby="desc-constants-table">
        <tr>
            <th class="w140" scope="col">Élément</th>
            <th scope="col">Valeur</th>
        </tr>
        <?php foreach ($checkConstants as $constant) : ?>
            <tr>
                <th scope="row"><?php hecho($constant->label); ?></th>
                <td><?php hecho($constant->result); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php if ($display_feature_toggle_in_test_page) : ?>
<div class="box">
    <h2 id="desc-constants-table">Fonctionnalités activables</h2>
    <table class='table table-striped' aria-labelledby="desc-constants-table">
        <tr>
            <th class="w140" scope="col">Nom de la fonctionnalité</th>
            <th class="w300" scope="col">Description de la fonctionnalité</th>
            <th scope="col">Activé</th>
            <th scope="col">Activé (valeur par défaut)</th>
        </tr>
        <?php foreach ($feature_toggle as $feature_name => $feature_properties) : ?>
            <tr>
                <th scope="row"><?php hecho($feature_name); ?></th>
                <td><?php hecho($feature_properties['description']); ?></td>
                <td>
                    <?php if ($feature_properties['is_enabled']) : ?>
                        <p class="badge badge-success">Activé</p>
                    <?php else : ?>
                        <p class="badge badge-info">Désactivé</p>
                    <?php endif ?>
                </td>
                <td>
                    <?php if ($feature_properties['is_enabled_by_default']) : ?>
                        <p class="badge badge-success">Activé</p>
                    <?php else : ?>
                        <p class="badge badge-info">Désactivé</p>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

<div class="box">
    <h2 id="desc-auto-test-table">Auto test</h2>
    <table class='table table-striped' aria-labelledby="desc-auto-test-table">
        <tr>
            <th scope="row"><?php hecho($checkDatabaseSchema->label); ?></th>
            <td>
                <?php if ($checkDatabaseSchema->isSuccess()) : ?>
                    <strong style='color:green'>
                        <?php hecho($checkDatabaseSchema->result); ?>
                    </strong>
                <?php else : ?>
                    <strong style='color:red'>
                        <?php hecho($checkDatabaseSchema->result); ?>
                    </strong>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php hecho($checkDatabaseEncoding->label); ?></th>
            <td>
                <?php if ($checkDatabaseEncoding->isSuccess()) : ?>
                    <strong style='color:green'>
                        <?php hecho($checkDatabaseEncoding->result); ?>
                    </strong>
                <?php else : ?>
                    <strong style='color:red'>
                        <?php hecho($checkDatabaseEncoding->result); ?>
                    </strong>
                    <table aria-label="Liste des tables avec leur encodage">
                        <?php foreach ($checkDatabaseEncoding->getDetails() as $detail) : ?>
                            <tr>
                                <th scope="row"><?php hecho($detail->label) ?></th>
                                <td>
                                    <?php hecho($detail->result); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php hecho($checkCrashedTables->label); ?></th>
            <td>
                <?php if ($checkCrashedTables->isSuccess()) : ?>
                    <strong style='color:green'>
                        <?php hecho($checkCrashedTables->result); ?>
                    </strong>
                <?php else : ?>
                    <table aria-label="Liste des tables avec leurs erreurs">
                        <tr>
                            <th scope="col">Nom de la table</th>
                            <th scope="col">Erreur(s) détectée(s)</th>
                        </tr>
                        <?php foreach ($checkCrashedTables->getDetails() as $detail) : ?>
                            <tr>
                                <td><?php hecho($detail->label); ?></td>
                                <td><strong style="color:red"><?php hecho($detail->result); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php hecho($checkMissingConnectors->label); ?></th>
            <td>
                <?php if ($checkMissingConnectors->isSuccess()) : ?>
                    <strong style='color:green'><?php hecho($checkMissingConnectors->result); ?></strong>
                <?php else : ?>
                    <strong style='color:red'><?php hecho($checkMissingConnectors->result); ?></strong>
                    <br/>
                    <a
                            href="<?php $this->url("System/missingConnecteur"); ?>"
                            class="btn btn-primary"
                    >
                        <i class="fa fa-eye"></i>&nbsp;Voir
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php hecho($checkMissingModules->label); ?></th>
            <td>
                <?php if ($checkMissingModules->isSuccess()) : ?>
                    <strong style='color:green'><?php hecho($checkMissingModules->result); ?></strong>
                <?php else : ?>
                    <strong style='color:red'><?php hecho($checkMissingModules->result); ?></strong>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>

<div class='box'>
    <h2 id="desc-send-mail-table">Envoi de mail</h2>
    <div class='alert alert-info'>Permet d'envoyer un <strong>email de test</strong></div>
    <form action='<?php $this->url("System/mailTest"); ?>' method='post'>
        <?php $this->displayCSRFInput() ?>
        <table class='table table-striped' aria-labelledby="desc-send-mail-table">
            <tr>
                <th class="w200" scope="row">ADMIN_EMAIL</th>
                <td> <?php echo ADMIN_EMAIL ?></td>
            </tr>
            <tr>
                <th class="w200" scope="row">PLATEFORME_MAIL</th>
                <td> <?php echo PLATEFORME_MAIL ?></td>
            </tr>
            <tr>
                <th class="w200" scope="row">
                    <label for='email'>Email</label>
                </th>
                <td>
                    <input
                            class="form-control col-md-4"
                            style='width:200px'
                            type='text'
                            name='email'
                            id="email"
                            value=''
                    />
                </td>
            </tr>
        </table>
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-paper-plane"></i>&nbsp;Envoyer
        </button>
    </form>
</div>

<div class='box'>
    <h2>Gestion des erreurs</h2>
    <a
            href="<?php $this->url("System/sendWarning") ?>"
            class="btn btn-warning"
    >
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;Provoquer un warning
    </a>
    <a
            href="<?php $this->url("System/sendFatalError") ?>"
            class="btn btn-danger"
    >
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;Provoquer une erreur fatale
    </a>
</div>
