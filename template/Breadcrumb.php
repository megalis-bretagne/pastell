<?php

/** @var $breadcrumbs */
/** @var $display_entite_racine */
/** @var $navigation_url */
/** @var $navigation_all_ancetre */
/** @var $navigation_denomination */

?>

<ul class="breadcrumb">
	<?php if (! $breadcrumbs) : ?>
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

		<li><b><?php hecho($navigation_denomination) ?></b></li>

	<?php endif;?>
</ul>

