<?php
/** @var Gabarit $this */
?>
<a class='btn btn-link' href='Flux/index?id_e=<?php echo $id_e ?>'><i class="fa fa-arrow-left"></i>&nbsp;Retour à la liste des flux</a>

<div class="box">

<h2>Information sur le connecteur</h2>

<table class='table table-striped'>
<tr>
	<th class='w200'>Entité</th>
	<td>
		<a href='Entite/detail?id_e=<?php echo $id_e?>'><?php hecho($entite_denomination)?></a>
	</td> 
</tr>
<tr>
<th class='w200'>Flux</th>
<td>
	<?php hecho($flux_name)?>
</td>
</tr>
<tr>
<th>Type de connecteur nécessaire</th>
<td>
    <?php hecho($type_connecteur)?>
	<?php if($type_connecteur_info && $type_connecteur_info['connecteur_with_same_type']) : ?>
        (connecteur #<?php echo $num_same_type+1;?>)
	<?php endif; ?>
</td>
<tr>
<th>Connecteur</th>
<td>
<?php if($connecteur_info) : ?>
<a href='<?php $this->url("Connecteur/edition?id_ce={$connecteur_info['id_ce']}") ?>'>
<?php hecho($connecteur_info['libelle'])?>
</a>
	<?php if( $connecteur_info['id_e'] != $id_e): ?>
		&nbsp;(<em>hérité de <a href='Entite/detail?id_e=<?php echo $connecteur_info['id_e']?>'><?php hecho($connecteur_info['denomination'])?></a></em>)
	<?php endif;?>
	
<?php else:?>
aucun connecteur sélectionné
<?php endif;?>
</td>
</tr>
</table>
</div>

<div class='box'>
<h2>Choix du connecteur</h2>

<form action='<?php $this->url("Flux/doEdition") ?>' method='post' >
	<?php $this->displayCSRFInput() ?>
<input type='hidden' name='id_e' value='<?php echo $id_e ?>' />
<input type='hidden' name='flux' value='<?php echo $flux ?>' />
<input type='hidden' name='type' value='<?php echo $type_connecteur ?>' />
<input type='hidden' name='num_same_type' value='<?php echo $num_same_type ?>' />



<table class='table table-striped'>
		<tr>
			<th>Instance du connecteur</th>
			<th>connecteur</th>
			<th>Hérité</th>
		</tr>
		<tr>
			<td><input type='radio' name='id_ce' value='' <?php echo  $connecteur_info?"":"checked='checked'"?>/>
			&nbsp;&nbsp;Aucun</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	<?php foreach($connecteur_disponible as $connecteur) : ?>
		<tr>
			<td>
				<input type='radio' name='id_ce' value='<?php hecho($connecteur['id_ce'])?>' <?php echo  $connecteur_info['id_ce']==$connecteur['id_ce']?"checked='checked'":""?> />
				&nbsp;&nbsp;
				<a href='<?php $this->url("Connecteur/edition?id_ce={$connecteur['id_ce']}")?>'><?php hecho($connecteur['libelle'])?></a>
			</td>
			<td><?php hecho($connecteur['id_connecteur'])?></td>
			<td>
				<?php if($id_e != $connecteur['id_e']) : ?>
					<a href='Entite/detail?id_e=<?php echo $connecteur['id_e']?>'><?php hecho($connecteur['denomination'])?></a>
				<?php else: ?>
					non
				<?php endif;?>
			
			
			</td>
		</tr>
	<?php endforeach;?>
	</table>
    <a class='btn btn-secondary' href='Flux/index?id_e=<?php echo $id_e?>'>
        <i class="fa fa-times-circle"></i>&nbsp;Annuler
    </a>
<button type='submit' class='btn btn-primary'><i class='fa fa-link'></i>&nbsp;Associer</button>
</form>
</div>
