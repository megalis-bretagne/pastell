<?php
$onglet_tab  = array(
	"Daemon/index"=>"Démon Pastell",
	"Daemon/job"=>"Tous les jobs",
	"Daemon/job?filtre=actif"=>"Jobs actifs",
	"Daemon/job?filtre=lock"=>"Jobs verrouillés",
	"Daemon/job?filtre=wait"=>"Jobs en attente"
);
?>

<div id="main_gauche">

	<h2>Démon</h2>
	<div class="menu">
		<ul>
			<?php foreach ($onglet_tab as $onglet_url => $onglet_name) : ?>
			<li >
				<a <?php echo ($onglet_url == $menu_gauche_select)?'class="actif"':'' ?>
					href='<?php echo $onglet_url?>'>
					<?php echo $onglet_name?>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>



</div><!-- main_gauche  -->