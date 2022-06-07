<?php

/**
 * @var Gabarit $this
 * @var int $id_e
 * @var array $options
 */

use Pastell\Service\ImportExportConfig\ExportConfigService;

?>


<div class="box">
    <h2>Les éléments suivants vont être exportés</h2>
    <ul>
        <?php if (! empty($exportInfo[ExportConfigService::ENTITY_INFO])) :?>
            <li>
                Export des informations de l'entité :
                <?php hecho($exportInfo[ExportConfigService::ENTITY_INFO]['denomination']) ?>
            </li>
        <?php endif; ?>
        <?php if (! empty($exportInfo[ExportConfigService::ENTITY_CHILD])) :?>
            <li>
                Export de <?php echo count($exportInfo[ExportConfigService::ENTITY_CHILD]) ?> entité(s) fille(s) :
                <ul>
                <?php for ($i = 0; $i < min(10, count($exportInfo[ExportConfigService::ENTITY_CHILD])); $i++) : ?>
                    <li><?php echo $exportInfo[ExportConfigService::ENTITY_CHILD][$i]['denomination']?></li>
                <?php endfor; ?>
                    <?php if (count($exportInfo[ExportConfigService::ENTITY_CHILD]) > 10) : ?>
                    <li>...</li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>
        <?php if (! empty($exportInfo[ExportConfigService::CONNECTOR_INFO])) :?>
            <li>
                Export de <?php echo count($exportInfo[ExportConfigService::CONNECTOR_INFO]) ?> connecteur(s) :
                <ul>
                    <?php for ($i = 0; $i < min(10, count($exportInfo[ExportConfigService::CONNECTOR_INFO])); $i++) : ?>
                        <li><?php echo $exportInfo[ExportConfigService::CONNECTOR_INFO][$i]['libelle']?></li>
                    <?php endfor; ?>
                    <?php if (count($exportInfo[ExportConfigService::CONNECTOR_INFO]) > 10) : ?>
                        <li>...</li>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endif; ?>
    </ul>
</div>

<div class="alert alert-info">
    Le mot de passe demandé permet de protéger le contenu du fichier.
    Il sera nécessaire pour importer à nouveau le connecteur sur un autre Pastell.
</div>
<div class="box">

    <form action='Entite/doExportConfig' method='post'>
        <?php $this->displayCSRFInput() ?>
        <input type='hidden' name='id_e' value='<?php hecho($id_e)?>'>
        <?php foreach (ExportConfigService::getOption() as $id => $label) : ?>
            <input type="hidden" name="<?php hecho($id) ?>" value="<?php hecho($options[$id])?>"/>
        <?php endforeach; ?>
        <table class='table table-striped'>
            <tr>
                <th>
                    <label for='password'>
                        Mot de passe<span class='obl'>*</span>
                    </label>
                </th>
                <td>
                    <div class="input-group">
                        <input
                            id="password"
                            type="password"
                            class="form-control col-md-4 ls-box-input"
                            name="password"
                            value=''
                            minlength="8"
                            required
                        />
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fa fa-eye-slash" onclick="switchInputType('password',this)"></i>
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    <label for='password_check'>
                        Mot de passe (vérification)<span class='obl'>*</span>
                    </label>
                </th>
                <td>
                    <div class="input-group">
                        <input
                            id="password_check"
                            type="password"
                            class="form-control col-md-4 ls-box-input"
                            name="password_check"
                            value=''
                            minlength="8"
                            required
                        />
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fa fa-eye-slash" onclick="switchInputType('password_check',this)"></i>
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <a class='btn btn-outline-primary' href='Entite/exportConfig?id_e=<?php hecho($id_e); ?>'>
            <i class="fa fa-times-circle"></i>&nbsp;Annuler
        </a>

        <button type='submit' class='btn btn-primary'><i class="fa fa-download"></i>&nbsp;Récupérer le fichier</button>

    </form>
</div>
