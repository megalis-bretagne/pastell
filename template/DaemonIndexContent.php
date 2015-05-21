<div class="box">
<h2>Démon Pastell</h2>

<table class='table'>
<tr>
	<th class="w300">Etat</th>
	<td>
		<?php if ($this->DaemonManager->status()) : ?>
			<span class='alert alert-success'>Le démon est actif</span>
			<a href="daemon/daemon-stop.php" class="btn btn-danger">Arrêter</a>
		<?php else : ?>
			<span class='alert alert-error'>Le démon est arrêté</span>
			<a href="daemon/daemon-start.php" class="btn btn-success">Démarrer</a>
		<?php endif;?>
	</td>
</tr>
<tr>
	<th>PID</th>
	<td><?php echo $daemon_pid?></td>
</tr>
<tr>
	<th>Workers simultanés maximum</th>
	<td><?php echo NB_WORKERS?></td>
</tr>
<tr>
	<th>Workers en cours d'exécution</th>
	<td><?php echo $nb_worker_actif ?></td>
</tr>
<tr>
	<th>Jobs en attente</th>
	<td>
		<?php echo $job_stat_info['nb_wait']?>
	</td>
</tr>
<tr>
	<th>Jobs vérouillés</th>
	<td>
		<?php echo $job_stat_info['nb_lock']?>	
	</td>
</tr>
<tr>
	<th>Jobs total</th>
	<td>
		<?php echo $job_stat_info['nb_job']?>
	</td>
</tr>
<tr>
	<th>Date du système</th>
	<td>
		<?php echo $this->FancyDate->getDateFr();?>
	</td>
</tr>

</table>
</div>

<div class='box'>
<h2>Jobs</h2>
<p><a href='daemon/job.php'>Voir tous les jobs</a></p>

<table class="table table-striped">
	<tr>
		<th>#ID job</th>
		<th>Type</th>
		<th>Vérouillé</th>
		<th>Entité</th>
		<th>Document</th>
		<th>Etat source</th>
		<th>Etat cible</th>
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
			<td><?php echo $job_info['type'] ==Job::TYPE_DOCUMENT?"Document":"Connecteur"?></td>
			<td>
				<?php if ($job_info['is_lock']) : ?>
					<p class='alert alert-error'>OUI  <br/>Depuis le <?php echo $this->FancyDate->getDateFr($job_info['lock_since']);?>
					<a href='daemon/unlock.php?id_job=<?php echo $job_info['id_job']?>' class=" btn-warning btn">Dévérouiller</a></p>
				<?php else: ?>
					<p>NON <a href='daemon/lock.php?id_job=<?php echo $job_info['id_job']?>' class="btn btn-warning">Vérouiller</a></p>	
				<?php endif;?>
			</td>
			<td><?php hecho($job_info['id_e'])?></td>
			<td><a href='document/detail.php?id_e=<?php echo $job_info['id_e']?>&id_d=<?php echo $job_info['id_d']?>'><?php hecho($job_info['id_d'])?></a></td>
			<td><?php hecho($job_info['etat_source'])?></td>
			<td><?php hecho($job_info['etat_cible'])?></td>
			<td><?php echo $this->FancyDate->getDateFr($job_info['first_try']) ?></td>
			<td><?php echo $this->FancyDate->getDateFr($job_info['last_try']) ?></td>
			<td><?php echo $job_info['nb_try'] ?></td>
			<td>
				<?php echo $this->FancyDate->getDateFr($job_info['next_try']) ?><br/>
				<?php echo $this->FancyDate->getTimeElapsed($job_info['next_try'])?>
			</td>
			<td><?php echo $job_info['id_worker']?></td>
			<td><?php echo $job_info['pid']?></td>
			<td>
				<?php if ($job_info['id_worker']) : ?>
					<?php echo $this->FancyDate->getDateFr($job_info['date_begin'])?><br/><?php echo $this->FancyDate->getTimeElapsed($job_info['date_begin'])?>
				<?php endif;?>
			</td>
		
		</tr>
	<?php endforeach;?>
</table>

</div>