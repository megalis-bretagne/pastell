<div class="box_suiv">
	<div class="prec">
		<?php if ( $offset) : ?>
			<a href="<?php echo $link ?>offset=<?php echo max(0,$offset-$limit); ?>" class="btn"><i class="fa fa-chevron-left"></i>&nbsp;<?php echo "Page précédente" ?></a>
		<?php else : ?>
			&nbsp;
		<?php endif; ?>
	</div>
	 <div class="milieu"><?php echo sprintf ( $message, ($offset+1), min($offset+$limit,$nb_total),$nb_total ); ?></div>
	 <div class="suiv">
	 	<?php if(($offset+$limit) < $nb_total) : ?>
	 		<a href="<?php echo $link ?>offset=<?php echo $offset+$limit ?>" class="btn"><i class="fa fa-chevron-right"></i>&nbsp;<?php echo "Page suivante" ?></a>
	 	<?php else : ?>
			&nbsp;
		<?php endif; ?>
	 </div>
</div>