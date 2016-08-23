<?php

/** @var $breadcrumbs */
/** @var $display_entite_racine */
/** @var $navigation_url */
/** @var $navigation_all_ancetre */
/** @var $navigation_denomination */

?>

<ul class="breadcrumb">
	<?php if (! $breadcrumbs && ! $navigation_liste_fille) : ?>
		<li class="active">Bienvenue</li>
	<?php else:?>


		<?php if ($display_entite_racine) : ?>
			<li>
					<a href='<?php echo $navigation_url?>'>Entité Racine</a> <span class="divider">/</span>
			</li>

		<?php endif;?>


		<?php foreach( $navigation_all_ancetre as $info_ancetre) : ?>
			<li>
				<a href="<?php echo "$navigation_url&id_e={$info_ancetre['id_e']}"?>">
					<?php echo $info_ancetre['denomination']?>
				</a> <span class="divider">/</span>
			</li>
		<?php endforeach;?>

		<li><b><?php hecho($navigation_denomination) ?></b> <span class="divider">/</span> </li>

		<?php if($navigation_liste_fille) : ?>
			<li>
				<form action='<?php echo $navigation_url?>' method='get' id="bc_form">
					<input type='hidden' name='type' value='<?php echo isset($type)?$type:'' ?>' />
					<select name='id_e' class='zselect_breadcrumb' id='zselect_id_e_bc'>
						<?php foreach($navigation_liste_fille as $fille) : ?>
							<option value='<?php echo $fille['id_e']?>'><?php hecho($fille['denomination']) ?></option>
						<?php endforeach;?>
					</select>
					<input type='submit' value='go' id='zselect_id_e_bc_submit'/>
				</form>
			</li>


		<script>
			$(document).ready(function(){
				$("#zselect_id_e_bc_submit").hide();
				$("#zselect_id_e_bc").change(function(){
					$(this).parents("form").submit();
				});
			});

		</script>
		<?php endif ?>

	<?php endif;?>
</ul>

