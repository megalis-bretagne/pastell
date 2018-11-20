<?php
/** @var Gabarit $this */
?>
<?php if ($id_d) : ?>
<a class='btn btn-mini' href='Journal/index?id_e=<?php echo $id_e?>'><i class="icon-circle-arrow-left"></i>Journal de <?php echo $infoEntite['denomination']?></a>
<?php endif;?>

<?php if ($roleUtilisateur->hasDroit($authentification->getId(),"journal:lecture",$id_e)) : 
$this->SuivantPrecedent($offset,$limit,$count,"Journal/index?id_e=$id_e&id_u=$id_u&recherche=$recherche&type=$type&id_d=$id_d");

?>
<div class="box">

<h2>Journal des événements (extraits)</h2>


    <form action="Journal/index" method='get' class="form-inline">
		<input type='hidden' name='id_e' value='<?php echo $id_e?>'/>
		<input type='hidden' name='type' value='<?php echo $type?>'/>
		<input type='hidden' name='id_d' value='<?php echo $id_d?>'/>
		<input type='hidden' name='id_u' value='<?php echo $id_u?>'/>
		<input type='text' name='recherche' value='<?php echo $recherche ?>'/>
		<button type='submit' class='btn'><i class='fa fa-search'></i>Rechercher</button>
    </form>

	<br/>


<table class="table table-striped">
	<tr>
		<th>Numéro</th>
		<th>Date</th>
		<th>Type</th>
		<th>Entité</th>
		<th>SIREN</th>
		<th>Utilisateur</th>
		<th>Document</th>
		<th>État</th>
		<th>Message</th>
		<th>Horodatage</th>
	</tr>
<?php foreach($all as $i => $ligne) : ?>
	<tr>
		<td><a href='Journal/detail?id_j=<?php echo $ligne['id_j'] ?>&id_d=<?php echo $id_d?>&type=<?php echo $type ?>&id_e=<?php echo $id_e ?>&offset=<?php echo $offset?>'><?php echo $ligne['id_j']?></a></td>
		<td><?php echo  time_iso_to_fr($ligne['date']) ?></td>
		<td><?php echo $this->Journal->getTypeAsString($ligne['type']) ?></td>
		<td><a href='Entite/detail?id_e=<?php echo $ligne['id_e'] ?>'><?php hecho($ligne['denomination'])?></a></td>
		<td><?php hecho($ligne['siren']) ?></td>
		<td><a href='Utilisateur/detail?id_u=<?php echo  $ligne['id_u']?>'><?php hecho($ligne['prenom'] . " " . $ligne['nom'])?></a></td>
		<td>
			<?php if ($ligne['id_d']) : ?>
			<a href='<?php $this->url("Document/detail?id_d={$ligne['id_d']}&id_e={$ligne['id_e']}"); ?>'>
				<?php echo $ligne['titre']?:$ligne['id_d']?>
			</a>
			<?php else : ?>
				N/A
			<?php endif;?>
		</td>
		<td>
		<?php hecho($ligne['action_libelle']); ?>
		</td>
		
		<td><?php hecho($ligne['message']) ?></td>
		<td><?php if ($ligne['preuve']) : ?> 
			<?php echo time_iso_to_fr($ligne['date_horodatage']) ?>
			<?php else : ?>
			en cours
			<?php endif;?>
		</td>
	</tr>
<?php endforeach;?>
</table>
</div>

<a class='btn' href='Journal/export?format=csv&offset=0&limit=<?php echo $count ?>&id_e=<?php echo $id_e?>&type=<?php echo $type?>&id_d=<?php echo $id_d?>&id_u=<?php echo $id_u ?>&recherche=<?php echo $recherche ?>'><i class='fa fa-download'></i>Exporter (CSV)</a>
<br/><br/>
<?php endif;?>
<?php 
$this->render("EntiteNavigation");


