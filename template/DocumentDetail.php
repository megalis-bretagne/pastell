<?php
/** @var Gabarit $this */
/** @var ActionPossible $actionPossible */
?>
<a class='btn btn-link' href='Document/list?type=<?php echo $info['type']?>&id_e=<?php echo $id_e?>&last_id=<?php echo $id_d ?>'>
<i class="fa fa-arrow-left"></i>&nbsp;Liste des "<?php echo $documentType->getName() ?>" de <?php echo $infoEntite['denomination']?></a>


<?php if ($donneesFormulaire->getNbOnglet() > 1): ?>
		<ul class="nav nav-tabs" style="margin-top:10px;">
			<?php foreach ($donneesFormulaire->getOngletList() as $page_num => $name) : ?>
				<li class="nav-item" >
					<a class="nav-link <?php echo ($page_num == $page)?'active':'' ?>" href='<?php $this->url("Document/detail?id_d=$id_d&id_e=$id_e") ?>&page=<?php echo $page_num?>'>
					<?php echo $name?>
					</a>
				</li>
			<?php endforeach;?>
		</ul>
<?php endif; ?>

<div class="box">

<?php
$this->render("DonneesFormulaireDetail");
?>


<table>
<tr>
<?php foreach($actionPossible->getActionPossible($id_e,$authentification->getId(),$id_d) as $action_name) :
if ($theAction->getProperties($action_name,'no-show')){
continue;
}
?>
<td>
<form action='Document/action' method='post' >
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_d' value='<?php echo $id_d ?>' />
	<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
	<input type='hidden' name='page' value='<?php echo $page ?>' />

	<input type='hidden' name='action' value='<?php echo $action_name ?>' />

    <button type="submit" class="btn <?php echo in_array($action_name,["supression","suppression"])?'btn-danger':(in_array($action_name,["modification"])?'btn-primary':'btn-secondary'); ?>"><i class="fa <?php

                $icon= [
                    'supression' => 'fa-trash',
					'suppression' => 'fa-trash',
                    'modification'=>'fa-pencil'
                ];
                if (isset($icon[$action_name])){
                    echo $icon[$action_name];
                } else {
                    echo "fa-cogs";
                }
            ?>
        "></i>&nbsp; <?php hecho($theAction->getDoActionName($action_name)) ?></button>
</form>
</td>
<?php endforeach;?>
</tr>
</table>

</div>

<?php
$infoDocumentEmail = $documentEmail->getInfo($id_d);
if ($infoDocumentEmail) :
	$reponse_column = array();
	foreach($infoDocumentEmail as $i => $infoEmail){
		if ($infoEmail['reponse']){
			$reponse = json_decode($infoEmail['reponse']);
			foreach($reponse as $reponse_key => $reponse_value) {
				if (!in_array($reponse_key, $reponse_column)) {
					$reponse_column[] = $reponse_key;
				}
				$infoDocumentEmail[$i][$reponse_key] = $reponse_value;
			}
		}
	}
?>
<div class="box">
<h2>Utilisateurs destinataires du message</h2>

<table class="table table-striped">
		<tr>
			<th class="w200">Email</th>
			<th>Type</th>
			<th>Date d'envoi</th>
			<th>Dernier envoi</th>
			<th>Nombre d'envois</th>
			<th>Lecture</th>
			<?php foreach($reponse_column as $reponse_column_name): ?>
				<th><?php hecho($reponse_column_name)?></th>
			<?php endforeach; ?>
            <?php if ($document_email_reponse_list):?>
                <th>Réponse</th>
            <?php endif; ?>
			<?php if($actionPossible->isActionPossible($id_e,$this->Authentification->getId(),$id_d,'renvoi')) : ?>
				<th>&nbsp;<th>
			<?php endif;?>

		</tr>

<?php foreach($infoDocumentEmail as $infoEmail) :?>
	<tr>
		<td><?php hecho($infoEmail['email']);?></td>
		<td><?php echo DocumentEmail::getChaineTypeDestinataire($infoEmail['type_destinataire']) ?></td>
		<td><?php echo time_iso_to_fr($infoEmail['date_envoie'])?></td>
		<td><?php echo time_iso_to_fr($infoEmail['date_renvoi'])?></td>
		<td><?php echo $infoEmail['nb_renvoi']?></td>
		<td>
			<?php if ($infoEmail['lu']) : ?>
                <p class="badge badge-success"><?php echo time_iso_to_fr($infoEmail['date_lecture'])?></p>
			<?php elseif($infoEmail['has_error']):?>
                <a href="Document/mailsecError?id_de=<?php hecho($infoEmail['id_de']) ?>&id_e=<?php hecho($id_e)?>" target="_blank">
                    <p class="badge badge-important">Erreur possible !</p>
                </a>
			<?php else: ?>
                Non
			<?php endif;?>
		</td>
		<?php foreach($reponse_column as $reponse_column_name): ?>
			<?php if (isset($infoEmail[$reponse_column_name])) : ?>
				<td><?php hecho($infoEmail[$reponse_column_name])?></td>
			<?php elseif ($infoEmail['type_destinataire'] == "to") : ?>
				<td></td>
			<?php else : ?>
				<td>--</td>
			<?php endif;?>
		<?php endforeach; ?>
		<?php if ($document_email_reponse_list):?>
            <td>
                <?php if (isset($document_email_reponse_list[$infoEmail['id_de']])):
				        $reponse_info = $document_email_reponse_list[$infoEmail['id_de']];
                ?>
                    <a
                            href="<?php $this->url("/Document/detailMailReponse?id_e=$id_e&id_d=$id_d&id_d_reponse={$reponse_info['id_d_reponse']}"); ?>"
                            class="badge <?php echo $reponse_info['is_lu']?:"badge-info" ?>"
                    >
                        <?php hecho($reponse_info['titre']?:"Voir"); ?>
                    </a>
                <?php endif; ?>

            </td>
		<?php endif; ?>

			<?php if($actionPossible->isActionPossible($id_e,$this->Authentification->getId(),$id_d,'renvoi')) : ?>
			<td>
			<form action='Document/action' method='post' >
				<?php $this->displayCSRFInput() ?>
				<input type='hidden' name='id_d' value='<?php echo $id_d ?>' />
				<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
				<input type='hidden' name='id_de' value='<?php echo $infoEmail['id_de']?>' />
				<input type='hidden' name='page' value='<?php echo $page ?>' />
				<input type='hidden' name='action' value='renvoi' />
                <button type="submit" class="btn btn-secondary">
                    <i class="fa fa-cogs"></i>&nbsp;Envoyer à nouveau
                </button>
			</form>
			</td>
		<?php endif;?>
	</tr>
<?php endforeach;?>
</table>
</div>


<?php endif;?>


<div class="box">
<h2>États du dossier</h2>

    <table class="table table-striped">

            <tr>
                <th class="w300">État</th>
                <th class="w200">Date</th>
                <th class="w200">Utilisateur</th>
                <th>Journal</th>
            </tr>

            <?php foreach($documentActionEntite->getAction($id_e,$id_d) as $action) : ?>
                <tr>
                    <td><?php echo $theAction->getActionName($action['action']) ?></td>
                    <td><?php echo time_iso_to_fr($action['date'])?></td>
                    <td>
                        <?php if ($action['id_u'] == 0) : ?>
                            Action automatique
                        <?php endif;?>
                        <?php if ($action['id_e'] == $id_e) :?>
                            <a href='Utilisateur/detail?id_u=<?php echo $action['id_u']?>'><?php echo $action['prenom']?> <?php echo $action['nom']?></a>
                        <?php endif;?>
                    </td>
                    <td>
                        <?php if($action['id_j']) : ?>
                        <a href='Journal/detail?id_j=<?php echo $action['id_j']?>&id_d=<?php echo $id_d ?>&id_e=<?php echo $id_e ?>&type=<?php echo $info['type'] ?>' data-toggle="tooltip" data-placement="right" title="Consulter le détail des événements"><i class="fa fa-eye"></i> </a>
                        <?php endif;?>
                    </td>
                </tr>
            <?php endforeach;?>
    </table>
    <div class="row">
        <div class="col float-right">
            <a class='btn btn-link' href='Journal/index?id_e=<?php echo $id_e?>&id_d=<?php echo $id_d?>&type=<?php echo $info['type'] ?>'><i class='fa fa-list-alt'></i>&nbsp;Voir le journal des événements</a>
        </div>
    </div>

</div>


<?php if ($is_super_admin):?>

<div class="box" >
    <a class="collapse-link" data-toggle="collapse" href="#collapseExample">
        <h2> <i class="fa fa-plus-square"></i>&nbsp;Administration avancée</h2>
    </a>

<div class="collapse"   id="collapseExample">
<?php if($job_list):?>
    <div class='box'>
        <h3>Travaux programmés</h3>
        <table class="table table-striped">
            <tr>
                <th>#ID travail</th>
                <th>Suspendu</th>
                <th>Etat source<br/>Etat cible</th>
                <th>Premier essai</th>
                <th>Dernier essai</th>
                <th>Nombre d'essais</th>
                <th>Dernier message</th>
                <th>Prochain essai</th>
                <th>Verrou</th>
                <th>#ID processus</th>
                <th>PID processus</th>
                <th>Début processus</th>
                <th>Fonction</th>
            </tr>
			<?php foreach ($job_list as $job_info): ?>
                <tr>
                    <td><?php echo $job_info['id_job']?></td>
                    <td>
						<?php if ($job_info['is_lock']) : ?>
                            <p class='alert alert-danger'>OUI  <br/>Depuis le <?php echo $this->FancyDate->getDateFr($job_info['lock_since']);?><br/>
                                <a href='<?php $this->url("Daemon/unlock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class=" btn-warning btn">
                                    <i class="fa fa-unlock-alt"></i>&nbsp;
                                    Reprendre
                                </a></p>
						<?php else: ?>
                            <p>NON <br/>
                                <a href='<?php $this->url("Daemon/lock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class="btn btn-warning">
                                    <i class="fa fa-lock"></i>&nbsp;
                                    Suspendre
                                </a>
                            </p>
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
                    <td>
						<?php hecho($job_info['id_verrou']) ?>
                    </td>
                    <td><?php echo $job_info['id_worker']?></td>
                    <td>
						<?php echo $job_info['pid']?>
						<?php if ($job_info['pid']) : ?>
							<?php if (! $job_info['termine']) : ?>
                                <a href='<?php $this->url("Daemon/kill?id_worker={$job_info['id_worker']}&return_url={$return_url}") ?>' class='btn btn-danger'>
                                    <i class="fa fa-power-off"></i>&nbsp;Tuer
                                </a>
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
                    <td>
                        <a href="Daemon/deleteJobDocument?id_job=<?php echo $job_info['id_job'] ?>&id_e=<?php echo $id_e?>&id_d=<?php echo $id_d?>" class="btn btn-danger">
                            <i class="fa fa-trash"></i>&nbsp;
                            Supprimer
                        </a>
                    </td>
                </tr>
			<?php endforeach;?>
        </table>

		<?php if($droit_erreur_fatale) : ?>
            <form action='Document/action' method='post' >
				<?php $this->displayCSRFInput() ?>
                <input type='hidden' name='id_d' value='<?php echo $id_d ?>' />
                <input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
                <input type='hidden' name='page' value='<?php echo $page ?>' />
                <input type='hidden' name='action' value='fatal-error' />

                <button type='submit' class='btn btn-danger'>
                    <i class="fa fa-exclamation-triangle"></i>&nbsp;Passer en erreur fatale
                </button>
            </form>
		<?php endif;?>

    </div>
<?php endif;?>


<div class="box">
<h3>Modification manuelle de l'état</h3>

<div class='alert alert-danger'>
<b>Attention !</b> Rien ne garantit la cohérence du nouvel état !
</div>
<form action='<?php $this->url("Document/changeEtat"); ?>' method='post'>
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_e' value='<?php echo $id_e?>'/>
	<input type='hidden' name='id_d' value='<?php echo $id_d?>'/>
Nouvel état : <select name='action' class="form-control">
	<option value=''></option>
	<?php foreach($all_action as $etat => $libelle_etat) : ?>
		<option value='<?php echo $etat?>'><?php echo $libelle_etat?> [<?php echo $etat?>]</option>
	<?php endforeach;?>
</select><br/>
Texte à mettre dans le journal : <input class="form-control" type='text' value='' name='message'>
<br/>
    <button type="submit" class="btn btn-danger"><i class="fa fa-floppy-o"></i>&nbsp;Valider le changement d'état</button>


</form>
</div>
</div>
</div>
<?php endif;?>
