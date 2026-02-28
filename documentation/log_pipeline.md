# Log pipeline automatique (SSH -> Listener -> Chart.js)

## Objectif

Ce pipeline permet de :

1. Collecter les logs distants via SSH à chaque modification observée.
2. Dédupliquer et stocker les événements dans des tables `ts_*`.
3. Agréger les événements par heure pour les 24 dernières heures.
4. Visualiser le flux comme un graphe type Kafka via Chart.js.

## Schéma de stockage intelligent

- `ts_log_event` : stockage brut dédupliqué (`event_hash`) des événements.
- `ts_log_event_hourly` : agrégation compacte pour affichage dashboard.

## Exécution

1. Appliquer `sql/incremental_v2/log_pipeline.sql`.
2. Collecter des logs :
   - `/LogPipeline/collect/{id_mysql_server}/{log_path}/{max_lines}`
3. Visualiser le dashboard :
   - `/LogPipeline/index`
4. Endpoint JSON (24h) :
   - `/LogPipeline/api24h/{id_mysql_server}`

## Intégration listener

Le contrôleur `LogPipeline` est conçu pour être appelé depuis un listener/daemon existant (ex: job périodique), afin de déclencher la collecte automatiquement.
