# Migration d'une version 2.0.X à une version 3.0.Y


## Avant la migration 

### Connecteurs GED dépréciés

Les connecteurs suivants sont dépréciés : 
- ged-ftp => utiliser depot-ftp
- ged-ssh => utiliser depot-sftp
- ged-webdav => utiliser depot-webdav
- smb => utiliser depot-cmis
- recuperation-fichier-local => utiliser glaneur-sftp
- recuperation-fichier-ssh => utiliser glaneur-sftp
- glaneur-local => utiliser glaneur-sftp
- glaneur-doc => utiliser glaneur-sftp
- creation-pes-aller => utiliser glaneur-sftp 
- creation-document => utiliser glaneur-sftp

Bien que les connecteurs ont été mis dans l'extension pastell-compat-v2, il est fortement déconseillé des les utiliser.





## Utilisation des classes de connecteur-type


### Récupération des accusés de reception du SAE (Aknowledgement)

On utilisera désormais : 

```yaml
    verif-sae:
        name-action: Récupérer l'AR du document sur le SAE
        name: Récuperation de l'AR sur le SAE
        rule:
            # some rules
        action-class: StandardAction
        connecteur-type: SAE
        connecteur-type-action: SAEVerifier
```

Classes dépréciées : 

- pdf-generique/action/PDFGeneriqueSAEVerif.class.php
- actes-generique/action/SAEVerif.clasS.php
- helios-generique/action/HeliosGeneriqueSAEVerif.class.php



### Actions dépréciées 

- (actes-generique) EnvoieSAEChange (remplacé par ActesGeneriqueCheminementChange)
- (helios-generique) HeliosEnvoieSAEChange (remplacé par HeliosGeneriqueCheminementChange)



## Pastell Chorus Pro

- glaneur-pivot supprimé => utiliser glaneur-sftp
- passer le script installation/reindex-document.php facture-cpp date_depot
- le "Format du nom du repertoire à créer pour le dépot en GED" n'est plus dans le connecteur parametrage-flux-facture-cpp, il est à renseigner au niveau du connecteur de dépôt

