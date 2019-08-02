<?php
$onglet_tab  = array(
	"Daemon/index"=>"Gestionnaire de tâches",
	"Daemon/verrou"=>"Files d'attente",
	"Daemon/job"=>"Tous les travaux",
	"Daemon/job?filtre=actif"=>"Travaux actifs",
	"Daemon/job?filtre=lock"=>"Travaux suspendus",
	"Daemon/job?filtre=wait"=>"Travaux en attente"
);
?>

<div id="main_gauche">

	<h2>Tâches automatiques</h2>
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
