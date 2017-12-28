<?php

class GlaneurLocalFilenameMatcher {

    public function getFilenameMatching(string $file_preg_match, array $cardinalite_element, array $files_list) {
        $result = array();
        $preg_match_list = $this->getArrayFromFilePregMatch($file_preg_match);
        $matches = array();
        foreach($preg_match_list as $key => $regexp){

            foreach($files_list as $i => $filename){
                $regexp = preg_replace_callback(
                    '#\$matches\[(\d+)\]\[(\d+)\]#',
                    function ($m) use ($matches){
                        if (empty($matches[$m[1]][$m[2]])){
                            return false;
                        }
                        return $matches[$m[1]][$m[2]];
                    },
                    $regexp
                );

                $r = preg_match($regexp,$filename,$match);

                if ($r){
                    $result[$key][] = $filename;
                    unset($files_list[$i]);
                    $matches[$i] = $match;
                    if (isset($cardinalite_element[$key]) && $cardinalite_element[$key] == 1) {
                        continue 2;
                    }
                }
            }
        }
        return $result;
    }

    private function getArrayFromFilePregMatch($file_preg_match){
        $result = array();
        foreach(explode("\n",$file_preg_match) as $line){
            $l = explode(':',$line);
            if (count($l)<2){
                continue;
            }
            $result[trim($l[0])] = trim($l[1]);
        }
        return $result;
    }

}