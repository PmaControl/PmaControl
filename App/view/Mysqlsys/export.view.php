<?php


if (!function_exists('generateDokuwikiTable')) {


    function generateDokuwikiTable($data) {
        // Récupération des colonnes (clés)
        $columns = array_keys(current($data));

        // Génération de l'en-tête du tableau
        $output = "^ ID ^ " . implode(" ^ ", array_map('ucfirst', $columns)) . " ^\n";

        // Génération des lignes du tableau
        foreach ($data as $id => $row) {
            $output .= "| $id ";
            foreach ($columns as $col) {
                $output .= "| ". "<nowiki>". $row[$col] . "</nowiki>";
            }
            $output .= "|\n";
        }

        return $output;
    }
}


echo "\n";
print_r(generateDokuwikiTable($data['export']));
echo "\n";