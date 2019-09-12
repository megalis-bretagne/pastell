# Utilisation de l'API Pastell

Ce répertoire contient des exemples d'utilisation de l'API Pastell.
Ils fonctionnent sans aucune bibliothèque externe si ce n'est la bibliothèque Curl.

Vous devez créer un fichier LocalSettings.php dans ce répertoire contenant les informations d'accès au Pastell

```php
<?php
define("PASTELL_URL","https://pastell.partenaires.libriciel.fr"); // URL du serveur Pastell
define("PASTELL_LOGIN","userdemo");
define("PASTELL_PASSWORD","XXXX");
define("PASTELL_ID_E","34"); // Identifiant de l'entité Pastell


```

Présentation des scripts :

- send-actes-generiques : permet d'envoyer un acte au contrôle de légalité via Pastell (circuit Tdt uniquement)
- retrieve-actes-generiques : permet de récupérer l'acquittement du contrôle de légalité



