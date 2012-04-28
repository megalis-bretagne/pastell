<?php
require_once(dirname(__FILE__)."/../init.php");


$paramInfo = array (
		'id_e' => array(
			"required" =>  true,
			"default" => "",
			"comment" => "Identifiant de l'entit� (retourn� par list-entite.php)",
			),
		"type"=>array(
			"required" =>  true,
			"default" => "",
			"comment" => "Type de document (retourn� par document-type.php)",
			),
		"id_d"=>array(
			"required" =>  true,
			"default" => "",
			"comment" => "Identifiant unique du document  (retourn� par list-document.php)",
			),
		"action"=>array(
			"required" =>  true,
			"default" => "",
			"comment" => "Nom de l'action  (retourn� par detail-document.php, champs action-possible)",
			),
		"offset" => array(
			"required" =>  false,
			"default" => "0",
			"comment" => "num�ro de la premi�re ligne � retourner",
			),
		"limit" => array(
			"required" =>  false,
			"default" => "100",
			"comment" => "Nombre maximum de lignes � retourner",
			)
		);
$info = array(
"version" => array(
	"name"=> "Version de l'application",
	"script"=> "version.php",
	"result"=>"version.php",
	"param" => array()
	),
"document-type" => array(
	"name"=> "Types de document support�s par la plateforme",
	"script"=> "document-type.php",
	"result"=>"document-type.php",
	"param" => array()
	),

"document-type-info" => array(
		"name" => "Information sur un type de document",
		"script" => "document-type-info.php",
		"result" => "document-type-info.php?type=actes",
		"param" => array("type" => $paramInfo['type'])
	),
"list-entite" => array(
		"name"=> "Listes des entit�s ",
		"script"=> "list-entite.php",
		"result"=>"list-entite.php",
		"param" => array()
	),
"list-document" => array(
	"name"=> "Listes de documents d'une collectivit�",
	"script"=> "list-document.php",
	"result"=>"list-document.php?id_e=576&type=actes",
	"param" => array("id_e" => $paramInfo['id_e'],
						"type"=>$paramInfo['type'],
						"offset"=>$paramInfo['offset'],
						"limit"=>$paramInfo['limit']),
	),
	
"detail-document" => array(
	"name"=> "D�tail sur un document",
	"script"=> "detail-document.php",
	"result"=>"detail-document.php?id_d=CFA0o0U&id_e=576",
	"param" => array("id_e" => $paramInfo['id_e'],"id_d"=>$paramInfo['id_d']),
	),	
	
"create-document" => array(
	"name"=> "Cr�ation d'un document",
	"script"=> "create-document.php",
	"result"=>"create-document.php?id_e=576&type=test",
	"param" => array("id_e" => $paramInfo['id_e'],"type"=>$paramInfo['type']),
	),
	
	
"external-data" => array(
		"name" => "R�cup�ration des choix possibles pour un champs sp�cial du document",
		"script" => "external-data.php",
		"result" => "external-data.php?id_e=3&id_d=Om70q95&field=type",
		"param" => array("id_e" => $paramInfo['id_e'],"id_d" => $paramInfo['id_d'], "field"=> array("required" => true,"default"=>"","comment"=>"le nom d'un champ du document")),
	),	
	
"modif-document" => array(
	"name"=> "Modification d'un document",
	"script"=> "modif-document.php",
	"result"=>"modif-document.php?id_e=576&id_d=TTlclOA&Test=333",
	"param" => array("id_e" => $paramInfo['id_e'],"id_d"=>$paramInfo['id_d'],"autre" => array("required" => false,"default"=>"","comment"=>"tous les champs du document")),
	),


"action" => array(
	"name"=> "Execute une action sur un document",
	"script"=> "action.php",
	"result"=>"action.php?id_e=576&id_d=TTlclOA&action=test-3",
	"param" => array("id_e" => $paramInfo['id_e'],
				"id_d"=>$paramInfo['id_d'],
				"action"=>$paramInfo['action'],
				"destinataire[]" => array("required" => false,"default"=>"","comment"=>"tableau contenant l'identifiant des destinataires pour les actions qui le requi�rent")
				),	
	),	

	"recuperation-fichier.php" => array(
		"name" => "R�cup�re le contenu d'un fichier",
		"script" => "recuperation-fichier.php",
		"result" => "recuperation-fichier.php?id_d=7GUygPb&id_e=3&field=fichier_pes&num=0",
		"param" => array("id_e" => $paramInfo['id_e'],
						"id_d"=>$paramInfo['id_d'],
						"field"=> array("required" => true,"default"=>"","comment"=>"le nom d'un champ du document"),
						"num" => array("required" => false,"default"=>"0","comment"=>"le num�ro du fichier, s'il s'agit d'un champ fichier multiple")
					)
	),
	
"journal" => array(
	"name" => "R�cup�rer le journal",
	"script"=> "journal.php",
	"result"=>"journal.php",
	"param" => array("id_e" => array(
			"required" =>  false,
			"default" => "",
			"comment" => "Identifiant de l'entit� (retourn� par list-entite.php)",
			),
			"recherche" => array(
			"required" =>  false,
			"default" => "",
			"comment" => "Champs de recherche sur le contenu du message horodat�",
			),
			
			"id_user" => array(
			"required" =>  false,
			"default" => "",
			"comment" => "Identifiant de l'utilisateur",
			),
			
			"id_d"=>array(
			"required" =>  false,
			"default" => "",
			"comment" => "Identifiant unique du document  (retourn� par list-document.php)",
			),"type"=>array(
			"required" =>  false,
			"default" => "",
			"comment" => "Type de document (retourn� par document-type.php)",
			),"format"=>array(
			"required" =>  false,
			"default" => "json",
			"comment" => "Format du journal : json ou bien csv"),
			"offset"=>$paramInfo['offset'],
			"limit"=>$paramInfo['limit']),	
			),
			
	
);

$page_title = "API Pastell";
include( PASTELL_PATH ."/include/haut.php");
?>

<div class="box_contenu clearfix">
<h2>G�n�ralit�s</h2>
<p>

L'authentification � l'API se fait soit : </p>

<ul>
	<li>via un certificat</li>
	<li>via le login/mot de passe Pastell. Celui-ci doit �tre pass� via une authentification HTTP en mode BASIC</li>
</ul>
<p>
Les param�tres peuvent �tre envoy�s en GET ou en POST. Si des fichiers doivent �tre envoy�s, alors 
il faudra utiliser POST.
</p>
</div>
<?php 

foreach($info as $nameRequest => $tabTypeRequest) : ?>
<div class="box_contenu clearfix">

<h2><?php echo $tabTypeRequest['name']?></h2>
Nom du script : <?php echo SITE_BASE ?><?php echo $tabTypeRequest['script']?><br/>
<?php if ($tabTypeRequest['param'] ) : ?>
<table class="tab_04">
	<tr>
		<th>Nom du param�tre</th>
		<th>Obligatoire ? </th>
		<th>Valeur par d�faut</th>
		<th>Commentaire</th>
	</tr>
	<?php foreach($tabTypeRequest['param'] as $name => $value): ?>
	<tr>
		<td><?php echo $name ?></td>
		<td><?php echo $value['required']?"oui":"non"?></td>
		<td><?php echo $value['default']?></td>
		<td><?php echo $value['comment']?></td>
	</tr>
	<?php endforeach;?>
</table>
<br/><br/>
<?php endif;?>
Exemple de r�sultat :
<a href='api/<?php echo $tabTypeRequest['result'] ?>'><?php echo $tabTypeRequest['result']?></a>
</div>

<?php endforeach;?>

<?php include( PASTELL_PATH ."/include/bas.php");
