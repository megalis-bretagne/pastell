<?php
/** @var Gabarit $this */
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

    <button type="submit" class="btn <?php echo in_array($action_name,["supression","suppression"])?'btn-danger':'btn-secondary'; ?>"><i class="fa <?php

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

			<?php if($actionPossible->isActionPossible($id_e,$this->Authentification->getId(),$id_d,'renvoi')) : ?>
			<td>
			<form action='Document/action' method='post' >
				<?php $this->displayCSRFInput() ?>
				<input type='hidden' name='id_d' value='<?php echo $id_d ?>' />
				<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
				<input type='hidden' name='id_de' value='<?php echo $infoEmail['id_de']?>' />
				<input type='hidden' name='page' value='<?php echo $page ?>' />
				<input type='hidden' name='action' value='renvoi' />
                <button type="submit" class="btn">
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
<h2>États du document</h2>

    <table class="table table-striped">

            <tr>
                <th class="w200">État</th>
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
                        <a href='Journal/detail?id_j=<?php echo $action['id_j']?>' data-toggle="tooltip" data-placement="right" title="Consulter le détail des événements"><i class="fa fa-eye"></i> </a>
                        <?php endif;?>
                    </td>
                </tr>
            <?php endforeach;?>
    </table>
    <div class="row">
        <div class="col float-right">
            <a class='btn btn-info float-right' href='Journal/index?id_e=<?php echo $id_e?>&id_d=<?php echo $id_d?>'><i class='fa fa-list-alt'></i>&nbsp;Voir le journal des événements</a>
        </div>
    </div>

</div>


<?php if ($is_super_admin):?>
    <a class="btn btn-link" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
        <i class="fa fa-eye"></i>&nbsp;Administration avancée
    </a>
<div class="box collapse" id="collapseExample">
    <h2>Administration avancée</h2>

<?php if($job_list):?>
    <div class='box'>
        <h3>Travaux programmés</h3>
        <table class="table table-striped">
            <tr>
                <th>#ID job</th>
                <th>Verrouillé</th>
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
                <th>Fonction</th>
            </tr>
			<?php foreach ($job_list as $job_info): ?>
                <tr>
                    <td><?php echo $job_info['id_job']?></td>
                    <td>
						<?php if ($job_info['is_lock']) : ?>
                            <p class='alert alert-error'>OUI  <br/>Depuis le <?php echo $this->FancyDate->getDateFr($job_info['lock_since']);?><br/>
                                <a href='<?php $this->url("Daemon/unlock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class=" btn-warning btn">
                                    <i class="fa fa-unlock"></i>&nbsp;
                                    Déverrouiller
                                </a></p>
						<?php else: ?>
                            <p>NON <br/>
                                <a href='<?php $this->url("Daemon/lock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class="btn btn-warning">
                                    <i class="fa fa-lock"></i>&nbsp;
                                    Verrouiller
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
                    <i class="fa fa-bomb"></i>&nbsp;Déclencher une erreur fatale sur le document
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
    <button type="submit" class="btn btn-danger"><i class="fa fa-floppy-o"></i>&nbsp;Enregistrer</button>


</form>
</div>

</div>
<?php endif;?>


