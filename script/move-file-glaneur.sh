#! /bin/bash

# TODO à supprimer

INPUT_DIR=$1
OUTPUT_DIR=$2


if [ ! -d "$INPUT_DIR" -o ! -d "$OUTPUT_DIR" ]
then
    echo "Usage $0 INPUT_DIR OUTPUT_DIR"
    echo "Copie les fichiers de INPUT_DIR dans OUTPUT_DIR une fois qu'il n'y a plus d'activité dessus"
    echo "Les fichiers doivent se trouver dans le même filesystem"
    exit -1
fi


#on boucle sur les fichiers régulier de INPUT_DIR
for FILE in $(find "$INPUT_DIR" -type f )
do
    #pour chaque fichier, on regarde si lsof ne renvoi rien
    lsof "$FILE"
    if [ $? -eq 1 ]
    then
        # on move le fichier sur OUTPUT_DIR
        echo "Move $FILE to $OUTPUT_DIR"
        mv $FILE $OUTPUT_DIR
    fi
done


sleep 10



## Exemple à ajouter dans un fichier upstart (en respawn)
## exec sudo -u www-data path_to/move-file_glaneur.sh /data/glaneur/xxx/in_tmp /data/glaneur/xxx/in