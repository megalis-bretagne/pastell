# Migration depuis la 3.1.x vers la 4.0

## Extensions

Les fichiers PHP ne sont plus chargés automatiquement par Pastell, un autoloader au niveau de chaque extension est désormais
requis.

Pour chaque extension, Pastell va tenter de charger automatiquement 2 fichiers :
* `autoload.php` (à la racine de l'extension)
* `vendor/autoload.php` si le fichier à la racine n'existe pas

Nous recommandons de toujours créer son propre fichier `autoload.php` à la racine, même si son contenu est simplement :

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
```

### Chargement des fichiers

#### Automatiquement avec composer

La méthode la plus simple est l'utilisation de composer pour générer la liste des fichiers à inclure.

Exemple de fichier `composer.json` :

```json
{
  "name": "<vendor_name>/<my_extension>",
  "version": "1.0.0",
  "autoload": {
    "classmap": [
      "connecteur",
      "connecteur-type",
      "module"
    ]
  }
}
```

Il convient d'adapter les entrées dans le classmap pour refléter les répertoires contenant des fichiers PHP,
il n'est pas nécessaire de mettre les répertoires de sous niveau. Composer ira chercher tous les fichiers récursivement.

Les fichiers chargés par un classmap ne sont pas mis à jour en temps réel (ajout d'un nouveau fichier par exemple).
Il est nécessaire d'exécuter la commande `composer dump-autoload` avant de packager son extension.

La documentation de composer est disponible sur [le site officiel](https://getcomposer.org/doc/).

#### Manuellement

Il est également possible de charger manuellement tous ses fichiers avec des directives `require_once`, exemple  de fichier `autoload.php` :

```php
<?php

require_once __DIR__ . '/connecteur/MyConnector/MyConnector.php';
require_once __DIR__ . '/connecteur/MyConnector/action/MyAction1.php';
require_once __DIR__ . '/connecteur/MyConnector/action/MyAction2.php';
// ...
```
