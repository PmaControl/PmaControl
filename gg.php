<?php
function removeAnsiCodes(string $text): string {
    // Supprime toutes les séquences ANSI d'échappement
    return preg_replace('/\x1B\[[0-9;]*[A-Za-z]/', '', $text);
}

// Exemple :
$log = "\033[32mOK\033[0m - Everything is fine";
echo removeAnsiCodes($log); // Affichera : OK - Everything is fine
?>
