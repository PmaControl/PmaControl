# Installer / mettre à jour mydumper-myloader pour le module `Slave/reloadFromMaster`

> Surface DBA — procédure copier-coller pour préparer un master + ses
> replicas à utiliser la procédure **mydumper / myloader (parallel)** de
> [#1280](https://git.istosia.com/pmacontrol/pmacontrol/issues/1280)
> sur `/Slave/reloadFromMaster/<id>/`.

## Pourquoi cette doc

Le module reload offre trois procédures :

| Procédure | Binaires requis | Avantage | Limite |
|---|---|---|---|
| physical | `mariadb-backup` + `mbstream` sur les **deux** côtés | hot, le plus rapide | versions majeures identiques obligatoires |
| logical | `mariadb-dump` (déjà dans le paquet `mariadb-client`) | toujours compatible | mono-thread, le plus lent |
| **mydumper / myloader** | `mydumper` côté master + `myloader` côté slave | **5–10× plus rapide que logical** sur multi-DB, cross-version | `--stream` nécessite mydumper ≥ 0.12 *et* myloader ≥ 0.12 ; sans ça → fallback dir-based qui demande ~taille du dump en disque libre sur `/tmp` du slave |

Les versions packagées Debian/Ubuntu stables sont régulièrement en retard
(Debian 12 = mydumper 0.10.x, qui ne sait pas streamer). Le module
détecte automatiquement la présence des binaires (step `probe_binaries`)
mais pas la version — d'où cette doc.

## TL;DR — version recommandée

Installer **mydumper ≥ 0.16** sur master ET slave pour bénéficier de
`--stream` côté myloader (élimine l'étape tar intermédiaire qui exige
de la place libre sur `/tmp` du slave égale à la taille du dump).

## Installation propre — Debian 12 / Ubuntu 22.04 / 24.04

### 1) Détecter ce qui est déjà installé

```bash
which mydumper myloader
mydumper --version
myloader --version
```

Cible attendue : `mydumper v0.16+` sur **les deux** hôtes. Si une seule
des deux versions est < 0.12, le module bascule en mode dir-based
(landing dir sur `/tmp` du slave).

### 2) Désinstaller la version paquet si trop vieille

```bash
# Debian / Ubuntu — désinstalle 0.10.x du dépôt apt
sudo apt remove -y mydumper

# Vérifie qu'il ne reste plus de binaire
which mydumper myloader || echo "ok, plus aucun binaire"
```

### 3) Installer la release officielle (recommandé)

Les binaires statiques sont publiés ici :
<https://github.com/mydumper/mydumper/releases>

```bash
# Variables à adapter
VERSION=0.16.7-3
DISTRO=bookworm   # ou jammy, noble, etc.
ARCH=amd64

cd /tmp
wget "https://github.com/mydumper/mydumper/releases/download/v${VERSION}/mydumper_${VERSION}.${DISTRO}_${ARCH}.deb"
sudo dpkg -i mydumper_${VERSION}.${DISTRO}_${ARCH}.deb
sudo apt -f install -y    # tire les éventuelles dépendances manquantes

# Vérification
mydumper --version
myloader --version
```

### 4) Compilation depuis les sources (si pas de paquet pour ta distro)

```bash
sudo apt install -y cmake build-essential libglib2.0-dev \
  zlib1g-dev libpcre3-dev libssl-dev libmariadb-dev

cd /tmp
git clone --depth=1 --branch=v0.16.7-3 https://github.com/mydumper/mydumper.git
cd mydumper
cmake .
make -j"$(nproc)"
sudo make install
```

Cible installée : `/usr/local/bin/mydumper` et `/usr/local/bin/myloader`.
**Important** : `/usr/local/bin/` doit précéder `/usr/bin/` dans le
`PATH` du compte qui exécute le reload, sinon la version paquet (0.10)
reste prioritaire et le `--stream` échoue silencieusement.

## Vérification que le slave PEUT consommer un stream

```bash
myloader --help 2>&1 | grep -- --stream
```

Sortie attendue (mydumper ≥ 0.12) :
```
  --stream        Use STREAM. Read from the standard input
```

Si la ligne n'apparaît PAS, le slave est sur une vieille version → le
module bascule automatiquement en mode dir-based + tar et exige de la
place libre sur `/tmp` du slave **≥ taille décompressée du dump du
master**.

## Espace disque requis côté slave (mode dir-based)

Estimation grossière :

```bash
# Côté master, dans la session SQL `pmacontrol@%`
SELECT
    table_schema AS db,
    ROUND(SUM(data_length + index_length) / 1024 / 1024 / 1024, 1) AS gb
FROM information_schema.tables
WHERE table_schema NOT IN ('mysql', 'sys', 'information_schema', 'performance_schema')
GROUP BY table_schema
ORDER BY gb DESC;
```

Le dump non compressé tar pèse à peu près 50–80 % de la taille
`data_length + index_length` (compactage SQL vs format InnoDB on-disk).
**Le slave doit pouvoir héberger ce volume sous `/tmp`** pendant la
durée du chargement.

## Test rapide

Après installation, depuis l'hôte PmaControl :

```bash
# Vérifie que les deux binaires sont accessibles via le tunnel local
mysql pmacontrol -e "SELECT id, display_name, ssh_port FROM mysql_server WHERE id IN (<MASTER_ID>, <SLAVE_ID>)"

# Probe binaire master
ssh -p <MASTER_SSH_PORT> root@127.0.0.1 'mydumper --version'

# Probe binaire slave
ssh -p <SLAVE_SSH_PORT> root@127.0.0.1 'myloader --version'

# Probe support --stream
ssh -p <SLAVE_SSH_PORT> root@127.0.0.1 'myloader --help 2>&1 | grep -q -- --stream && echo "STREAM OK" || echo "FALLBACK DIR-BASED"'
```

## Drapeaux mydumper choisis par le module

Le worker `Slave::reloadCliRunMydumper` exécute (côté master) :

```
mydumper
    --threads=4
    --rows=10000000
    --regex='^(?!(mysql|sys|information_schema|performance_schema)\.)'  # exclut les schémas système
    --use-savepoints                                                     # locking minimal, InnoDB SAVEPOINTs
    --host=127.0.0.1 --protocol=tcp
    --user=<master.login>
    --password=<master.passwd décrypté>
    -o /tmp/mydumper-<jobId>
```

et côté slave :

```
myloader
    -d /tmp/myloader-<jobId>
    --threads=4
    --host=127.0.0.1 --protocol=tcp
    --user=root
    --enable-binlog
    --overwrite-tables
```

Les schémas système (`mysql.*`, `sys.*`, `*_schema.*`) sont
**volontairement exclus** : le slave conserve son `mysql.user` local,
ses grants, ses `slave_*_info`, etc. Seuls les schémas applicatifs sont
remplacés.

## Liens

- Doc officielle mydumper : <https://github.com/mydumper/mydumper>
- Compatibility matrix mydumper ↔ MariaDB :
  <https://github.com/mydumper/mydumper#mariadb-support>
- `/Slave/reloadFromMaster/<id>/` — page UI qui orchestre la procédure
- `App/Controller/Slave.php::reloadCliRunMydumper()` — worker côté
  PmaControl (forké via `nohup` après le POST CSRF-protégé)
