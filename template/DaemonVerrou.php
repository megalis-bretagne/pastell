

<div class='box'>
	<h2>Liste des jobs par verrou et par état</h2>
	<table class="table table-striped">
		<tr>
			<th>Verrou</th>
			<th>État source</th>
			<th>État cible</th>
			<th>Dernier essai</th>
			<th>Nombre total de job</th>
			<th>Nombre de job verrouillés</th>
            <th>Nombre de job en retard</th>
            <th>Action</th>

        </tr>

<?php foreach($job_queue_info_list as $job_queue_list) :?>
	<tr>
		<td><?php hecho($job_queue_list['id_verrou']); ?></td>
		<td><?php hecho($job_queue_list['etat_source']); ?></td>
		<td><?php hecho($job_queue_list['etat_cible']); ?></td>
		<td><?php hecho($job_queue_list['last_try']); ?></td>
		<td><?php hecho($job_queue_list['count']); ?></td>
		<td><?php hecho($job_queue_list['nb_lock']); ?></td>
        <td><?php hecho($job_queue_list['nb_late']); ?></td>
        <td>

            <a href='<?php $this->url("Daemon/lock?id_verrou={$job_queue_list['id_verrou']}&etat_source={$job_queue_list['etat_source']}&etat_cible={$job_queue_list['etat_cible']}&return_url={$return_url}") ?>' class="btn btn-warning">
                <i class="fa fa-lock"></i>&nbsp;
                Verrouiller</a>
            <a href='<?php $this->url("Daemon/unlock?id_verrou={$job_queue_list['id_verrou']}&etat_source={$job_queue_list['etat_source']}&etat_cible={$job_queue_list['etat_cible']}&return_url={$return_url}") ?>' class="btn btn-warning">
                <i class="fa fa-unlock"></i>&nbsp;
                Déverrouiller</a>
        </td>
	</tr>
<?php endforeach; ?>
	</table>
</div>