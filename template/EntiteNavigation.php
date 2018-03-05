<div class="box">
	<h2>Navigation dans les collectivités</h2>
	
	<table class="table table-striped table-hover table-condensed">
	
	<?php if ($navigation_entite_affiche_toutes) : ?>
		<tr>
		<td>
		<a href='<?php echo $navigation_url?>'>Toutes</a>
		</td>
		</tr>
	<?php endif;?>
	
	<?php foreach($navigation_all_ancetre as $ancetre) : ?>
		<tr>
		<td>
		<img src="img/commun/picto_decal.png" alt="" /><a href='<?php echo $navigation_url ?>&id_e=<?php echo $ancetre['id_e']?>'><?php echo $ancetre['denomination']?></a>
		</td>
		</tr>
	<?php endforeach; ?>
	
	<?php if ($navigation_denomination) : ?>
        <?php if (empty($navigation_all_ancetre)): ?>
            <tr>
                <td>
                    <img src="img/commun/picto_decal.png" alt="" /><b><?php echo $navigation_denomination ?></b>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <td>
                    <img src="img/commun/picto_decal_niv2.png" alt="" /><b><?php echo $navigation_denomination ?></b>
                </td>
            </tr>
        <?php endif;?>
	<?php endif;?>
	
	<?php if (count($navigation_liste_fille)>NB_ENTITE_BEFORE_COLLAPSE) : ?>
		<tr>
			<td>
				<form action='<?php echo $navigation_url?>' method='get'>
				<input type='hidden' name='type' value='<?php echo isset($type)?$type:'' ?>' />
				<select name='id_e' class='zselect_entite' id='zselect_id_e'> 
				<?php foreach($navigation_liste_fille as $fille) : ?>
					<option value='<?php echo $fille['id_e']?>'><?php hecho($fille['denomination']) ?></option>
				<?php endforeach;?>
				</select>
				<input type='submit' value='go' id='zselect_id_e_submit'/>
				</form>
			</td>
		</tr>
	<?php else: ?>
	<?php foreach($navigation_liste_fille as $fille) : ?>
		<tr>
			<td>
				<img src="img/commun/picto_decal_niv2.png" alt="" /><a href='<?php echo $navigation_url ?>&id_e=<?php echo $fille['id_e'] ?>'>
					<?php hecho($fille['denomination']) ?>
				</a>
			</td>
		</tr>
	<?php endforeach;?>
	<?php endif;?>
	</table>
	
</div>

<script>
$(document).ready(function(){
	$("#zselect_id_e_submit").hide();
	$("#zselect_id_e").change(function(){
		$(this).parents("form").submit();
	});
});



</script>
