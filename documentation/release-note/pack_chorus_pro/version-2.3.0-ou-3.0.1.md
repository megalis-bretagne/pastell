Attention, la procédure de migration est spécifique !

1) Arrêter le démon Pastell

2) Installer l'extension

3) Faire passer le script `script/plateform-update/redis-flush-all.php`

4) Faire passer le script de ré-indexation suivant :

```bash
php /var/www/pastell/installation/reindex-document.php facture-cpp date_depot
```

Attention, ce script permet de traiter quelque milliers de transaction par minute (4000 / minutes).
L'opération peut donc être très très longue.

Pour vérifier le nombre de transactions, vous avez la requête suivante qui vous permet d'avoir une idée :

```sql
SELECT count(*) FROM document WHERE type='facture-cpp';
```

et pour vérifier le nombre de documents indexés :

```sql
SELECT count(*) FROM document_index WHERE field_name='date_depot';
``` 

5) Redémarrer le démon