<?php
//TODO à supprimer

require_once __DIR__ . "/../../init.php";

$csvoutput = new CSVoutput();


$roleSQL = $objectInstancier->getInstance(RoleSQL::class);

$roleDroit = $objectInstancier->getInstance(RoleDroit::class);
$all_droit = $roleDroit->getAllDroit();


foreach ($roleSQL->getAllRole() as $info) {
    $droit = $roleSQL->getDroit($all_droit, $info['role']);
    $droit = array_filter($droit, function ($value) {
        return $value == 1;
    });
    $result['role'][] = ['role' => $info['role'],'libelle' => $info['libelle'],'droit' => $droit];
}


$frequenceSQL = $objectInstancier->getInstance(ConnecteurFrequenceSQL::class);

$result['frequence'] =  $frequenceSQL->getAll();





$connecteurEntiteSQL = $objectInstancier->getInstance(ConnecteurEntiteSQL::class);


$entiteSQL = $objectInstancier->getInstance(EntiteSQL::class);


$connecteurFactory = $objectInstancier->getInstance(ConnecteurFactory::class);


$fluxEntiteSQL  = $objectInstancier->getInstance(FluxEntiteSQL::class);

$entite_list = $entiteSQL->getAll();
array_unshift($entite_list, ['id_e' => 0,'denomination' => 'Entité racine','siren' => '','type' => '']);


foreach ($entite_list as $info) {
    $connecteur_list  = $connecteurEntiteSQL->getAll($info['id_e']);
    array_walk($connecteur_list, function (&$item) {
        $item = ['id_ce' => $item['id_ce'],'type' => $item['type'],'id_connecteur' => $item['id_connecteur'],'libelle' => $item['libelle']];
    });
    foreach ($connecteur_list as $i => $conneteur_info) {
        $config = $connecteurFactory->getConnecteurConfig($conneteur_info['id_ce'])->getRawData() ?: [];

        array_walk($config, function (&$item, $key) {

            if (preg_match("#password#", $key)) {
                $item = "XXXXXXXXXX";
            }
        });
        $connecteur_list[$i]['config'] = $config;
    }

    $flux_entite = $fluxEntiteSQL->getAllFluxEntite($info['id_e']);
    array_walk($flux_entite, function (&$item) {
        $item = ['flux' => $item['flux'],'type' => $item['type'],'id_ce' => $item['id_ce']];
    });

    $result['entite'][] = [
        'id_e' => $info['id_e'],
        'denomination' => $info['denomination'],
        'siren' => $info['siren'],
        'type' => $info['type'],
        'connecteurs' => $connecteur_list,
        'association_flux' => $flux_entite,
        ];
}

$utilisateurListe = $objectInstancier->getInstance(UtilisateurListe::class);

foreach ($utilisateurListe->getAllUtilisateurSimple() as $info) {
    $result['utilisateur'][] = ['id_u' => $info['id_u'],'login' => $info['login'],'email' => $info['email'],'nom' => $info['nom'],'prenom' => $info['prenom'],'id_e' => $info['id_e']];
}



print_r($result);
