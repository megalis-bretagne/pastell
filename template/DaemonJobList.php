<?php
/** @var array $job_list */
/** @var Gabarit $this */
?>
<div class='box'>
<h2>Liste des jobs</h2>
<table class="table table-striped">
	<tr>
		<th>#ID job</th>
		<th>Type</th>
		<th>Verrouillé</th>
		<th>Entité</th>
		<th>Document / Connecteur</th>
		<th>Etat source<br/>Etat cible</th>
		<th>Premier essai</th>
		<th>Dernier essai</th>
		<th>Nombre d'essais</th>
		<th>Dernier message</th>
		<th>Prochain essai</th>
		<th>Verrou</th>
		<th>#ID worker</th>
		<th>PID worker</th>
		<th>Début worker</th>
	</tr>
	<?php foreach ($job_list as $job_info): ?>
		<tr>
			<td><a href="Daemon/detail?id_job=<?php echo $job_info['id_job'] ?>"><?php echo $job_info['id_job']?></a></td>
			<td><?php echo $job_info['type'] ==Job::TYPE_DOCUMENT?"Document":"Connecteur"?></td>
			<td>
				<?php if ($job_info['is_lock']) : ?>
					<p class='alert alert-error'>OUI  <br/>Depuis le <?php echo $this->FancyDate->getDateFr($job_info['lock_since']);?>
					<a href='<?php $this->url("Daemon/unlock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class=" btn-warning btn">Déverouiller</a></p>
				<?php else: ?>
					<p>NON <a href='<?php $this->url("Daemon/lock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class="btn btn-warning">Verouiller</a></p>
				<?php endif;?>
			</td>
			<td><?php hecho($job_info['id_e'])?></td>
			<td>
				<?php if ($job_info['id_d']) : ?>
					<a href='Document/detail?id_e=<?php echo $job_info['id_e']?>&id_d=<?php echo $job_info['id_d']?>'><?php hecho($job_info['id_d'])?></a>
				<?php endif;?>
				<?php if ($job_info['id_ce']) : ?>
					<a href='<?php $this->url("Connecteur/edition?id_ce={$job_info['id_ce']}")?>'><?php hecho($job_info['id_ce'])?></a>
				<?php endif;?>
			
			</td>
			<td><?php hecho($job_info['etat_source'])?><br/>
			<?php hecho($job_info['etat_cible'])?></td>
			<td><?php echo $this->FancyDate->getDateFr($job_info['first_try']) ?></td>
			<td><?php echo $this->FancyDate->getDateFr($job_info['last_try']) ?></td>
			<td><?php echo $job_info['nb_try'] ?></td>
			<td><?php hecho($job_info['last_message']) ?></td>
			<td>
				<?php echo $this->FancyDate->getDateFr($job_info['next_try']) ?><br/>
				<?php echo $this->FancyDate->getTimeElapsed($job_info['next_try'])?>
			</td>
			<td><?php hecho($job_info['id_verrou']) ?></td>
			<td><?php echo $job_info['id_worker']?></td>
			<td>
				<?php echo $job_info['pid']?>
				<?php if ($job_info['pid']) : ?>
					<?php if (! $job_info['termine']) : ?>
					<a href='<?php $this->url("Daemon/kill?id_worker={$job_info['id_worker']}&return_url={$return_url}") ?>' class='btn btn-danger'>Kill</a>
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