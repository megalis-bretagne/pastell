<a class='btn btn-mini' href='mailsec/detail.php?id_a=<?php echo $info['id_a'] ?>'><i class='icon-circle-arrow-left'></i><?php echo hecho($info['email']) ?></a>

<div class="box">
<h2>�dition d'un contact</h2>
<form action='mailsec/do-edit-contact.php' method='post' >		
	<input type='hidden' name='id_e' value='<?php echo $info['id_e'] ?>' />
	<input type='hidden' name='id_a' value='<?php echo $info['id_a'] ?>' />
	<table class="table table-striped">
			<tr>
				<th>Description</th>
				<td><input type='text' name='description' value='<?php hecho($info['description']) ?>' /></td>
			</tr>
			<tr>
				<th>Email</th>
				<td><input type='text' name='email' value='<?php echo hecho($info['email']) ?>'/></td>
			</tr>
	</table>
	<button type='submit' class='btn'>Modifier</button>
</form>
</div>