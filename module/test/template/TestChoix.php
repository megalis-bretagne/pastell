<a class='btn btn-mini' href='document/edition.php?id_d=<?php echo $id_d ?>&id_e=<?php echo $id_e?>&page=<?php echo $page ?>'>
	<i class='icon-circle-arrow-left'></i>Revenir à l'édition du document <em><?php echo $id_d?></em></a>



<div class="box">
	<h2>Choix</h2>

	<form action='document/external-data-controler.php' method='post'>
		<input type='hidden' name='id_d' value='<?php echo $id_d?>' />
		<input type='hidden' name='id_e' value='<?php echo $id_e?>' />
		<input type='hidden' name='page' value='<?php echo $page?>' />
		<input type='hidden' name='field' value='<?php echo $field?>' />

		<input type='text' name='choix'  value=''/></td>

		<input type='submit' value='Choisir' class='btn' />

	</form>
</div>
