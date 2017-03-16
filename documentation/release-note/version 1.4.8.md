# Ajout de la connexion LDAP

Il faut configurer et associer un connecteur de type *Vérification* dans les connecteurs globaux.

La connexion passe systématiquement par LDAP si un connecteur est associé, sauf pour le login *admin*.

Le connecteur de vérification permet de synchroniser les utilisateurs.

Limitation :

On ne peut utiliser que des utilisateurs rangé de la manière suivante :
champs=%LOGIN%,x=y,z=t,...
Il n'est donc pas possible de récupérer des utilisateurs qui serait dans deux branches distinctes.

# Mise à jour du copyright

# Gestion de la surveillance des connecteurs

Les connecteurs pouvait être vérouillé silencieusement. Maintenant :

* sur la page daemon, on indique le nombre de travaux vérouillés depuis plus d'une heure (situation anormale)
* une pastille affiche le nombre de travaux vérouillés depuis plus d'une heure pour un super admin (system:lecture sur l'entité racine).
* le script installation/test-last-job.php envoie un mail si un connecteur est vérouillé depuis plus d'un heure
* un bouton permet de dévérouiller tous les jobs d'un coup sur la page démon , travaux vérouillés
