#!/bin/bash

while true; do
    # Votre commande ici
    echo "Exécution à $(date)"
    pmacontrol recover importData --debug    
    echo "end"
	# Attendre 1 minute
    sleep 60
done
