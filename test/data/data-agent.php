<?php

function getInTab(array $tab){
	return $tab[mt_rand(0,count($tab) - 1 )];
}

$tab_lettre = array('X','Y','E','T');

$grade = array(
	"adjoint administratif principal de 1ère classe",
	"adjoint administratif principal de 2ème classe",
	"adjoint administratif 1ère classe",
	"adjoint administratif 2ème classe",
	"adjoint technique principal de 1ère classe",
	"adjoint technique principal de 2ème classe",
	"adjoint technique 1ère classe",
	"adjoint technique 2ème classe",
	"technicien de laboratoire de classe exceptionnelle",
	"technicien de laboratoire de classe supérieure",
	"technicien de laboratoire de classe normale",
	"secrétaire administratif de classe exceptionnelle",
	"secrétaire administratif de classe supérieure",
	"secrétaire administratif de classe normale",
	"conseiller technique de service social",
	"attaché principal",
	"attaché",
	"administrateur civil hors classe",
	"administrateur civil",
	"Chef de service",
);


$prenom = explode("\n",file_get_contents("prenom.txt"));
$nom = explode("\n",file_get_contents("nom.txt"));


for ($nb=0; $nb<200; $nb++)  {

	for ($i = 0; $i<8; $i++){
		echo  mt_rand(0,9);
	}
	echo ",";
	
	echo getInTab($prenom);
	
	echo ",";
	echo ucfirst(getInTab($nom));
	echo ",";
	
	if (mt_rand(0,1) == 0){
		echo "titulaire";
		$gradeok = true;
	} else if (mt_rand(0,1) == 0){
		echo "non-titulaire";
		$gradeok = true;
	} else {
		echo "stagiaire";
		$gradeok = false;
	}
	echo ",";
	if ($gradeok){
		echo getInTab($grade);
	}
	echo "\n";

}


