<?php
//TODO à supprimer

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'init.php';

if (count($argv) != 2) {
    echo "{$argv[0]} : Ajoute les notifications mails du flux choisi aux utilisateurs déjà abonnés aux notifications mail sécurisé \n";
    echo "Usage : {$argv[0]} flux_cible\n";
    echo "Exemple : {$argv[0]} convocation\n";
    exit;
}

$notification_source = 'mailsec';
$notification_target = get_argv(1);

$utilisateurList = $objectInstancier->getInstance(UtilisateurListe::class);
$notification = $objectInstancier->getInstance(Notification::class);

$mailActions = ['reception', 'reception-partielle'];
$users = $utilisateurList->getAllUtilisateurSimple();

foreach ($users as $user) {
    $userId = $user['id_u'];
    $userNotifications = $notification->getAll($userId);

    foreach ($userNotifications as $userNotification) {
        if ($userNotification['type'] === $notification_source) {
            foreach ($mailActions as $action) {
                $notification->add(
                    $userId,
                    $userNotification['id_e'],
                    $notification_target,
                    $action,
                    $userNotification['daily_digest']
                );
            }
        }
    }
}
