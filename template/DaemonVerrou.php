<div class='box'>
    <h2>Liste des travaux par files d'attente et par états</h2>
    <table class="table table-striped">
        <tr>
            <th>File d'attente</th>
            <th>État source</th>
            <th>État cible</th>
            <th>Dernier essai</th>
            <th>Nombre total de travaux</th>
            <th>Nombre de travaux suspendus</th>
            <th>Nombre de travaux en retard</th>
            <th>Action</th>

        </tr>

<?php

foreach ($job_queue_info_list as $job_queue_list) :?>
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
                Suspendre</a>
            <a href='<?php $this->url("Daemon/unlock?id_verrou={$job_queue_list['id_verrou']}&etat_source={$job_queue_list['etat_source']}&etat_cible={$job_queue_list['etat_cible']}&return_url={$return_url}") ?>' class="btn btn-warning">
                <i class="fa fa-unlock-alt"></i>&nbsp;
                Reprendre</a>
        </td>
    </tr>
<?php endforeach; ?>
    </table>
</div>
