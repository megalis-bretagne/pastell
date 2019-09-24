<?php
/**
 * @var Gabarit $this
 * @var array $liste
 */
?>
<div class="box">

<h2><?php hecho($type); ?></h2>

<form action='Document/action' method='post'>
	<?php $this->displayCSRFInput() ?>
	<input type='hidden' name='id_d' value='<?php hecho($id_d); ?>' />
	<input type='hidden' name='id_e' value='<?php hecho($id_e); ?>' />
	<input type='hidden' name='action' value=<?php hecho($action); ?> />

<table class="table table-striped">
	<tr>
		<th>&nbsp;</th>
		<th>Dénomination</th>
		<th>Siren</th>
	</tr>
<?php 
$cpt = 0;
foreach($liste as $i => $entite) : 
	$cpt++;
	?>
	<tr>
		<td class="w30"><input type='checkbox' name='destinataire[]' id="label_denomination_<?php echo $cpt ?>" value='<?php hecho($entite['id_e']); ?>'/></td>
		<td><label for="label_denomination_<?php echo $cpt ?>">	<a href='Entite/detail?id_e=<?php hecho($entite['id_e']); ?>'><?php hecho($entite['denomination']); ?></a></label></td>
		<td>
			<?php echo $entite['siren']?:""?>
		</td>

	</tr>
<?php endforeach; ?>
</table>

<input type='submit' value='Envoyer le document' class='btn' />

</form>
</div>
