# MDEV-39044 - Findings utiles pour ce cas PmaControl

Source principale:

- https://jira.mariadb.org/browse/MDEV-39044

## Titre du ticket

`MDEV-39044`: `MyRocks corruption after restart during/after ALTER workload: Corruption: truncated record body, .frm mismatch, no crash log, no OOM killer`

Le titre a pu etre extrait directement depuis la page publique HTML, meme si Jira demande une connexion pour une lecture complete.

## Ce que le ticket dit de pertinent pour notre cas

Points visibles sur la page et ses extraits HTML:

- le probleme cible MyRocks / RocksDB
- symptome principal:
  - `RocksDB: Error opening instance, Status Code: 2, Status: Corruption: truncated record body`
- le serveur peut ensuite:
  - faire une `InnoDB crash recovery`
  - faire une recovery binlog
  - continuer a monter partiellement
- mais des erreurs metadata suivent:
  - `Incorrect information in file: './pmacontrol/...frm'`
- le ticket insiste sur un point important:
  - pas de crash net dans l'error log
  - pas d'evenement OOM killer evident
- contexte de reproduction decrit dans le ticket:
  - charge d'ecriture tres forte
  - tables RocksDB partitionnees tres volumineuses
  - `ALTER TABLE` sur des tables RocksDB
  - pression memoire InnoDB simultanee
  - un workflow applicatif parasitaire cote InnoDB

## Correlation avec notre serveur

Le cas du `2026-03-06` sur ce serveur colle tres fortement au ticket:

- on observe exactement:
  - `RocksDB: Error opening instance ... Corruption: truncated record body`
  - puis `Incorrect information in file: './pmacontrol/ts_value_general_int.frm'`
  - puis d'autres `.frm mismatch` sur les tables `ts_value_*`
- l'application `pmacontrol` manipule de tres grosses tables RocksDB partitionnees:
  - `ts_value_general_int`
  - `ts_value_general_json`
  - `ts_mysql_digest_stat`
  - `ts_value_general_text`
  - `ts_value_slave_text`
  - `ts_value_slave_int`
- le ticket mentionne explicitement un contexte de:
  - `ALTER TABLE`
  - ajout de partitions
  - forte pression d'ecriture
  - pression memoire cote InnoDB

Ce sont exactement des ingredients que l'on retrouve dans ce projet et dans les symptomes observes.

## Ce que le ticket apporte pour ameliorer le diagnostic

### 1. Le pattern "pas de crash explicite" est normal dans ce scenario

Le ticket indique que l'absence de crash net dans l'error log ou dans le kernel log n'exclut pas un incident severe.

Impact pratique:

- ne pas classer a tort `2026-01-29`, `2026-02-05`, `2026-02-23`, `2026-03-03` comme simples restarts faute de stack trace
- la presence de `crash recovery` + restart + reset brutal d'`uptime` suffit deja a faire monter le niveau de suspicion

### 2. Le pattern `.frm mismatch` est un indicateur cle

Dans notre cas, les erreurs suivantes sont centrales:

- `Incorrect information in file: './pmacontrol/ts_value_general_int.frm'`
- memes erreurs sur d'autres tables `ts_value_*`

Le ticket confirme que ce pattern est coherent avec un desalignement entre metadata MyRocks et fichiers `.frm`.

### 3. Les DDL sur tables RocksDB geantes doivent etre consideres comme facteur de risque

Le ticket oriente clairement vers:

- `ALTER TABLE`
- ajout de partitions
- charge d'ecriture continue

Dans PmaControl, les tables de metriques sont geantes et partitionnees par jour. C'est donc un candidat direct a la reproduction du probleme.

### 4. La pression memoire InnoDB est un facteur aggravant, pas forcement la cause unique

Le ticket fait le lien avec:

- `InnoDB: Memory pressure event disregarded`

Sur notre serveur, ce motif est visible:

- avant le crash du `2026-03-03`
- avant et apres l'incident du `2026-03-12`

Conclusion:

- la pression memoire n'explique pas a elle seule la corruption MyRocks
- mais elle peut favoriser le contexte qui precipite le restart anormal ou le comportement non atomique

### 5. Le ticket cite l'atomicite des DDL comme axe de correction

Un commentaire de Marko Makkela visible sur le ticket rappelle:

- les DDL sont censes etre "mostly atomic"
- cela devrait inclure les cas RocksDB, meme si le niveau de couverture/retest est incertain

Impact pratique:

- si le probleme touche l'ajout de partitions RocksDB sous charge, il faut traiter cela comme bug moteur / integration DDL, pas uniquement comme "mauvais usage"

## Lecture appliquee a nos incidents

### Incident du 2026-03-06

C'est le match le plus fort avec `MDEV-39044`:

- meme message `truncated record body`
- memes erreurs `.frm`
- restarts multiples
- abort sur `Can't init tc log`
- tables `pmacontrol.ts_value_*` impactees

### Incident du 2026-03-12

Le cas n'est pas identique:

- ici on a un `SIGKILL` explicite par systemd
- mais le restart suivant part sur une `crash recovery`

Lien avec `MDEV-39044`:

- faible a moyen pour la cause immediate du kill
- fort pour le contexte general de fragilite: grosses tables RocksDB, pression memoire, recovery apres arret brutal

### Incidents precedents du 2026-01-29, 2026-02-05, 2026-02-23, 2026-03-03

Le ticket aide a reclasser ces evenements comme:

- potentiellement des crashes/restarts anormaux sans signature frontale
- pas simplement des redemarrages applicatifs invisibles

## Actions concretes qui decoulent du ticket

1. Geler les `ALTER TABLE` / ajouts de partitions en plein trafic sur les grosses tables RocksDB.
2. Verifier tous les scripts d'automatisation qui manipulent les partitions `ts_value_*`.
3. Produire une chronologie precise des DDL autour du `2026-03-06`.
4. Isoler les workflows qui combinent:
   - forte ingestion
   - DDL RocksDB
   - forte pression memoire InnoDB
5. Capturer a l'avenir:
   - `journalctl -u mariadb -f`
   - `dmesg -w`
   - trace des jobs de maintenance / partitionnement
6. Ouvrir un rapprochement explicite entre l'incident local et `MDEV-39044` avec:
   - les timestamps du `2026-03-06`
   - les logs `.frm mismatch`
   - la topologie des grosses tables RocksDB

## Limites

- la page Jira complete n'est pas accessible anonymement: une connexion est demandee
- j'ai pu extraire:
  - le titre exact
  - plusieurs extraits visibles dans le HTML
  - le contexte general et les commentaires exposes dans la page retournee

Inference:

- le rapprochement avec notre cas est fort, mais il reste une inference basee sur les symptomes exposes publiquement et sur nos logs locaux
