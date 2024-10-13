<?php

namespace App\Controller;

use \Glial\Synapse\Controller;
use \Glial\Acl\Acl as Droit;

class Telegram extends Controller {

    public function index() {


	// Remplace par ton token de bot et l'identifiant du canal
	$token = "";
	$channel_id = "@nom_du_canal";  // Par exemple : "@monCanal"
	$message = "Ceci est un message envoyé sur Telegram via PHP!";

	// URL pour envoyer le message via l'API Telegram
	$url = "https://api.telegram.org/bot$token/sendMessage";
	
	// Créer les données à envoyer
	$data = [
	    'chat_id' => $channel_id,
	    'text' => $message,
	];

	// Utiliser cURL pour envoyer la requête POST
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	// Exécuter la requête et récupérer la réponse
	$response = curl_exec($ch);

	// Fermer la session cURL
	curl_close($ch);

	// Afficher la réponse de Telegram (facultatif)
	echo $response;


    }


}
