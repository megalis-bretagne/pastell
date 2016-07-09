<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='Document/list?type=<?php echo $info['type']?>&id_e=<?php echo $id_e?>&last_id=<?php echo $id_d ?>'>
<i class="icon-circle-arrow-left"></i>Liste des "<?php echo $documentType->getName() ?>" de <?php echo $infoEntite['denomination']?></a>


<?php if ($donneesFormulaire->getNbOnglet() > 1): ?>
		<ul class="nav nav-pills" style="margin-top:10px;">
			<?php foreach ($donneesFormulaire->getOngletList() as $page_num => $name) : ?>
				<li <?php echo ($page_num == $page)?'class="active"':'' ?>>
					<a href='<?php $this->url("Document/detail?id_d=$id_d&id_e=$id_e") ?>&page=<?php echo $page_num?>'>
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
	
	<input type='submit' class='btn <?php if ($action_name=="supression")  echo 'btn-danger'; ?>' value='<?php hecho($theAction->getDoActionName($action_name)) ?>'/>&nbsp;&nbsp;
</form>
</td>
<?php endforeach;?>
</tr>
</table>

</div>

<?php if($job_list):?>
<div class='box'>
<h2>Travaux programmés</h2>
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
		<th>#ID worker</th>
		<th>PID worker</th>
		<th>Début worker</th>
	</tr>
	<?php foreach ($job_list as $job_info): ?>
		<tr>
			<td><?php echo $job_info['id_job']?></td>
			<td>
				<?php if ($job_info['is_lock']) : ?>
					<p class='alert alert-error'>OUI  <br/>Depuis le <?php echo $this->FancyDate->getDateFr($job_info['lock_since']);?>
					<a href='<?php $this->url("Daemon/unlock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class=" btn-warning btn">Déverouiller</a></p>
				<?php else: ?>
					<p>NON <a href='<?php $this->url("Daemon/lock?id_job={$job_info['id_job']}&return_url={$return_url}") ?>' class="btn btn-warning">Verouiller</a></p>
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
			<td><?php echo $job_info['id_worker']?></td>
			<td>
				<?php echo $job_info['pid']?>
				<?php if ($job_info['pid']) : ?>
					<?php if (! $job_info['termine']) : ?>
					<a href='<?php $this->url("Daemon/kill?id_worker={$job_info['id_worker']}&return_url={$return_url}") ?>' class='btn btn-danger'>Kill</a>
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
	
	<input type='submit' class='btn btn-danger' value='Déclencher une erreur fatale sur le document'/>&nbsp;&nbsp;
</form>
<?php endif;?>

</div>
<?php endif;?>


<div class="box">
<h2>Entité concernée par le document</h2>

<table class="table table-striped">
		<tr>
			<th class="w200">Entité</th>
			<th>Rôle</th>
		</tr>
		
<?php foreach($documentEntite->getEntite($id_d) as $docEntite) : 
	if ($my_role == 'editeur' || $docEntite['role'] == 'editeur' || $docEntite['id_e'] == $id_e) : 
?>
	<tr>
			<td><a href='Entite/detail?id_e=<?php echo $docEntite['id_e'] ?>'><?php echo $docEntite['denomination']?></a></td>
			<td><?php echo $docEntite['role']?></td>
		</tr>
<?php 
	endif;
endforeach;?>

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
				$reponse_key = utf8_decode($reponse_key);
				if (!in_array($reponse_key, $reponse_column)) {
					$reponse_column[] = $reponse_key;
				}
				$infoDocumentEmail[$i][$reponse_key] = utf8_decode($reponse_value);
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
				<?php echo time_iso_to_fr($infoEmail['date_lecture'])?>
			<?php else : ?>
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
				<input type='submit' class='btn btn-mini' value='Envoyer à nouveau'/>&nbsp;&nbsp;
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
			<th class="w200">Entité</th>
			<th class="w200">Utilisateur</th>
			<th>Journal</th>
		</tr>
		
		<?php foreach($documentActionEntite->getAction($id_e,$id_d) as $action) : ?>
			<tr>
				<td><?php echo $theAction->getActionName($action['action']) ?></td>
				<td><?php echo time_iso_to_fr($action['date'])?></td>
				<td><a href='Entite/detail?id_e=<?php echo $action['id_e']?>'><?php echo $action['denomination']?></a></td>
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
					<a href='Journal/detail?id_j=<?php echo $action['id_j']?>'>voir</a>
					<?php endif;?>
				</td>
			</tr>
		<?php endforeach;?>

</table>
</div>

<?php if ($is_super_admin):?>
<div class="box">
<h2>[Admin] Changement manuel de l'état</h2>

<div class='alert alert-danger'>
<b>Attention !</b> Rien ne garantit la cohérence du nouvel état !
</div>
<form action='<?php $this->url("Document/changeEtat"); ?>' method='post'>
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_e' value='<?php echo $id_e?>'/>
	<input type='hidden' name='id_d' value='<?php echo $id_d?>'/>
Nouvel état : <select name='action'>
	<option value=''></option>
	<?php foreach($all_action as $etat => $libelle_etat) : ?>
		<option value='<?php echo $etat?>'><?php echo $libelle_etat?> [<?php echo $etat?>]</option>
	<?php endforeach;?>
</select><br/>
Texte à mettre dans le journal : <input type='text' value='' name='message'>
<br/>
<input type='submit' value='Valider' class='btn btn-danger'/>
</form>
</div>

<?php endif;?>

<a class='btn btn-mini' href='Journal/index?id_e=<?php echo $id_e?>&id_d=<?php echo $id_d?>'><i class='icon-list'></i>Voir le journal des évènements</a>

