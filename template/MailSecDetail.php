<a class='btn btn-mini' href='mailsec/annuaire.php?id_e=<?php echo $info['id_e'] ?>'><i class='icon-circle-arrow-left'></i>Voir la liste des contacts</a>

<div class="box">
<h2>Propriétés</h2>
<table  class="table table-striped">
	<tr>
		<th class="w300">Description</th>
		<td><?php hecho($info['description'])?></td>
	</tr>
	<tr>
		<th class="w300">E-mail</th>
		<td><?php hecho($info['email'])?></td>
	</tr>	
</table>

<?php if ($can_edit) : ?>
<table>
<tr>
<td>
<form action='mailsec/del-contact.php' method='post' >		
	<input type='hidden' name='id_e' value='<?php echo $info['id_e'] ?>' />
	<input type='hidden' name='id_a' value='<?php echo $info['id_a'] ?>' />
	<input type='submit' class='btn btn-danger' value='Supprimer'/>&nbsp;&nbsp;
</form>
</td>
<td>
<form action='mailsec/edit.php' method='get' >		
	<input type='hidden' name='id_a' value='<?php echo $info['id_a'] ?>' />
	<input type='submit' class='btn' value='Modifier'/>
</form>
</td>
</tr>
</table>
<?php endif; ?>

</div>
