<?php
//TODO à supprimer

require_once(dirname(__FILE__) . "/../../init.php");

$file = file_get_contents($argv[1]);
$result = unserialize($file);

$id_d = $result['id_d'];

$file_content = base64_decode($result['file']);

file_put_contents("/tmp/$id_d.tar.gz", $file_content);

$workspace_path  = WORKSPACE_PATH;

exec("cd $workspace_path && tar xvzf /tmp/$id_d.tar.gz");



foreach ($result['sql'] as $table_name => $table_data) {
    $primary_key = getPrimaryKey($table_name);
    echo "TABLE $table_name - PRIMARY KEY $primary_key\n";
    foreach ($table_data as $ligne_data) {
        if ($primary_key) {
            $pk_value = $ligne_data[$primary_key];
            $sql = "SELECT * FROM $table_name WHERE $primary_key=?";
            $ligne_in_database = $sqlQuery->queryOne($sql, $pk_value);
        } else {
            $column_name = implode("=? AND ", array_keys($ligne_data)) . "=?";
            $sql = "SELECT * FROM $table_name WHERE $column_name";
            $ligne_in_database = (bool)$sqlQuery->query($sql, array_values($ligne_data));
        }
        if ($ligne_in_database) {
            if (isset($pk_value)) {
                echo "Ligne $pk_value : dj prsente\n";
            } else {
                echo "Ligne " . implode(",", array_values($ligne_data)) . "dj prsente\n";
            }
        } else {
            $column_name = implode(",", array_keys($ligne_data));
            $column_name = preg_replace("#key#", '`key`', $column_name);
            $question_mark = implode(',', array_fill(0, count($ligne_data), '?'));

            $sql = "INSERT INTO $table_name ($column_name) VALUES ($question_mark)";
            echo "Ligne $pk_value : a ajout\n";
            echo $sql . "\n";
            $sqlQuery->queryOne($sql, array_values($ligne_data));
        }
    }
}

function getPrimaryKey($table_name)
{
    global $sqlQuery;
    if ($table_name == 'document_entite') {
        return'id_d';
    }

    if ($table_name == 'document_action_entite') {
        return 'id_a';
    }
    if ($table_name == 'annuaire_groupe_contact') {
        return 'id_g';
    }
    $sql = "show columns from $table_name where `Key` = 'PRI'";
    $result = $sqlQuery->query($sql);

    if (count($result) != 1) {
        return false;
    }
    return $result[0]['Field'];
}
