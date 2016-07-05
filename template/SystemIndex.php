<?php
/** @var Gabarit $this */
?>
<ul class="nav nav-pills">
	<?php foreach ($onglet_tab as $onglet_number => $onglet_name) : ?>
	<li <?php echo ($onglet_number == $page_number)?'class="active"':'' ?>>
		<a href='<?php $this->url("System/index?page_number={$onglet_number}") ?>'>
			<?php echo $onglet_name?>
		</a>
	</li>
	<?php endforeach;?>
</ul>

	
<?php $this->render($onglet_content);?>
