# data_geoip / data_geoip_city

Tables de lookup GeoIP basees sur des plages reseau (range IP → pays/ville), importees depuis les fichiers MaxMind GeoLite2 (`.mmdb`). L'objectif est d'eviter d'ouvrir le `.mmdb` a chaque rendu de page et de permettre des jointures SQL directes.

Au lieu de stocker une ligne par serveur (cache par IP), on stocke **l'integralite des plages IPv4 de GeoLite2**. Une IP est resolue par une requete range :

```sql
SELECT country_iso FROM data_geoip
WHERE network_start <= INET6_ATON('89.30.104.134')
  AND network_end   >= INET6_ATON('89.30.104.134')
ORDER BY network_start DESC
LIMIT 1;
```

## Structure

### data_geoip (GeoLite2-Country)

```sql
CREATE TABLE `data_geoip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `network_start` varbinary(16) NOT NULL,
  `network_end`   varbinary(16) NOT NULL,
  `country_iso`   char(2) NOT NULL DEFAULT '',
  `country_name`  varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX `idx_network_start` (`network_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### data_geoip_city (GeoLite2-City)

Ajoute les colonnes `region_iso`, `region_name`, `city`, `postal`, `latitude`, `longitude`, `time_zone`. Meme schema de plages que `data_geoip`.

Les IPs sont stockees en `varbinary(16)` via `INET6_ATON()` pour supporter IPv4 et IPv6 uniformement (bien que le loader actuel n'importe que l'IPv4).

## Peupler / Rafraichir

```bash
# GeoLite2-Country → data_geoip   (~650k ranges, quelques secondes)
php App/Webroot/index.php server loadGeoip

# GeoLite2-City → data_geoip_city (~3.7M ranges, plus long)
php App/Webroot/index.php server loadGeoipCity
```

Chaque commande :

1. Ouvre `data/GeoLite2-Country.mmdb` (ou `GeoLite2-City.mmdb`) via `MaxMind\Db\Reader`
2. `TRUNCATE` la table cible
3. Parcourt l'espace IPv4 (0 → 4294967295) en utilisant `getWithPrefixLen()` pour extraire chaque bloc CIDR
4. Inserte par batch (1000 pour country, 500 pour city) avec `network_start`/`network_end` encodees en `UNHEX(bin2hex(inet_pton(...)))`
5. Saute les plages privees/non attribuees (pas d'`iso_code` dans le record MaxMind)

Les IPs privees (10.x, 172.16-31.x, 192.168.x, 127.x) ne ressortent pas : elles n'ont pas de record GeoLite2, donc la jointure renvoie NULL.

## Requetes utiles

```sql
-- Pays d'une IP
SELECT country_iso, country_name FROM data_geoip
WHERE network_start <= INET6_ATON('89.30.104.134')
  AND network_end   >= INET6_ATON('89.30.104.134')
ORDER BY network_start DESC
LIMIT 1;

-- Ville d'une IP
SELECT country_iso, region_name, city, latitude, longitude
FROM data_geoip_city
WHERE network_start <= INET6_ATON('89.30.104.134')
  AND network_end   >= INET6_ATON('89.30.104.134')
LIMIT 1;

-- Tous les serveurs avec leur pays (jointure range)
SELECT s.id, s.display_name, s.ip, s.port, g.country_iso, g.country_name
FROM mysql_server s
LEFT JOIN data_geoip g
  ON g.network_start <= INET6_ATON(s.ip)
 AND g.network_end   >= INET6_ATON(s.ip)
WHERE s.is_deleted = 0
ORDER BY g.country_iso, s.display_name;
```

La jointure range etant couteuse sur 650k lignes, `Server::main()` resout chaque IP distincte individuellement avec une requete `LIMIT 1` en boucle plutot qu'un `JOIN` global.

## Utilisation dans la vue

Le controller `Server::main()` construit `$data['geoip']` comme un mapping `ip => country_iso` :

```php
$data['geoip'] = [];
foreach (array_keys($uniqueIps) as $ip) {
    $sqlGeo = "SELECT country_iso FROM data_geoip
               WHERE network_start <= INET6_ATON('$ip')
               AND   network_end   >= INET6_ATON('$ip')
               ORDER BY network_start DESC
               LIMIT 1";
    // ... fetch et stockage
}
```

La vue genere le flag emoji via `isoToFlag()` (voir `App/view/Server/main.view.php:20`) :

```php
if (!empty($data['geoip'][$server['ip']])) {
    $flag = isoToFlag($data['geoip'][$server['ip']]);
}
```

## Quand relancer loadGeoip

- Apres mise a jour du fichier `data/GeoLite2-Country.mmdb` (les fichiers MaxMind sont mis a jour chaque semaine)
- `loadGeoipCity` uniquement si `data_geoip_city` est utilise (plus gros, plus long a charger)
- Pas besoin de relancer quand on ajoute un serveur : la table couvre deja tout l'IPv4

Cron hebdomadaire typique :

```
0 3 * * 1 php /srv/www/pmacontrol/App/Webroot/index.php server loadGeoip
```

## Fichiers lies

| Fichier | Role |
|---------|------|
| `sql/incremental_v2/data_geoip.sql`      | Schema table country |
| `sql/incremental_v2/data_geoip_city.sql` | Schema table city |
| `App/Controller/Server.php`              | Actions `loadGeoip()`, `loadGeoipCity()`, lookup dans `main()` |
| `App/view/Server/main.view.php`          | `isoToFlag()` + affichage du flag pays |
| `data/GeoLite2-Country.mmdb`             | Base MaxMind country (source) |
| `data/GeoLite2-City.mmdb`                | Base MaxMind city (source) |
