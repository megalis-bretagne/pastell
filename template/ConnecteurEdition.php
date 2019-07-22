<?php
/** @var Gabarit $this */
/** @var ConnecteurFrequence $connecteurFrequence */
/** @var array $connecteurFrequenceByFlux */
/** @var array $usage_flux_list */
/** @var array $fieldDataList */
?>
<a class='btn btn-link' href='Entite/connecteur?id_e=<?php echo $connecteur_entite_info['id_e']?>'><i class="fa fa-arrow-left"></i>&nbsp;Retour à la liste des connecteurs</a>

<div class="box">
<h2>Connecteur <?php hecho($connecteur_entite_info['type']) ?> - <?php hecho($connecteur_entite_info['id_connecteur'])?> : <?php hecho($connecteur_entite_info['libelle']) ?>

</h2>
<?php 

$this->render("DonneesFormulaireDetail");


?>
    <?php if ($fieldDataList) : ?>
    &nbsp;<a class='btn btn-primary' href="<?php $this->url("Connecteur/editionModif?id_ce=$id_ce") ?>">
        <i class="fa fa-pencil"></i>&nbsp;Modifier
    </a>
	<?php endif ?>
<?php

$action_possible = $objectInstancier->ActionPossible->getActionPossibleOnConnecteur($id_ce,$authentification->getId());




foreach($action_possible as $action_name) : ?>
    <form action='Connecteur/action' method='post' style='margin-top:10px; ' >
        <?php $this->displayCSRFInput(); ?>
        <input type='hidden' name='id_ce' value='<?php echo $id_ce ?>' />
        <input type='hidden' name='action' value='<?php echo $action_name ?>' />

        <button type='submit' class='btn btn-secondary' >
            <i class="fa fa-cogs"></i>&nbsp; <?php hecho($action->getActionName($action_name)) ?>
        </button>
    </form>
<?php endforeach;?>

</div>


<div class="box">
<h2>Instance du connecteur</h2>
	<table class="table table-striped" >
		<tr >
			<th class="w300">Libellé</th>
			<td><?php hecho($connecteur_entite_info['libelle']) ?></td>
            <td>&nbsp;</td>
		</tr>
        <tr>
            <th>Utilisation</th>
            <td><?php hecho(implode(",",$usage_flux_list)?:"Aucune"); ?></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <th>Fréquence (action du connecteur)</th>
            <td>
                <?php if ($connecteurFrequence->id_cf): ?>
                <a href="<?php $this->url("Daemon/connecteurFrequenceDetail?id_cf=".$connecteurFrequence->id_cf) ?>">
                    <?php echo $connecteurFrequence->getExpressionAsString() ?>
                </a>
                <?php else: ?>
                    <?php echo nl2br($connecteurFrequence->getExpressionAsString()) ?>
                <?php endif ?>
            </td>
            <td>
                <?php hecho($connecteurFrequence->id_verrou) ?>
            </td>
        </tr>
        <?php foreach($connecteurFrequenceByFlux as $flux => $connecteur) : ?>
        <tr>
            <th>Fréquence (<?php hecho($flux) ?>)</th>
            <td>

                <?php if ($connecteur->id_cf): ?>
                    <a href="<?php $this->url("Daemon/connecteurFrequenceDetail?id_cf=".$connecteur->id_cf) ?>">
                        <?php echo nl2br($connecteur->getExpressionAsString()) ?>
                    </a>
                <?php else: ?>
                    <?php echo nl2br($connecteur->getExpressionAsString()) ?>
                <?php endif ?>
                <em>Sauf action particulière</em>
            </td>
            <td>
                <?php hecho($connecteur->id_verrou) ?>
            </td>
        </tr>
        <?php endforeach; ?>

	</table>

	<a class='btn btn-primary' href="<?php $this->url("Connecteur/editionLibelle?id_ce=$id_ce") ?>" >
		<i class="fa fa-pencil"></i>&nbsp;Modifier le libellé
	</a>

    <a class='btn btn-secondary' href="<?php $this->url("Connecteur/export?id_ce=$id_ce") ?>" >
        <i class="fa fa-download"></i>&nbsp;Exporter
    </a>
    <a class='btn btn-secondary' href="<?php $this->url("Connecteur/import?id_ce=$id_ce") ?>" >
        <i class="fa fa-upload"></i>&nbsp;Importer
    </a>

    <a class='btn btn-danger <?php echo $usage_flux_list?'disabled':'' ?>' href="<?php $this->url("Connecteur/delete?id_ce=$id_ce") ?>"
         >
        <i class="fa fa-trash"></i>&nbsp;Supprimer
    </a>
</div>

<div class='box'>
<h2>Travaux programmés</h2>
<table class="table table-striped">
	<tr>
		<th>#ID job</th>
		<th>Verrouillé</th>
		<th>Action</th>
		<th>Premier essai</th>
		<th>Dernier essai</th>
		<th>Nombre d'essais</th>
		<th>Prochain essai</th>
        <th>Verrou</th>
		<th>#ID worker</th>
		<th>PID worker</th>
		<th>Début worker</th>
		<th>Fonction</th>
	</tr>
	<?php foreach ($job_list as $job_info): ?>
		<tr>
			<td><?php echo $job_info['id_job']?></td>
			<td>
				<?php if ($job_info['is_lock']) : ?>
					<p class='alert alert-danger'>OUI  <br/>Depuis le <?php echo $this->FancyDate->getDateFr($job_info['lock_since']);?>
                        <br/>
					<a href='<?php $this->url("Daemon/unlock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class=" btn-warning btn"> <i class="fa fa-unlock"></i>&nbsp;Déverrouiller</a></p>
				<?php else: ?>
					<p>NON
                        <br/><a href='<?php $this->url("Daemon/lock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class="btn btn-warning"><i class="fa fa-lock"></i>&nbsp;Verrouiller</a></p>
				<?php endif;?>
			</td>
			<td><?php hecho($job_info['etat_cible'])?></td>
			<td><?php echo $this->FancyDate->getDateFr($job_info['first_try']) ?></td>
			<td><?php echo $this->FancyDate->getDateFr($job_info['last_try']) ?></td>
			<td><?php echo $job_info['nb_try'] ?></td>
			<td>
				<?php echo $this->FancyDate->getDateFr($job_info['next_try']) ?><br/>
				<?php echo $this->FancyDate->getTimeElapsed($job_info['next_try'])?>
			</td>
            <td>
                <?php hecho($job_info['id_verrou']) ?>
            </td>
			<td><?php echo $job_info['id_worker']?></td>
			<td>
				<?php echo $job_info['pid']?>
				<?php if ($job_info['pid']) : ?>
					<?php if (! $job_info['termine']) : ?>
					<a href='<?php $this->url("Daemon/kill?id_worker={$job_info['id_worker']}&return_url={$return_url}") ?>' class='btn btn-danger'>
                        <i class="fa fa-power-off"></i>&nbsp;
                        Tuer</a>
					<?php else: ?>
					<br/><?php echo $job_info['message']?>
					<?php endif;?>
				<?php endif;?>
			</td>
			<td>
				<?php if ($job_info['id_worker']) : ?>
					<?php echo $this->FancyDate->getDateFr($job_info['date_begin'])?><br/><?php echo $this->FancyDate->getTimeElapsed($job_info['date_begin'])?>
				<?php endif;?>
			</td>
			<td>
				<a href="Daemon/deleteJob?id_job=<?php echo $job_info['id_job'] ?>&id_ce=<?php echo $job_info['id_ce'] ?>" class="btn btn-danger"><i class="fa fa-trash"></i>&nbsp;Supprimer</a>
			</td>
		</tr>
	<?php endforeach;?>
</table>


</div>