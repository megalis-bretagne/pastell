Bonjour, 

Le syst�me Pastell vous envoie les notifications suivantes (r�sum� journalier) : 


<?php foreach($info as $info_notification):?>

*******************
<?php echo $info_notification['message']?>

URL de consultation du document : 
<?php echo SITE_BASE ?>/document/detail.php?id_d=<?php echo $info_notification['id_d']."&" ?>id_e=<?php echo $info_notification['id_e']?>

*******************

<?php endforeach;?>


-- 
Pastell - <?php echo SITE_BASE ?>

Vous recevez ce message car vous avez s�lectionner la r�ception d'un r�sum� journalier sur certain type de message .

