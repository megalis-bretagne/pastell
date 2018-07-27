<?php
/** @var Gabarit $this */

/** @var VersionAPIController $versionController */
$versionController = $this->getAPIController('Version');
$manifest_info = $versionController->get();

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
		<?php foreach($manifest_info['extensions_versions_accepted'] as $version_module): ?>
			<li><?php hecho($version_module)?></li>
		<?php endforeach;?>
		</ul>
	</td>
</tr>
</table>

</div>


<div class="box">
    <h2>Workspace</h2>
    <table class='table table-striped'>
    <tr>
        <th class='w400'><?php echo WORKSPACE_PATH ?> accessible en lecture/écriture ?</th>
        <td><?php echo $checkWorkspace?"<b style='color:green'>ok</b>":"<b style='color:red'>NON</b>"?></td>
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
            <b style="color:<?php echo $free_space_data['disk_use_too_big']?'red':'green'?>">
				<?php echo $free_space_data['disk_use_percent']; ?>
            </b>
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
                <?php if ($redis_status):?>
                    <b style='color:green'>OK</b>
                <?php else: ?>
                    <b style='color:red'>KO</b> - <?php hecho($redis_last_error) ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th class="w140">Temps de mise en cache (défintion des flux, des connecteurs, ...)</th>
            <td>
				<?php echo TTL_CACHE_DEFINITION_FILE_IN_SECONDS ?> seconde(s)
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
		<?php foreach($check_ini as $key => $data) : ?>
            <tr>
                <td><?php echo $key ?></td>
                <td><?php echo $data['expected']?></td>
                <td >
                    <b style='color:<?php echo $data['is_ok']?'green':'red' ?>'>
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

<table class='table table-striped'>
	<?php foreach($checkExtension as $extension => $is_ok) : ?>
		<tr>
			<th class="w140"><?php echo $extension ?></th>
			<td><?php echo $is_ok?"ok":"<b style='color:red'>CETTE EXTENSION N'EST PAS INSTALLEE</b>"; ?></td>
		</tr>
	<?php endforeach;?>
</table>


</div>

<div class="box">
<h2>Modules PHP</h2>

<table class='table table-striped'>
	<?php foreach($checkModule as $module => $is_ok) : ?>
		<tr>
			<th class="w140"><?php echo $module ?></th>
			<td><?php echo $is_ok?"ok":"<b style='color:red'>CE MODULE N'EST PAS ACCESSIBLE</b>"; ?></td>
		</tr>
	<?php endforeach;?>
</table>
</div>


<div class="box">
    <h2>Classes PHP</h2>

    <table class='table table-striped'>
		<?php foreach($checkClasses as $class => $is_ok) : ?>
            <tr>
                <th class="w140"><?php echo $class ?></th>
                <td><?php echo $is_ok?"ok":"<b style='color:red'>CETTE CLASSE N'EST PAS ACCESSIBLE</b>"; ?></td>
            </tr>
		<?php endforeach;?>
    </table>
</div>

<div class="box">
    <h2>Elements attendus</h2>

    <table class='table table-striped'>
        <tr>
            <th class="w300">Élément</th>
            <th class="w300">Attendu</th>
            <th>Trouvé</th>
        </tr>
		<?php foreach($check_value as $name => $value) : ?>
            <tr>
                <th><?php echo $name?></th>
                <td><?php echo $value[0] ?></td>
                <td>
                    <?php if(preg_match($value[0],$value[1])) : ?>
                        <b style='color:green'><?php echo $value[1] ?></b>
                    <?php else: ?>
                        <b style='color:red'><?php echo $value[1] ?></b>
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
	<?php foreach($commandeTest as $commande => $emplacement) : ?>
	<tr>
		<th><?php echo $commande?></th>
		<td><?php echo $emplacement?:"<b style='color:red'>La commande n'est pas disponible</b>"; ?></td>
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
		<th>Connecteur manquant</th>
		<td>
		<?php if($connecteur_manquant) : ?>
			<b style='color:red'>
				<?php echo  implode(", ",$connecteur_manquant) ?>
			</b>
		<?php else: ?>
			<b style='color:green'>
				aucun
			</b>
		<?php endif;?>
		</td>
	</tr>
	<tr>
		<th>Type de document manquant</th>
		<td>
		<?php if($document_type_manquant) : ?>
			<b style='color:red'>
				<?php echo  implode(", ",$document_type_manquant) ?>
			</b>
		<?php else: ?>
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
	<td> <input style='width:200px' type='text' name='email' value='' /></td>
</tr>
</table>
<input type='submit' class='btn' value="Envoyer" />

</form>
</div>

<div class='box'>
    <h2>Gestion des erreurs</h2>
    <a href="<?php $this->url("System/sendWarning") ?>" class="btn btn-warning">Provoquer un warning</a>
    <a href="<?php $this->url("System/sendFatalError") ?>" class="btn btn-danger">Provoquer une erreur fatale</a>
</div>
