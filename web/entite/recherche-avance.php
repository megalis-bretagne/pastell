<?php

require_once( dirname(__FILE__) . "/../init-authenticated.php");


$page_title = "Recherche de fournisseurs";



include( PASTELL_PATH ."/include/haut.php");
?>

<div class="box_info">
<p><strong>Version de d�monstration</strong></p>
<p>La recherche avanc�e est d�sactiv�e pour la version de d�monstration<br/><br/>
<a href='<?php echo SITE_BASE?>/entite/fournisseur.php'>� revenir � la liste des fournisseurs</a>
 </p></div>
<?php 
include( PASTELL_PATH ."/include/bas.php");
