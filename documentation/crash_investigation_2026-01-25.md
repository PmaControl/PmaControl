# Investigation crash MariaDB / PmaControl

## Perimetre

- Serveur analyse: `id_mysql_server = 1`
- Periode: du `2026-01-25` au `2026-03-12`
- Serie de reference crash: `ts_variable.id = 2693` (`uptime`, `status/general`)
- Metriques 1 heure avant crash:
  - `threads_connected` (`2687`)
  - `threads_running` (`2689`)
  - `cpu_usage` (`4015`, `ssh_stats/general`)
  - `memory_mysqld` (`4017`, `ssh_stats/general`)
- Logs corrélés:
  - `/srv/mysql/log/error.log`
  - `/srv/mysql/log/error.log.old`
  - `journalctl`

Contraintes d'execution respectees:

- chaque requete SQL: `SET SESSION max_statement_time=60`
- chaque commande shell: `timeout 65s`
- analyse journaliere / partition par partition sur `ts_value_general_int`

## Methode

1. detection des resets d'`uptime` via `ts_value_general_int`
2. filtration des seuls evenements anormaux
3. correlation avec les logs MariaDB
4. correlation avec les logs systeme pour chercher:
   - `status=9/KILL`
   - `oom`
   - `Killed process`
   - `segfault`
   - `panic`
5. synthese des metriques sur l'heure precedente

Note:

- quand les metriques `cpu_usage` ou `memory_mysqld` disparaissent avant le crash, cela signifie en pratique que la collecte SSH n'a plus de donnees. Dans ce contexte, c'est souvent coherent avec un serveur deja down ou en etat degrade.

## Reboots retenus

Seuls les crashes, crashes probables ou incidents majeurs sont gardes.

| Date | Heure reset `uptime` | Classification | Signature principale |
|---|---:|---|---|
| 2026-01-29 | 16:20:02 | crash probable | `InnoDB crash recovery` + `Recovering after a crash using /srv/mysql/binlog/mariadb-bin` |
| 2026-02-05 | 20:20:02 | crash probable | crash recovery + `Error in Log_event::read_log_event(): 'Event invalid'` |
| 2026-02-23 | 14:30:02 | crash probable | `InnoDB crash recovery` + recovery binlog |
| 2026-03-03 | 14:15:02 | crash probable | `Too many connections` avant reset + crash recovery |
| 2026-03-06 | 08:48:01 et autres cycles | incident majeur MyRocks | `Corruption: truncated record body`, `.frm mismatch`, `Can't init tc log`, `Aborting` |
| 2026-03-12 | 17:32:52 | crash confirme | `systemd: status=9/KILL` + crash recovery |

## Ce que disent les logs systeme

### Trouve

- `2026-03-12 17:32:15`
  - `mariadb.service: Main process exited, code=killed, status=9/KILL`
  - restart automatique 5 secondes plus tard

### Non trouve

Pour les autres crashes retenus, je n'ai pas trouve dans `journalctl` de trace exploitable du type:

- `Out of memory`
- `Killed process`
- `oom-killer`
- `segfault`
- `kernel panic`

Inference:

- l'absence de signature kernel/systemd n'innocente pas ces incidents
- elle est meme coherente avec le pattern du ticket `MDEV-39044`, qui decrit explicitement un cas sans crash record clair ni OOM killer visible

## Detail par crash

### 2026-01-29 16:20:02

Classification: crash probable

Evidence logs:

- `Starting MariaDB 10.11.15...`
- `InnoDB: Starting crash recovery from checkpoint LSN=3035597468044`
- `Recovering after a crash using /srv/mysql/binlog/mariadb-bin`
- transactions XA preparees retrouvees et recoverees

Contexte avant crash:

- nombreuses `Aborted connection ... Got timeout reading communication packets`
- erreurs repetitives dans l'event scheduler:
  - `Unknown column 'File_size' in 'SELECT'`

Resume 1 heure precedente:

| Metrique | Min | Moyenne | Max | Premier | Dernier | Derniere mesure |
|---|---:|---:|---:|---:|---:|---|
| threads_connected | 19 | 33.83 | 49 | 36 | 27 | 2026-01-29 16:20:02 |
| threads_running | 2 | 2.39 | 5 | 3 | 3 | 2026-01-29 16:20:02 |
| memory_mysqld | 10150908 | 10244668.12 | 10342684 | 10207432 | 10315680 | 2026-01-29 16:18:41 |
| cpu_usage | 29.7308 | 29.7309 | 29.7310 | 29.7310 | 29.7310 | 2026-01-29 16:18:41 |

Lecture:

- pas de derive CPU visible
- `threads_connected` modere mais stable
- incident plutot lie a un crash/restart backend qu'a une saturation progressive simple

### 2026-02-05 20:20:02

Classification: crash probable

Evidence logs:

- `InnoDB: Starting crash recovery from checkpoint LSN=3127773059315`
- `Recovering after a crash using /srv/mysql/binlog/mariadb-bin`
- `Error in Log_event::read_log_event(): 'Event invalid', data_len: 0, event_type: 0`

Resume 1 heure precedente:

| Metrique | Min | Moyenne | Max | Premier | Dernier | Derniere mesure |
|---|---:|---:|---:|---:|---:|---|
| threads_connected | 17 | 17 | 17 | 17 | 17 | 2026-02-05 20:20:02 |
| threads_running | 6 | 6 | 6 | 6 | 6 | 2026-02-05 20:20:02 |
| memory_mysqld | n/a | n/a | n/a | n/a | n/a | aucune mesure |
| cpu_usage | n/a | n/a | n/a | n/a | n/a | aucune mesure |

Lecture:

- absence quasi totale de metriques systeme sur l'heure precedente
- cela renforce l'idee d'une indisponibilite ou d'un trou de collecte avant le restart
- le point le plus fort reste le binlog recovery avec `Event invalid`

### 2026-02-23 14:30:02

Classification: crash probable

Evidence logs:

- `InnoDB: Starting crash recovery from checkpoint LSN=3359727579223`
- `Recovering after a crash using /srv/mysql/binlog/mariadb-bin`
- `Crash table recovery finished`

Resume 1 heure precedente:

| Metrique | Min | Moyenne | Max | Premier | Dernier | Derniere mesure |
|---|---:|---:|---:|---:|---:|---|
| threads_connected | 15 | 15 | 15 | 15 | 15 | 2026-02-23 14:30:02 |
| threads_running | 5 | 5 | 5 | 5 | 5 | 2026-02-23 14:30:02 |
| memory_mysqld | n/a | n/a | n/a | n/a | n/a | aucune mesure |
| cpu_usage | n/a | n/a | n/a | n/a | n/a | aucune mesure |

Lecture:

- meme pattern que le `2026-02-05`: peu ou pas de metriques systeme disponibles dans la fenetre
- le crash recovery InnoDB/binlog suffit a classer l'evenement comme anormal

### 2026-03-03 14:15:02

Classification: crash probable

Evidence logs:

- juste avant:
  - nombreuses connexions `unauthenticated` depuis `10.68.68.103`
  - `Too many connections`
  - `InnoDB: Memory pressure event disregarded`
- apres restart:
  - `InnoDB: Starting crash recovery from checkpoint LSN=3473476291238`
  - transactions XA preparees retrouvees
  - recovery binlog

Resume 1 heure precedente:

| Metrique | Min | Moyenne | Max | Premier | Dernier | Derniere mesure |
|---|---:|---:|---:|---:|---:|---|
| threads_connected | 22 | 32.81 | 82 | 39 | 29 | 2026-03-03 14:15:02 |
| threads_running | 4 | 5.64 | 59 | 5 | 6 | 2026-03-03 14:15:02 |
| memory_mysqld | 10001148 | 10194736.17 | 10373696 | 10223980 | 10105840 | 2026-03-03 14:08:42 |
| cpu_usage | 24.8315 | 24.8433 | 24.8649 | 24.8315 | 24.8649 | 2026-03-03 14:08:42 |

Lecture:

- la saturation est visible cote threads:
  - pic `threads_connected = 82`
  - pic `threads_running = 59`
- le systeme ne montre pas une derive CPU equivalente: ce n'est pas juste "CPU a 100%"
- pattern tres coherent avec une saturation de connexions / backlog applicatif avant crash

### 2026-03-06

Classification: incident majeur MyRocks

Evidence logs:

- `RocksDB: Error opening instance, Status Code: 2, Status: Corruption: truncated record body`
- ensuite:
  - `Incorrect information in file: './pmacontrol/ts_value_general_int.frm'`
  - memes erreurs sur plusieurs `ts_value_*`
- a `09:15:24`:
  - `Heuristic crash recovery mode`
  - `Can't init tc log`
  - `Aborting`
- multiples cycles start/stop tout au long de la journee

Resume 1 heure precedente du premier reset retenu (`08:48:01`):

| Metrique | Min | Moyenne | Max | Premier | Dernier | Derniere mesure |
|---|---:|---:|---:|---:|---:|---|
| threads_connected | 12 | 25.01 | 40 | 26 | 12 | 2026-03-06 08:48:01 |
| threads_running | 4 | 4.07 | 7 | 5 | 4 | 2026-03-06 08:48:01 |
| memory_mysqld | n/a | n/a | n/a | n/a | n/a | aucune mesure |
| cpu_usage | n/a | n/a | n/a | n/a | n/a | aucune mesure |

Lecture:

- les compteurs de threads ne montrent pas une saturation massive juste avant
- l'absence de `cpu_usage` et `memory_mysqld` sur la fenetre renforce plutot l'idee d'un serveur deja degrade ou d'une collecte casse
- la signature structurante est clairement MyRocks / metadata corruption

### 2026-03-12 17:32:52

Classification: crash confirme

Evidence logs:

- `systemd`:
  - `Main process exited, code=killed, status=9/KILL`
  - restart automatique
- `error.log` apres restart:
  - `InnoDB: Starting crash recovery from checkpoint LSN=3694675237923`
  - recovery binlog
- apres restart:
  - `InnoDB: Memory pressure event shrunk innodb_buffer_pool_size=1536m`
  - puis `1280m`

Resume 1 heure precedente:

| Metrique | Min | Moyenne | Max | Premier | Dernier | Derniere mesure |
|---|---:|---:|---:|---:|---:|---|
| threads_connected | 16 | 22.16 | 37 | 23 | 34 | 2026-03-12 17:32:52 |
| threads_running | 4 | 4.89 | 15 | 4 | 15 | 2026-03-12 17:32:52 |
| memory_mysqld | 6084048 | 6413105.91 | 7963260 | 6314892 | 7781484 | 2026-03-12 17:32:13 |
| cpu_usage | 46.8672 | 80.6819 | 99.5037 | 75.3316 | 97.0223 | 2026-03-12 17:32:13 |

Lecture:

- c'est le cas le plus lisible cote pression systeme
- dans les 10 dernieres minutes avant le kill:
  - `cpu_usage` monte jusqu'a ~`99.5`
  - `memory_mysqld` grimpe jusqu'a ~`7.96M` sur cette metrique SSH
  - `threads_running` monte a `15`
  - `threads_connected` monte a `37`
- ce crash est distinct du pattern pur MyRocks:
  - ici l'arret brutal est explicite
  - mais il intervient dans un contexte de forte pression CPU + memoire

## Metriques supplementaires interessantes

En complement des 4 metriques demandees, les metriques MariaDB les plus interessantes pour le prochain niveau d'investigation sont:

- `max_used_connections`
  - utile pour distinguer charge instantanee et saturation historique
- `aborted_connects`
  - utile pour identifier problemes reseau/auth ou storms de connexion
- `aborted_clients`
  - utile pour reperer les clients qui coupent brutalement
- `slow_queries`
  - utile pour voir si la saturation est precedee d'une derive lente
- `open_tables`
  - utile pour detecter pression metadata / table cache
- `innodb_buffer_pool_pages_dirty`
  - utile pour evaluer pression d'ecriture / backlog flush
- `innodb_row_lock_waits`
  - utile pour voir si un contentieux transactionnel precede le crash

Sources officielles MariaDB:

- System & Status Variables Guide: https://mariadb.com/docs/server/mariadb-quickstart-guides/system-and-status-variables-guide
- Server Status Variables: https://mariadb.com/kb/en/server-status-variables/
- Handling Too Many Connections: https://mariadb.com/kb/en/handling-too-many-connections/

Inference:

- ces metriques sont pertinentes parce qu'elles permettent de distinguer:
  - crash moteur
  - saturation connexions
  - pression cache / metadata
  - backlog d'ecriture InnoDB
  - ralentissement applicatif avant crash

## Variables non par defaut

Comparaison effectuee entre:

- `pmacontrol.global_variable`
- `information_schema.SYSTEM_VARIABLES.DEFAULT_VALUE`

Resultats:

- `163` variables differentes du defaut au total
- `97` variables non par defaut avec origine `CONFIG` ou `AUTO`

La liste detaillee est dans:

- [global_variable_non_default_id1.md](/srv/www/pmacontrol/documentation/global_variable_non_default_id1.md)

## Conclusion

Les crashes les plus significatifs sont:

1. `2026-03-06`
   - incident majeur MyRocks
   - correspondance tres forte avec `MDEV-39044`
2. `2026-03-12`
   - crash confirme par `status=9/KILL`
   - fort emballement CPU / memoire juste avant
3. `2026-03-03`
   - crash probable precede d'un `Too many connections`
   - `threads_running` et `threads_connected` montrent une montee nette juste avant

Les crashes `2026-01-29`, `2026-02-05` et `2026-02-23` restent importants, mais les metriques systeme sont plus lacunaires; leur classification repose surtout sur les signatures de crash recovery dans les logs MariaDB.
