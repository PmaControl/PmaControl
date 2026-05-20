<?php
// Remplace TON_TOKEN par le token de ton bot Telegram
$token = '7675935068:AAHXS6KvQE7kaDYURCcusaSLgD4taXuvr9s';
$url = "https://api.telegram.org/bot{$token}/getUpdates";

// Récupère les mises à jour
$response = file_get_contents($url);
if ($response === FALSE) {
    die("Erreur : Impossible de récupérer les mises à jour.");
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Erreur : Impossible de décoder la réponse JSON.");
}

// Affiche les IDs des groupes
if (isset($data['result']) && is_array($data['result'])) {
    foreach ($data['result'] as $update) {
        if (isset($update['message']['chat']['id']) &&
            isset($update['message']['chat']['type']) &&
            in_array($update['message']['chat']['type'], ['group', 'supergroup'])) {
            echo "ID du groupe : " . $update['message']['chat']['id'] . "\n";
        }
    }
} else {
    echo "Aucun groupe trouvé ou aucune mise à jour disponible.\n";
}
?>
