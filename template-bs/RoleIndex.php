<div class="box_contenu clearfix">
<h2>Liste des r�les</h2>
	
	<table class="tab_01">
		<tr>
			<th>R�le</th>
			<th>Libell�</th>
		</tr>
	<?php foreach($allRole as $i => $info) : ?>
		<tr class='<?php echo $i%2?'bg_class_gris':'bg_class_blanc'?>'>
			<td><a href='role/detail.php?role=<?php echo  $info['role'] ?>'><?php hecho($info['role']) ?></a></td>
			<td><?php 
			echo $info['libelle'] ?></td>
		</tr>
	<?php endforeach; ?>
	</table>

</div>