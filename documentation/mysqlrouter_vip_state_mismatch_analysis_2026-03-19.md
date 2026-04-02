# Analyse `MysqlRouter` / `VIP` / `Server/state`

Date: 2026-03-19  
Sujet: incohérence entre `architecture/index` et `server/state` pour:

- `server_id=179` => `prodCluster-router-1-rw-split` => `10.68.68.134:6450`
- `server_id=185` => `prodCluster-router-vip-rw-split` => `10.68.68.130:6450`

## Résumé exécutif

Le problème ne vient pas du rendu `Server/state`.

La cause la plus probable est dans [Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php): pour les serveurs marqués `is_proxy=1`, `tryMysqlConnection()` ne se contente pas d'un test de connexion MySQL. Après connexion réussie, il exécute aussi:

- `SELECT 1`
- `BEGIN;`
- `COMMIT;`

Sur `server_id=179`, la connexion initiale réussit, mais le test proxy échoue ensuite sur `BEGIN;`, ce qui force `mysql_available` à `0`.

Sur `server_id=185`, qui est un `VIP`, ce test proxy supplémentaire n'est jamais exécuté. Le worker s'arrête après le test de connexion et la persistance de la route VIP. Résultat: `185` reste vert alors que `179` flappe ou reste rouge.

Donc:

- `185` n'est pas évalué avec la même sévérité que `179`
- `179` est pénalisé par un test applicatif supplémentaire
- l'écart observé est réel et explicable par le code actuel

## Inventaire réel

Source: table `mysql_server`

```text
179  server_69b0b6551bfc8  prodCluster-router-1-rw-split   10.68.68.134 6450  is_proxy=1 is_vip=0
180  server_69b0b6551fd5a  prodCluster-router-2-rw-split   10.68.68.135 6450  is_proxy=1 is_vip=0
185  server_69b0b655331ef  prodCluster-router-vip-rw-split 10.68.68.130 6450  is_proxy=0 is_vip=1
```

Source: table `vip_server`

```text
id_mysql_server=185
ip=10.68.68.130
id_mysql_server__actual=179
id_mysql_server__previous=180
updated_at=2026-03-19 16:44:20
```

Source: table `mysqlrouter_server`

```text
id=1 display_name=prodCluster-router-1-admin hostname=10.68.68.134 port=8443
id=2 display_name=prodCluster-router-2-admin hostname=10.68.68.135 port=8443
```

Source: table `mysqlrouter_server__mysql_server`

```text
id_mysqlrouter_server=1 -> id_mysql_server=179
id_mysqlrouter_server=2 -> id_mysql_server=180
```

## Quel worker alimente quoi

### Worker MySQL

Source: `worker_queue`

```text
name=worker_mysql
id_daemon_main=20
worker_method=tryMysqlConnection
timeout=10
nb_worker=20
```

Ce worker parcourt tous les `mysql_server` monitorés, donc:

- `179`
- `180`
- `185`

passent tous par `tryMysqlConnection()`.

### Worker MySQL Router

Source: `worker_queue`

```text
name=worker_mysqlrouter
id_daemon_main=36
worker_method=tryMysqlRouterConnection
timeout=3
nb_worker=10
```

Ce worker teste l'admin MySQL Router sur:

- `10.68.68.134:8443`
- `10.68.68.135:8443`

Important: ce worker alimente `mysqlrouter_available`, pas `mysql_available`.

Donc `server/state` ne montre pas directement le résultat de `tryMysqlRouterConnection()`.  
`server/state` affiche `mysql_available`, donc la donnée vue à l'écran vient du worker `tryMysqlConnection()`.

## Chaîne de décision dans `Aspirateur.php`

### Cas `179` et `180`

`179` et `180` sont `is_proxy=1`.

Dans [Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php), `tryMysqlConnection()` fait:

1. ouverture d'une vraie connexion MySQL sur l'endpoint
2. premier `setService(..., 'mysql')`
3. si `is_proxy=1`, test complémentaire:
   - `SELECT 1`
   - `BEGIN;`
   - `COMMIT;`
4. second `setService(..., 'mysql')`

Donc un proxy peut:

- d'abord être marqué `1`
- puis être immédiatement réécrit à `0`

### Cas `185`

`185` est `is_vip=1` et `is_proxy=0`.

Le même `tryMysqlConnection()`:

1. ouvre la connexion
2. écrit `mysql_available=1`
3. saute le bloc `is_proxy`
4. sort ensuite dans le bloc VIP après persistance de route

Donc `185` n'exécute jamais `BEGIN; COMMIT;`.

## Test direct du worker

### `server_id=179`

Commande exécutée:

```bash
php glial aspirateur tryMysqlConnection server_69b0b6551bfc8 179 10 --debug
```

Résultat observé:

```text
REPONSE MYSQL: available=1
...
Erreur : You have an error in your SQL syntax; after BEGIN only [WORK] is expected. Unexpected input near ;
...
mysql_available => 0
```

Conclusion:

- `10.68.68.134:6450` accepte la connexion
- puis le test proxy échoue sur `BEGIN;`
- le worker réécrit `mysql_available=0`

### `server_id=180`

Commande exécutée:

```bash
php glial aspirateur tryMysqlConnection server_69b0b6551fd5a 180 10 --debug
```

Résultat observé:

```text
REPONSE MYSQL: available=1
...
mysql_available => 1
```

Conclusion:

- le même chemin de code passe sur `180`
- `180` ne déclenche pas l'erreur `BEGIN;`
- `180` reste vert

### `server_id=185`

Commande exécutée:

```bash
php glial aspirateur tryMysqlConnection server_69b0b655331ef 185 10 --debug
```

Résultat observé:

```text
REPONSE MYSQL: available=1
...
vip_server updated
```

Conclusion:

- la connexion VIP est bonne
- le bloc `is_proxy` n'est pas exécuté
- `185` reste vert

## Historique réel de `mysql_available`

Dernière heure:

```text
179: total=725 ok=319 ko=406
180: total=794 ok=785 ko=9
185: total=719 ok=719 ko=0
```

Sur les dernières minutes observées:

- `179` est majoritairement à `0`
- `180` est majoritairement à `1`
- `185` est à `1` de façon continue

Ce comportement est cohérent avec les tests worker ci-dessus.

## Test TCP brut

Tests répétés 30 fois:

```text
10.68.68.134:6450 => ok=30 ko=0
10.68.68.130:6450 => ok=30 ko=0
10.68.68.134:8443 => ok=30 ko=0
```

Conclusion:

- le réseau n'explique pas l'état rouge de `179`
- le port écoute bien
- le problème est applicatif / SQL probe, pas TCP

## Interprétation fonctionnelle

Le système compare actuellement deux choses différentes:

- `185` = "le VIP accepte une connexion MySQL"
- `179` = "le listener direct accepte une connexion MySQL et passe un test transactionnel supplémentaire"

Donc le vert de `185` et le rouge de `179` ne sont pas contradictoires avec le code actuel.

En pratique, le vrai problème est que:

- le test utilisé pour les proxies n'est pas homogène avec le test VIP
- le test `BEGIN; COMMIT;` n'est pas robuste pour un MySQL Router frontend

## Hypothèse la plus probable sur l'erreur `BEGIN;`

L'erreur exacte:

```text
You have an error in your SQL syntax; after BEGIN only [WORK] is expected. Unexpected input near ;
```

montre que le `BEGIN;` tel qu'envoyé par le worker n'est pas accepté sur le chemin `179`.

Le point important n'est pas de savoir si l'erreur vient:

- du routeur lui-même
- d'un backend routé derrière `179`
- d'une différence de mode ou de version entre les deux routers

Le point important est que le probe choisi n'est pas suffisamment universel pour être utilisé comme vérité de disponibilité proxy.

## Pourquoi `vip_server.id_mysql_server__actual=179` peut sembler incohérent

`vip_server` indique actuellement:

- actual = `179`
- previous = `180`

Mais dans les mesures réelles:

- `179` est en échec applicatif
- `180` est sain
- `185` reste sain

Donc au moins une de ces propositions est vraie:

1. la route VIP est stockée avec retard
2. la résolution VIP ne décrit pas exactement le router réellement servi au moment de la mesure
3. le VIP est joignable alors que le listener direct `179` échoue seulement sur le test transactionnel supplémentaire

La 3e explication est suffisante pour expliquer l'écran actuel sans supposer de bug réseau.

## Conclusion

Le problème principal est dans la logique de probe de [Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php), pas dans `server/state`.

### Cause immédiate

Sur `179`, `tryMysqlConnection()`:

- réussit la connexion
- puis échoue sur `BEGIN;`
- et force `mysql_available=0`

Sur `185`, le worker:

- réussit la connexion
- ne fait pas le test proxy complémentaire
- garde `mysql_available=1`

### Cause structurelle

Le projet utilise aujourd'hui deux définitions différentes de "disponible":

- disponibilité de connexion pour un VIP
- disponibilité avec mini-test transactionnel pour un proxy

## Recommandations

### Correction recommandée

Pour `is_proxy=1`, remplacer le test:

```sql
SELECT 1;
BEGIN;
COMMIT;
```

par un test plus robuste et homogène, par exemple:

- `SELECT 1`

seulement.

C'est le meilleur compromis si le but est "endpoint joignable et capable de répondre à une requête SQL simple".

### Option plus stricte mais sûre

Si un test transactionnel est absolument voulu, il faut:

- le rendre spécifique au type de proxy
- ou l'exécuter seulement quand le moteur/proxy cible le supporte de façon fiable

Sinon `mysql_available` ne signifie plus "disponible", mais "disponible + compatible avec ce probe particulier".

### Alignement VIP / Router

Décider explicitement ce que doit signifier `mysql_available` pour:

- un VIP
- un MySQL Router frontend

et appliquer la même définition aux deux si on veut comparer visuellement leurs états.

## Proposition de patch minimal

Dans `tryMysqlConnection()`, bloc `is_proxy`:

- garder `SELECT 1`
- supprimer `BEGIN;` / `COMMIT;`

Effet attendu:

- `179` devrait passer vert tant que le listener répond réellement
- `server/state` deviendra cohérent avec `185`
- on élimine un faux négatif spécifique au probe

## Fichiers concernés

- [Aspirateur.php](/srv/www/pmacontrol/App/Controller/Aspirateur.php)
- [MysqlRouter.php](/srv/www/pmacontrol/App/Controller/MysqlRouter.php)
- [state.view.php](/srv/www/pmacontrol/App/view/Server/state.view.php)
- [state.js](/srv/www/pmacontrol/App/Webroot/js/Server/state.js)
