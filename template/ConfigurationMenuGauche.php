<?php
/** @var  $menu_gauche_select */
?>
<div id="main_gauche">
	<h2>Configuration</h2>
	<div class="menu">
		<ul>
			<li>
				<a class="<?php echo $menu_gauche_select=="Role"?"actif":"" ?>" href='<?php $this->url("Role/index")?>'>Rôles</a>
			</li>
			<li>
				<a class="<?php echo $menu_gauche_select=="Extension"?"actif":"" ?>" href='<?php $this->url("Extension/index")?>'>Extensions</a>
			</li>
		</ul>
	</div>

</div>