## Développements spécifiques permettant de récupérer les factures Chorus Pro par fournisseur

### Connecteur d'entité Chorus Par CSV:

Ce connecteur permet la récupération et la synchronisation des factures pour plusieurs structures et suivant plusieurs critères (ex: fournisseurs).

- Fichier CSV à renseigner:
Les lignes sont formatés de la manière suivante:
```
"utilisateur technique";"mot de passe";"SIRET de la structure (optionel)";"SIRET du fournisseur (optionel)"
```

Exemple:
```
"DEV_DESTTAA073@cpp2017.fr";"Riuxdnup64157[";"00000000013456";"00000000013357"
"DEV_DESTTAA073@cpp2017.fr";"Riuxdnup64157[";"00000000013456";
"DEV_DESTTAA073@cpp2017.fr";"Riuxdnup64157[";;
"DEV_DESTTAA073@cpp2017.fr";"Riuxdnup64157[";;"00000000013357"
"DEV_DESTTAA036@cpp2017.fr";"Veegeams98364[";"00000000002954";
```

- Le bouton "Interpréter le fichier CSV" permet d'interpréter ce ficher pour récupérer les identifiant chorus suivant les SIRET.
Les lignes sont formatés de la manière suivante:
```
"utilisateur technique";"mot de passe";"identifiant chorus suivant le SIRET de la structure (optionel)";"identifiant chorus suivant SIRET du fournisseur (optionel)"
```

Exemple:
```
DEV_DESTTAA073@cpp2017.fr;Riuxdnup64157[;00000000013456;25784152;00000000013357;25784150
DEV_DESTTAA073@cpp2017.fr;Riuxdnup64157[;00000000013456;25784152;;
DEV_DESTTAA073@cpp2017.fr;Riuxdnup64157[;;;;
DEV_DESTTAA073@cpp2017.fr;Riuxdnup64157[;;;00000000013357;25784150
DEV_DESTTAA036@cpp2017.fr;Veegeams98364[;00000000002954;508402;;
```

- Il est alors possible de "Récupérer et synchroniser les factures"


### Le flux Facture Chorus Pro

Le flux Facture Chorus Pro a été adapté pour une intégration de type "Importation Chorus Pro par CSV".
Cette intégration implique que les factures ne sont pas synchronisés de manière unitaire et que la modification de statut n'est pas remonté sur Chorus.