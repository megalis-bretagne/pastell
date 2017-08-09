<?php
/** @var Gabarit $this */
?>
<a class='btn btn-mini' href='<?php $this->url("Document/edition?id_d=$id_d&id_e=$id_e&page=$page"); ?>'><i class='icon-circle-arrow-left'></i>Retour à l'édition du document <em><?php echo $titre?></em></a>


<div class="box">

 <script>
		  $(document).ready(function(){
		    $("#all_grade").treeview( {collapsed: true,animated: "fast",control: "#container"});
		  });
 		 </script>
 		 <div id='container'>
 		 	<a href='#'>Tout replier</a>
			<a href='#'>Tout déplier</a>
		</div>
<ul  id="all_grade" class="filetree">
<?php foreach($all_grade as $name_filiere => $filiere) : ?>
	<li>
		<b><?php hecho($name_filiere) ?></b>
		<ul>
	<?php foreach($filiere as $name_cadre_emploi => $cadre_emploi) : ?>
		<li>
			<b><?php hecho($name_cadre_emploi) ?></b>
			<ul>
			<?php foreach($cadre_emploi as $libelle) : ?>
				<li><a href='Document/doExternalData?id_e=<?php echo $id_e?>&id_d=<?php echo $id_d?>&field=<?php echo $field ?>&page=<?php echo $page?>&libelle=<?php hecho($libelle)?>'><?php hecho($libelle)?></a></li>
			<?php endforeach;?>
			</ul>
		</li>
	<?php endforeach;?>
		</ul>
	</li>
<?php endforeach;?>
</ul>
</div>