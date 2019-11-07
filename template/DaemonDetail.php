<?php
/** @var array $job_info */
/** @var Gabarit $this */
?>
<div class="box">
    <h2>Information sur le travail</h2>
    <table class='table'>
        <tr>
            <th>Type</th>
            <td><?php echo $job_info['type'] == Job::TYPE_DOCUMENT ? "Document" : "Connecteur"?></td>
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
            <th>Suspension</th>
            <td>
            <?php if ($job_info['is_lock']) : ?>
                <p class='alert alert-danger'>OUI  <br/>Depuis le <?php echo $this->FancyDate->getDateFr($job_info['lock_since']);?>
                    <a href='<?php $this->url("Daemon/unlock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class=" btn-warning btn">
                        <i class="fa fa-unlock-alt"></i>&nbsp;

                        Reprendre
                    </a></p>
            <?php else : ?>
                <p>NON <a href='<?php $this->url("Daemon/lock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class="btn btn-warning">
                        <i class="fa fa-lock"></i>&nbsp;

                        Suspendre
                    </a></p>
            <?php endif;?>
            </td>
        </tr>
        <tr>
            <?php if ($job_info['id_d']) : ?>
                <th>Document</th>
            <?php else : ?>
                <th>Connecteur</th>
            <?php endif; ?>
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
            <th>File d'attente</th>
            <td><?php hecho($job_info['id_verrou']) ?></td>
        </tr>
    </table>

</div>

