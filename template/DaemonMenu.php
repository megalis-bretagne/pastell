<?php
$onglet_tab  = array(
	""=>"Démon Pastell",
	"job"=>"Tous les jobs",
	"job?filtre=actif"=>"Jobs actifs",
	"job?filtre=lock"=>"Jobs verrouillés",
	"job?filtre=wait"=>"Jobs en attente"
);
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

	