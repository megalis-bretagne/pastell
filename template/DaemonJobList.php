
<div class='box'>
<h2>Liste des jobs</h2>
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
					<a href='daemon/unlock.php?id_job=<?php echo $job_info['id_job']?>&return_url=<?php echo $return_url ?>' class=" btn-warning btn">Dévérouiller</a></p>
				<?php else: ?>
					<p>NON <a href='daemon/lock.php?id_job=<?php echo $job_info['id_job']?>&return_url=<?php echo $return_url ?>' class="btn btn-warning">Vérouiller</a></p>	
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