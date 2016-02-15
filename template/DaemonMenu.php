<?php
$onglet_tab  = array("index.php"=>"Démon Pastell","job.php"=>"Tous les jobs","job.php?filtre=actif"=>"Jobs actifs","job.php?filtre=lock"=>"Jobs verrouillés","job.php?filtre=wait"=>"Jobs en attente",);
?>

<ul class="nav nav-pills">
	<?php foreach ($onglet_tab as $onglet_url => $onglet_name) : ?>
	<li <?php echo ($onglet_url == $page_url)?'class="active"':'' ?>>
		<a href='daemon/<?php echo $onglet_url?>'>
			<?php echo $onglet_name?>
		</a>
	</li>
	<?php endforeach;?>
</ul>

	