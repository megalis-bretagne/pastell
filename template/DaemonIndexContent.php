<?php
/** @var Gabarit $this */
?>
<div class="box">
<h2>Démon Pastell</h2>

<table class='table'>
<tr>
	<th class="w300">Etat</th>
	<td>
		<?php if ($this->DaemonManager->status()) : ?>
			<span class='alert alert-success'>Le démon est actif</span>
			<a href="<?php $this->url("Daemon/daemonStop") ?>" class="btn btn-danger">Arrêter</a>
		<?php else : ?>
			<span class='alert alert-error'>Le démon est arrêté</span>
			<a href="<?php $this->url("Daemon/daemonStart") ?>" class="btn btn-success">Démarrer</a>
		<?php endif;?>
	</td>
</tr>
<tr>
	<th>PID</th>
	<td><?php echo $daemon_pid?></td>
</tr>
<tr>
	<th>Fichier PID</th>
	<td><?php echo $pid_file?></td>
</tr>
<tr>
	<th>Fichier PID accessible en lecture/écriture</th>
	<td><?php echo is_writable($pid_file)?'<b style=\'color:green\'>ok</b>':'<b style=\'color:red\'>ko</b>' ?></td>
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
	<th>Jobs verrouillés</th>
	<td>
		<?php echo $job_stat_info['nb_lock']?>
		<?php if($job_stat_info['nb_lock_one_hour']) : ?>
		<span class='alert alert-warning'>
 			<a href="Daemon/job?filtre=lock">
				<?php echo $job_stat_info['nb_lock_one_hour'] ?> depuis plus d'un heure !
			</a>
		</span>
		<?php endif; ?>
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

<?php include(__DIR__."/DaemonJobList.php")?>
