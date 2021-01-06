# Suivi des évolutions du flux Facture Chorus Fournisseur

## Rappel Flux Facture Chorus fournisseur

- Permet de créer et d'envoyer des factures (PDF ou structuré XML) vers le portail Chorus Pro.
- Lorsque la facture a pu être déposée sur Chorus les fichiers pivot.xml et historique.json sont récupérés.
- La synchronisation de la facture se poursuit si le connecteur de parametrage associé au flux l'indique ("Récupération des status des factures: OUI") et si la facture n'est pas en statut "final" (MISE_EN_PAIEMENT ou REJETEE) sinon la facture passe à l'état "Terminé".
- Lorsque le document est en état "Terminé" il peut être envoyé au SAE.


## Envoi automatique au SAE

***Ticket#2023461 — problème de non versement automatique SAE via connecteur Chorus***

V2.1.1 - 2019-03-26: Flux Facture Chorus fournisseur: Envoi automatique au SAE après l'état "Terminé" #105


## Ajout d'un délai de synchronisation

***Ticket#2026094 — Syncronisation des statuts chorus dans le flux "Facture Chorus Fournisseur"***

V2.2.0 - 2019-04-11: Flux Facture Chorus fournisseur: : la synchronisation s'arrête si la facture ne change pas de statut pendant 30 jours (par défaut) #105


## Prochaine version V2.2.1

### Nouvelle demande: Envoi automatique mais pas systématique au SAE (3 jours)
***Ticket#2027794 — L'activation/desactivation de l'envoi au SAE sur le flux chorus fournisseur n'est pas possible***

Spécifications:

- Ajout d'une case à cocher envoi_sae pour le cheminement des factures (ce choix ne prendra effet que pour les nouveaux documents).
- Au moment du passage à l'état "Terminé", on vérifie si "Transmission au SAE: OUI", dans ce cas c'est la nouvelle action preparation-send-sae qui est appelée.

### Correction
param-chorus-fournisseur, nommage des champs: le yml interprète '-' en '_' #123

## Ticket#2043448 en cours https://otrs.libriciel.fr/otrs/index.pl?Action=AgentTicketZoom;TicketID=43455;ArticleID=
