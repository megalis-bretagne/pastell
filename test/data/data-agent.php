<?php

function getInTab(array $tab){
	return $tab[mt_rand(0,count($tab) - 1 )];
}

$tab_lettre = array('X','Y','E','T');

$grade = array(
	"adjoint administratif principal de 1�re classe",
	"adjoint administratif principal de 2�me classe",
	"adjoint administratif 1�re classe",
	"adjoint administratif 2�me classe",
	"adjoint technique principal de 1�re classe",
	"adjoint technique principal de 2�me classe",
	"adjoint technique 1�re classe",
	"adjoint technique 2�me classe",
	"technicien de laboratoire de classe exceptionnelle",
	"technicien de laboratoire de classe sup�rieure",
	"technicien de laboratoire de classe normale",
	"secr�taire administratif de classe exceptionnelle",
	"secr�taire administratif de classe sup�rieure",
	"secr�taire administratif de classe normale",
	"conseiller technique de service social",
	"attach� principal",
	"attach�",
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


