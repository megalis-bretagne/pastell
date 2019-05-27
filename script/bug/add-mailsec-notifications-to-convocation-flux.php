<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'init.php';

$notification_source = 'mailsec';
$notification_target = 'convocation';

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