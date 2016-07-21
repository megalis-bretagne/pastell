
# Règles de codage : 

- Les fichiers PHP et YML **doivent** être encodés en UTF-8



# Génération de la documentation de l'API

Il est nécessaire d'installer le produit apidoc.js

A la racine de Pastell : 

ant apidoc

#Configuration du fichier php.ini

## APC(u)

APCU est très vivement recommandé pour les tests et indispensable pour la production : c'est lui qui assure le cache des transformation YML.
La bibliothèque de transformation étant fort lente.


    [apc]
    apc.enable_cli=1



