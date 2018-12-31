<?php if ($id_e) : ?>
	<a class='btn btn-link' href='<?php echo "Entite/detail?id_e={$id_e}" ?>'><i class="fa fa-arrow-left"></i>&nbsp;<?php hecho($entite_info['denomination']) ?></a>
<?php else : ?>
	<a class='btn btn-link' href='Entite/detail'><i class="fa fa-arrow-left"></i>&nbsp;<?php echo "Liste des collectivités" ?></a>
<?php endif;?>

<br/><br/>

<ul class="nav nav-pills">
	<?php foreach ($onglet_tab as $onglet_number => $onglet_name) : ?>
	<li <?php echo ($onglet_number == $page)?'class="active"':'' ?>>
		<a href='Entite/import?page=<?php echo $onglet_number?>&id_e=<?php echo $id_e ?>'>
			<?php echo $onglet_name?>
		</a>
	</li>
	<?php endforeach;?>
</ul>



<?php $this->render($template_onglet); ?>


