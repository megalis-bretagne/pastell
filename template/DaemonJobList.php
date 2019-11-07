<?php
/** @var array $job_list */
/** @var Gabarit $this */
?>
<div class='box'>
<h2>
    <?php hecho($sub_title); ?>

</h2>


    <?php if (isset($filtre) && $filtre == 'lock') : ?>
        <a class='btn btn-warning mb-2' href="Daemon/unlockAll"><i class="fa fa-unlock-alt"></i>&nbsp;Reprendre l'exécution de tous les travaux</a>
    <?php endif;?>

<table class="table table-striped">
    <tr>
        <th>#ID travail</th>
        <th>Type</th>
        <th>Suspendu</th>
        <th>Entité</th>
        <th>Dossier / Connecteur</th>
        <th>Etat source<br/>Etat cible</th>
        <th>Premier essai</th>
        <th>Dernier essai</th>
        <th>Nombre d'essais</th>
        <th>Dernier message</th>
        <th>Prochain essai</th>
        <th>File d'attente</th>
        <th>#ID processus</th>
        <th>PID processus</th>
        <th>Début processus</th>
    </tr>
    <?php foreach ($job_list as $job_info) : ?>
        <tr>
            <td><a href="Daemon/detail?id_job=<?php echo $job_info['id_job'] ?>"><?php echo $job_info['id_job']?></a></td>
            <td><?php echo $job_info['type'] == Job::TYPE_DOCUMENT ? "Dossier" : "Connecteur"?></td>
            <td>
                <?php if ($job_info['is_lock']) : ?>
                    <p class='alert alert-danger'>OUI  <br/>Depuis le <?php echo $this->FancyDate->getDateFr($job_info['lock_since']);?>
                    <a href='<?php $this->url("Daemon/unlock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class=" btn-warning btn">
                        <i class="fa fa-unlock-alt"></i>&nbsp;
                        Reprendre
                    </a>
                    </p>
                <?php else : ?>
                    <p>NON <a href='<?php $this->url("Daemon/lock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class="btn btn-warning">
                            <i class="fa fa-lock"></i>&nbsp;
                            Suspendre</a></p>
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
                    <a href='<?php $this->url("Daemon/kill?id_worker={$job_info['id_worker']}&return_url={$return_url}") ?>' class='btn btn-danger'>
                        <i class="fa fa-bolt"></i>&nbsp;
                        Tuer
                    </a>
                    <?php else : ?>
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

<?php if (isset($filtre) && $filtre == 'lock') : ?>
            <a class='btn btn-warning mb-2' href="Daemon/unlockAll"><i class="fa fa-unlock-alt"></i>&nbsp;Reprendre l'exécution de tous les travaux</a>
<?php endif;?>

</div>
