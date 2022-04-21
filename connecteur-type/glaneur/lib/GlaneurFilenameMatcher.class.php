<?php

class GlaneurFilenameMatcher
{
    /**
     * @param string $file_preg_match
     * @param array $cardinalite_element
     * @param array $files_list
     * @return array
     * @throws Exception
     */
    public function getFilenameMatching(string $file_preg_match, array $cardinalite_element, array $files_list)
    {
        $result = [];
        $file_match = [];
        $preg_match_list = $this->getArrayFromFilePregMatch($file_preg_match);
        $matches = [];
        $num_regexp = 0;
        foreach ($preg_match_list as $key => $regexp) {
            foreach ($files_list as $i => $filename) {
                $regexp = preg_replace_callback(
                    '#\$matches\[(\d+)\]\[(\d+)\]#',
                    function ($m) use ($matches) {
                        if (empty($matches[$m[1]]) || empty($matches[$m[1]][$m[2]])) {
                            return false;
                        }
                        return $matches[$m[1]][$m[2]];
                    },
                    $regexp
                );

                $r = preg_match($regexp, $filename, $match);

                if ($r) {
                    $file_match[$key][] = $filename;
                    unset($files_list[$i]);
                    $matches[$num_regexp] = $match;
                    if (isset($cardinalite_element[$key]) && $cardinalite_element[$key] == 1) {
                        $num_regexp++;
                        continue 2;
                    }
                }
            }
            $num_regexp++;
        }
        if (empty($file_match)) {
            throw new Exception("Impossible d'associer les fichiers");
        }
        $result['file_match'] = $file_match;
        $result['matches'] = $matches;
        return $result;
    }

    /**
     * @param $file_preg_match
     * @return array
     * @throws Exception
     */
    private function getArrayFromFilePregMatch($file_preg_match)
    {
        $result = [];
        foreach (explode("\n", $file_preg_match) as $line) {
            $l = explode(':', $line);
            if (count($l) < 2) {
                continue;
            }
            $result[trim($l[0])] = trim($l[1]);
        }
        if (empty($result)) {
            throw new Exception("Impossible de trouver les expressions pour associer les fichiers");
        }
        return $result;
    }
}
