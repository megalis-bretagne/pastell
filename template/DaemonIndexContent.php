<div class="box">
<h2>D�mon Pastell</h2>

<table class='table'>
<tr>
	<th class="w300">Etat</th>
	<td>
		<?php if ($this->DaemonManager->status()) : ?>
			<span class='alert alert-success'>Le d�mon est actif</span>
			<a href="daemon/daemon-stop.php" class="btn btn-danger">Arr�ter</a>
		<?php else : ?>
			<span class='alert alert-error'>Le d�mon est arr�t�</span>
			<a href="daemon/daemon-start.php" class="btn btn-success">D�marrer</a>
		<?php endif;?>
	</td>
</tr>
<tr>
	<th>PID</th>
	<td><?php echo $daemon_pid?></td>
</tr>
<tr>
	<th>Workers simultan�s maximum</th>
	<td><?php echo NB_WORKERS?></td>
</tr>
<tr>
	<th>Workers en cours d'ex�cution</th>
	<td><?php echo $nb_worker_actif ?></td>
</tr>
<tr>
	<th>Jobs en attente</th>
	<td>
		<?php echo $job_stat_info['nb_wait']?>
	</td>
</tr>
<tr>
	<th>Jobs v�rouill�s</th>
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
	<th>Date du syst�me</th>
	<td>
		<?php echo $this->FancyDate->getDateFr();?>
	</td>
</tr>

</table>
</div>

<?php include(__DIR__."/DaemonJobList.php")?>
