<?php

/**
 * @var ClassificationActes $classificationActes
 * @var int $id_e
 * @var string $id_d
 * @var int $page
 * @var string $field
 */
?>
<div class="box">
<h2>Classification</h2>
Veuillez sélectionner une classification : 
<?php

$classificationActes->affiche("Document/doExternalData?id_e=$id_e&id_d=$id_d&page=$page&field=$field");?>
</div>
