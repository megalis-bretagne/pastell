<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='<?php $this->url("Document/edition?id_d=$id_d&id_e=$id_e&page=$page"); ?>'><i class='icon-circle-arrow-left'></i>Revenir à l'édition du document <em><?php echo $titre?></em></a>

<div class='box'>
<form action='Document/externalData' method='get' >
	<input type='hidden' name='id_d' value='<?php echo $id_d?>' />
	<input type='hidden' name='id_e' value='<?php echo $id_e?>' />
	<input type='hidden' name='page' value='<?php echo $page?>' />
	<input type='hidden' name='field' value='<?php echo $field?>' />
	
	<input type='text' name='search' value='<?php echo $search?>'/>
	<input type='submit' value='Rechercher' class='btn' />
</form>
</div>

<?php 
$this->SuivantPrecedent($offset,AgentSQL::NB_MAX,$nbAgent,"Document/externalData?id_e=$id_e&id_d=$id_d&page=$page&field=$field");
?>

<div class="box">
<h2>Agent</h2>

<form action='Document/doExternalData' method='post'>
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_d' value='<?php echo $id_d?>' />
	<input type='hidden' name='id_e' value='<?php echo $id_e?>' />
	<input type='hidden' name='page' value='<?php echo $page?>' />
	<input type='hidden' name='field' value='<?php echo $field?>' />

<table class="table table-striped">
	<tr>
		<th>&nbsp;</th>
		<th>Matricule</th>
		<th>Nom </th>
		<th>Prénom </th>
		<th>Statut</th>
		<th>Grade</th>
	</tr>
	<?php foreach ($listAgent as $i => $agent) : ?>
		<tr>
			<td class="w30">				
				<input type='radio' name='id_a' id="label_agent_<?php echo $i ?>" value='<?php echo $agent['id_a']?>'/></td>
			<td><label for="label_agent_<?php echo $i ?>"><?php echo $agent["matricule"] ?></label></td>
			<td><label for="label_agent_<?php echo $i ?>"><?php echo $agent['nom_patronymique'] ?></label></td>
			<td><label for="label_agent_<?php echo $i ?>"><?php echo $agent['prenom'] ?></label></td>
			<td><label for="label_agent_<?php echo $i ?>"><?php echo $agent["emploi_grade_code"] ?></label></td>
			<td><label for="label_agent_<?php echo $i ?>"><?php echo $agent['emploi_grade_libelle'] ?></label></td>
			
		</tr>
	     
	<?php endforeach;?>
</table>

<input type='submit' value='Choisir' class='btn' />

</form>
</div>
