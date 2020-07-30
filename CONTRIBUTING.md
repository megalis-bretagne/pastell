# Développement

```bash
git clone https://gitlab.libriciel.fr/libriciel/pole-plate-formes/pastell/pastell.git

docker-compose up -d

```


# Stratégie GIT 

- pull (ou clone) et chechout master
- Créer une branche en local 
- Développement, commit sur la branche locale 
- push de la branche local vers origin 
- sur gitlab, on créer un merge request de la branche vers master (avec suppression de la branche)
- en local, on checkout master et on supprime la branche

# Pose de Tag

Si 2.0.n est la dernière version taguée : 

- Développement sur 2.0.(n+1)
- Millestone 2.0.(n+1)
- CHANGELOG 2.0.(n+1)

Pose d'un tag: 

- finaliser le CHANGELOG
- création de la millestone 2.0.(n+2)
- mettre toutes les issues non fermé de 2.0.(n+1) en 2.0.(n+2)
- clore la millestone 2.0.(n+1)
- créer le tag

Communication OTRS:

- lister les tickets en version de correction 2.0.(n+1)
- demande de création de la version actuelle 2.0.(n+1) et de correction 2.0.(n+2)
- envoyer le changelog

# Extensions V2

- Pour une adaptation V1.4 à V2 suivre le guide de migration: https://gitlab.libriciel.fr/pastell/pastell/blob/master/documentation/release-note/version%202.0%20extensions.md
- Mettre à jour le manifest.yml
- Intégration au docker: Voir exemple https://gitlab.libriciel.fr/pastell/pastell-cd31/blob/master/.gitlab-ci.yml
- Sur Le projet GitLab, Settings, CI/CD, Secret variables: Ajouter RESSOURCE_LIBRICIEL_FTP_PASSWD, MATTERMOST_WEBHOOK et SSH_PRIVATE_KEY

