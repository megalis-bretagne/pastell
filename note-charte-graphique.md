Menu principale

- Document (Liste de documents) => pas d'icône trouvé, utilisation de l'icône "home"
- Démon Pastell -> renommage en "Tâches automatiques"
- Configuration -> renommage en "Administration avancée"

Bouton divers : 

- Traitement par lot -> "Exécuter  (tâches multiples)" ? 

- Vider -> "Réinitialiser" ? 


- Provoquer un warning -> fa-exclamation-triangle
- Provoquer une erreur fatale -> fa-bomb




Indicateur :
- Sens du trie : sort-alpha-asc ?   

 
 => j'ai des chevron (normal, actif, hover) sur mon menu de gauche
 
 
```html
    <?php if($id_u):?>
        <button type="submit" class="btn">
            <i class="fa fa-pencil"></i>&nbsp;Modifier
        </button>
    <?php else: ?>
        <button type="submit" class="btn">
            <i class="fa fa-plus"></i>&nbsp;Créer
        </button>
    <?php endif; ?>
    
    
    <button type="submit" class="btn btn-danger">
            <i class="fa fa-trash"></i>&nbsp;Supprimer
       </button>
    
    
    <button type="submit" class="btn">
        <i class="fa fa-plus-circle"></i>&nbsp;Ajouter
    </button>
    
```