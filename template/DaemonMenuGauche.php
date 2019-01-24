<?php
$onglet_tab  = array(
	"Daemon/index"=>"Démon Pastell",
	"Daemon/verrou"=>"Vue par verrou",
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
				<i class="fa fa-chevron-right"></i></a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>

    <h2>Configuration</h2>
    <div class="menu">
        <ul>
            <li>
                <a <?php echo ('Daemon/config' == $menu_gauche_select)?'class="actif"':'' ?> href="<?php $this->url("Daemon/config")?>">Fréquence des connecteurs<i class="fa fa-chevron-right"></i></a>
            </li>
        </ul>
    </div>


</div><!-- main_gauche  -->
