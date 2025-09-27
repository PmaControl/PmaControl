<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Library\Graphviz;
use \Glial\Synapse\Controller;
use App\Library\Extraction;
use App\Library\Extraction2;
use \App\Library\Debug;
use App\Library\Country;

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;


use \Glial\Sgbd\Sgbd;

class Ollama extends Controller
{

    public function index($param)
    {

        $model = "llama3";

        // Le prompt que tu veux envoyer
        $prompt = "Quelle est la capitale de la France ?";

        // Préparation des données à envoyer
        $data = [
            "model" => $model,
            "prompt" => $prompt,
            "stream" => false // si true, la réponse arrive en flux (chunked)
        ];

        // Initialisation de la requête cURL
        $ch = curl_init("http://localhost:11434/api/generate");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Headers JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        // Exécution de la requête
        $response = curl_exec($ch);

        if ($response === false) {
            echo "Erreur cURL: " . curl_error($ch);
        } else {
            // Affichage de la réponse
            $result = json_decode($response, true);
            echo "Réponse : " . ($result['response'] ?? "Aucune réponse");
        }

        // Fermeture
        curl_close($ch);
    }


}