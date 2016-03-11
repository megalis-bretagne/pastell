<a class='btn btn-mini' href='entite/detail.php?id_e=<?php echo $connecteur_entite_info['id_e']?>&page=3'><i class='icon-circle-arrow-left'></i>Revenir à <?php echo $entite_info['denomination']?></a>

<div class="box">
<h2>Connecteur <?php hecho($connecteur_entite_info['type']) ?> - <?php hecho($connecteur_entite_info['id_connecteur'])?> : <?php hecho($connecteur_entite_info['libelle']) ?> 
&nbsp;<a class='btn btn-mini' href="connecteur/edition-modif.php?id_ce=<?php hecho($id_ce) ?>">
Modifier
</a>
</h2>
<?php 

$this->render("DonneesFormulaireDetail");
 
$action_possible = $objectInstancier->ActionPossible->getActionPossibleOnConnecteur($id_ce,$authentification->getId());
 
foreach($action_possible as $action_name) : ?>
<form action='connecteur/action.php' method='post' style='margin-top:10px;'>
	<input type='hidden' name='id_ce' value='<?php echo $id_ce ?>' />
	<input type='hidden' name='action' value='<?php echo $action_name ?>' />
	<input type='submit' class='btn' value='<?php hecho($action->getActionName($action_name)) ?>'/>
</form>
<?php endforeach;?>

</div>



<div class="box">
<h2>Méta-information sur l'instance du connecteur</h2>

	<table class="table table-striped" >
		<tr >
			<th class="w300">Libellé</th>
			<td><?php hecho($connecteur_entite_info['libelle']) ?></td>
		</tr>
		<tr>
			<th>Fréquence d'utilisation</th>
			<td><?php hecho($connecteur_entite_info['frequence_en_minute']) ?> minute<?php echo $connecteur_entite_info['frequence_en_minute']>1?'s':'' ?></td>
		</tr>
		<tr>
			<th>Verrou exclusif</th>
			<td><?php hecho($connecteur_entite_info['id_verrou']?:"(aucun)") ?></td>
		</tr>
	</table>

	<a class='btn' href="connecteur/edition-libelle.php?id_ce=<?php echo $id_ce?>" >
		Modifier
	</a>

</div>

<div class="box">
	<h2>Autre fonctions</h2>

	<a class='btn' href="connecteur/export.php?id_ce=<?php echo $id_ce?>" >
		Exporter
	</a>
	<a class='btn' href="connecteur/import.php?id_ce=<?php echo $id_ce?>" >
		Importer
	</a>

	<a class='btn btn-danger' href="connecteur/delete.php?id_ce=<?php echo $id_ce?>" >
		Supprimer
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
		<th>#ID worker</th>
		<th>PID worker</th>
		<th>Début worker</th>
	</tr>
	<?php foreach ($job_list as $job_info): ?>
		<tr>
			<td><?php echo $job_info['id_job']?></td>
			<td>
				<?php if ($job_info['is_lock']) : ?>
					<p class='alert alert-error'>OUI  <br/>Depuis le <?php echo $this->FancyDate->getDateFr($job_info['lock_since']);?>
					<a href='daemon/unlock.php?id_job=<?php echo $job_info['id_job']?>&return_url=<?php echo $return_url ?>' class=" btn-warning btn">Déverouiller</a></p>
				<?php else: ?>
					<p>NON <a href='daemon/lock.php?id_job=<?php echo $job_info['id_job']?>&return_url=<?php echo $return_url ?>' class="btn btn-warning">Vérouiller</a></p>	
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
			<td><?php echo $job_info['id_worker']?></td>
			<td>
				<?php echo $job_info['pid']?>
				<?php if ($job_info['pid']) : ?>
					<?php if (! $job_info['termine']) : ?>
					<a href='daemon/kill.php?id_worker=<?php echo $job_info['id_worker']?>&return_url=<?php echo $return_url ?>' class='btn btn-danger'>Kill</a>
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
		
		</tr>
	<?php endforeach;?>
</table>


</div>