<?php

/** @var Gabarit $this */
?>
<div class="box">
<table class='table'>
<tr>
    <th class="w300">État</th>
    <td>
        <?php if ($this->DaemonManager->status()) : ?>
            <span class='alert alert-success'>Le gestionnaire de tâches est actif</span>
            <a href="<?php $this->url("Daemon/daemonStop") ?>" class="btn btn-danger" id="arreter_deamon" name="arreter_deamon"><i class="fa fa-stop"></i>&nbsp; Arrêter</a>
        <?php else : ?>
            <span class='alert alert-danger'>Le gestionnaire de tâches est arrêté</span>
            <a href="<?php $this->url("Daemon/daemonStart") ?>" class="btn btn-success"><i class="fa fa-play"></i>&nbsp;Démarrer</a>
        <?php endif;?>
    </td>
</tr>
<tr>
    <th>PID</th>
    <td><?php echo $daemon_pid?></td>
</tr>
<tr>
    <th>Processus simultanés maximum</th>
    <td><?php echo NB_WORKERS?></td>
</tr>
<tr>
    <th>Processus en cours d'exécution</th>
    <td><?php echo $nb_worker_actif ?></td>
</tr>
<tr>
    <th>Travaux en attente</th>
    <td>
        <?php echo $job_stat_info['nb_wait']?>
    </td>
</tr>
<tr>
    <th>Travaux suspendus</th>
    <td>
        <?php echo $job_stat_info['nb_lock']?>
        <?php if ($job_stat_info['nb_lock_one_hour']) : ?>
        <span class='alert alert-warning'>
            <a href="Daemon/job?filtre=lock">
                <?php echo $job_stat_info['nb_lock_one_hour'] ?> depuis plus d'une heure !
            </a>
        </span>
        <?php endif; ?>
    </td>
</tr>
<tr>
    <th>Nombre total de travaux</th>
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

<?php include(__DIR__ . "/DaemonJobList.php")?>
