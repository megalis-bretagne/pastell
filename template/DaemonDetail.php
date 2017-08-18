<?php
/** @var array $job_info */
/** @var Gabarit $this */
?>
<div class="box">
	<h2>Information sur le job</h2>
	<table class='table'>
		<tr>
			<th>Type</th>
			<td><?php echo $job_info['type']==Job::TYPE_DOCUMENT?"Document":"Connecteur"?></td>
		</tr>
		<tr>
			<th>Entité</th>
			<td><?php echo $job_info['id_e'] ?></td>
		</tr>

		<tr>
			<th>Dernier message</th>
			<td><?php echo $job_info['last_message'] ?></td>
		</tr>

		<tr>
			<th>Verrouillé</th>
			<td>
			<?php if ($job_info['is_lock']) : ?>
				<p class='alert alert-error'>OUI  <br/>Depuis le <?php echo $this->FancyDate->getDateFr($job_info['lock_since']);?>
					<a href='<?php $this->url("Daemon/unlock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class=" btn-warning btn">Déverrouiller</a></p>
			<?php else: ?>
				<p>NON <a href='<?php $this->url("Daemon/lock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class="btn btn-warning">Verrouiller</a></p>
			<?php endif;?>
			</td>
		</tr>
		<tr>
			<th>Document</th>
			<td>
			<?php if ($job_info['id_d']) : ?>
				<a href='Document/detail?id_e=<?php echo $job_info['id_e']?>&id_d=<?php echo $job_info['id_d']?>'><?php hecho($job_info['id_d'])?></a>
			<?php endif;?>
			<?php if ($job_info['id_ce']) : ?>
				<a href='<?php $this->url("Connecteur/edition?id_ce={$job_info['id_ce']}")?>'><?php hecho($job_info['id_ce'])?></a>
			<?php endif;?>

		</td>
		</tr>
		<tr>
			<th>Etat source</th>
			<td>
				<?php hecho($job_info['etat_source'])?>
			</td>
		</tr>
		<tr>
			<th>Etat cible</th>
			<td>
				<?php hecho($job_info['etat_cible'])?>
			</td>
		</tr>
		<tr>
			<th>Premier essai</th>
			<td><?php echo $this->FancyDate->getDateFr($job_info['first_try']) ?></td>
		</tr>
		<tr>
			<th>Dernier essai</th>
		<td><?php echo $this->FancyDate->getDateFr($job_info['last_try']) ?></td>
		</tr>
		<tr>
			<th>Nombre d'essai</th>
		<td><?php echo $job_info['nb_try'] ?></td>
		</tr>
		<tr>
			<th>Prochain essai</th>

			<td>

			<?php echo $this->FancyDate->getDateFr($job_info['next_try']) ?><br/>
			<?php echo $this->FancyDate->getTimeElapsed($job_info['next_try'])?>
		</td>
		</tr>
		<tr>
			<th>Identifiant verrou</th>

			<td><?php hecho($job_info['id_verrou']) ?></td>
		</tr>
	</table>

</div>

