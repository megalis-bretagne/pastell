# Mise à jour de Pastell

## Principes généraux

### Mise à jour de la base de données

Il est nécessaire de passer le script :
```
script/database-update.php
```

Ce script affiche les commandes à passer sur la base de données afin que celle-ci soit conforme aux attentes de Pastell.

Afin de passer automatiquement les commandes sur la base :

```
script/database-update.php do
```

