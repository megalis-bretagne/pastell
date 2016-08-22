<?php
/** @var $id_e_menu */
/** @var array $all_module */
/** @var $type_e_menu */
?>
<div id="main_gauche">

	<h2>Documents</h2>
	<div class="menu">
		<ul>
			<li>
				<a class="dernier" href='<?php $this->url("document/index?id_e=$id_e_menu")?>'>Tous</a>
			</li>
		</ul>
	</div>


	<?php
	foreach($all_module as $type_flux => $les_flux) : ?>
		<h3><?php echo $type_flux  ?></h3>
		<div class="menu">
			<ul>
				<?php foreach($les_flux as $nom => $affichage) : ?>


					<?php
					$array_keys = array_keys($les_flux);
					$last_key = end($array_keys);
					?>
					<?php
					$a_class = "";
					if($nom === $last_key) $a_class = "dernier";
					if ( $type_e_menu == $nom ) $a_class = "actif";

					if( ($nom === $last_key) && ($type_e_menu == $nom) ) $a_class = "actif dernier";
					?>



					<li>
						<a class="<?php echo $a_class ?>" href='<?php $this->url("Document/list?type=$nom&id_e=$id_e_menu"); ?>'>
							<?php echo $affichage ?>
						</a>

					</li>
				<?php endforeach;?>
			</ul>
		</div>
	<?php endforeach;?>


</div><!-- main_gauche  -->