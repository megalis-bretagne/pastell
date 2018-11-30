# Migration d'une version 2.0.X à une version 2.1.Y


## Utilisation des classe de connecteur-type

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
