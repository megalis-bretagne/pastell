<?php if ($id_e != 0) : ?>
<div class="box">
	<form action='Document/index' method='get' class="form-inline">
		<input type='hidden' name='id_e' value='<?php echo $id_e?>'/>
		<input type='text' name='search' value='<?php echo $search?>' class="form-control mr-2 col-md-3"/>
		<button type='submit' class='btn btn-primary mr-2'><i class='fa fa-search'></i>Rechercher</button>
        <div class="float_right">
            <a class='btn btn-secondary mr-2' href='<?php $this->url("Document/search?id_e=$id_e"); ?>'>
                <i class="fa fa-search-plus"></i>
                Recherche avancée
            </a>
        </div>
	</form>


</div>
<?php
	$this->SuivantPrecedent($offset,$limit,$count,"Document/index?id_e=$id_e&search=$search");
	$this->render("DocumentListBox");
	endif;
?>

<?php if ($id_e) : ?>
<a class='btn btn-info' href='Journal/index?id_e=<?php echo $id_e?>'><i class='fa fa-list-alt'></i>&nbsp;Voir le journal des événements</a>
<?php endif; ?>
