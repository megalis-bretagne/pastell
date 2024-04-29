<?php

class ChorusParCSVListeFacture extends ActionExecutor
{
    /**
     * @return UTF8Encoder
     */
    public function getUTF8Encoder(): UTF8Encoder
    {
        return $this->objectInstancier->getInstance(UTF8Encoder::class);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function metier(): string
    {
        $connecteur_properties = $this->getConnecteurProperties();
        $fichier_csv_interprete = $connecteur_properties->getFilePath('fichier_csv_interprete');
        if (!file_exists($fichier_csv_interprete)) {
            throw new Exception("Il n'y a pas de fichier CSV interprété");
        }
        $CSV = new CSV();
        $colList = $CSV->get($fichier_csv_interprete, ';');
        $result = "";

        foreach ($colList as $col) {
            if (!$col[0]) {
                continue;
            }
            if (count($col) !== 6) {
                $message = 'Les lignes doivent être de la forme ' .
                        '"utilisateur technique";' .
                        '"mot de passe";' .
                        '"SIRET de la structure (optionel)";' .
                        '"Identifiant Chorus de la structure (optionel)";' .
                        '"SIRET du fournisseur (optionel)";' .
                        '"Identifiant Chorus du fournisseur (optionel)"';
                throw new Exception($message);
            }
            // ('user_login', 'user_password',
            // 'siret_structure', 'id_chorus_structure','siret_fournisseur','id_chorus_fournisseur')
            $connecteur_properties->setData('user_login', $col[0]);
            $connecteur_properties->setData('user_password', $col[1]);
            $connecteur_properties->setData('identifiant_structure_cpp', $col[3]);

            /** @var ChorusParCsv $connecteur_chorus */
            $connecteur_chorus = $this->getMyConnecteur();
            $result .= 'Pour la ligne CSV: ' . $col[0] . ";" . $col[2] . ";" . $col[3] . ";" .
                $col[4] . ";" . $col[5] . '<br/>';
            $result .= $this->getUTF8Encoder()->decode(
                json_encode($this->getUTF8Encoder()->encode(
                    $connecteur_chorus->getListeFacturesRecipiendaire($col[5])
                ))
            ) . '<br/>';
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function go(): bool
    {
        try {
            $result = $this->metier();
        } catch (Exception $ex) {
            $this->setLastMessage(
                "La liste des factures d'après Le fichier CSV interprété n'a pas pu être récupérée: " . "<br/>" .
                $ex->getMessage()
            );
            return false;
        }
        $this->setLastMessage("Liste des factures : " . '<br/>' . $result);
        return true;
    }
}
