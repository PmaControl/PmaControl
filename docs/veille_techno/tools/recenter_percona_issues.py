#!/usr/bin/env python3
"""Recenter already-created Percona improvement issues.

The first triage pass deliberately grouped articles by broad rules. That made
many titles start with the same "cockpit XtraBackup" wording even when the
article-specific improvement was about locks, keyrings, ProxySQL backup,
upgrade, replication, or security. This script rewrites the Codex issue title
and body while preserving the existing Claude comments in each thread.
"""

from __future__ import annotations

import json
import re
import subprocess
from pathlib import Path
from typing import Any
from urllib.parse import urlparse

import requests


ROOT = Path(__file__).resolve().parents[3]
WATCH_ROOT = Path(__file__).resolve().parents[1]
ISSUES_PATH = WATCH_ROOT / "percona" / "_data" / "issues_created.jsonl"


FOCUS: dict[int, dict[str, str]] = {
    29309: {
        "title": "XtraBackup 8.4: mesurer les backup locks et les DDL bloquées",
        "proposal": "Ajouter au domaine Backup une mesure explicite du temps passé sous backup lock pendant XtraBackup, avec timeline des DDL bloquées, seuil d'alerte et comparaison avant/après changement de version.",
    },
    21014: {
        "title": "PXC 8: auditer la disparition de wsrep_sst_auth et les secrets SST",
        "proposal": "Ajouter un contrôle PXC qui détecte la dépendance à `wsrep_sst_auth`, vérifie les secrets SST, les privilèges minimaux et les impacts ProxySQL/backup avant upgrade.",
    },
    35546: {
        "title": "PXC Replication Manager: suivre failover source/replica et topologie",
        "proposal": "Ajouter une vue de suivi pour PXC Replication Manager: source active, replicas async, bascules, état Galera, erreurs récentes et cohérence avec les vues Replication/Galera existantes.",
    },
    34800: {
        "title": "MySQL 8.4 LTS: conseiller GTID, auth, FK et dépréciations",
        "proposal": "Créer un rapport de préparation MySQL 8.4 LTS qui croise GTID, authentification, clés étrangères, auto_increment, variables dépréciées et changements de comportement visibles dans le parc.",
    },
    29196: {
        "title": "MySQL 8.4/9.1: comparer gains de performance et risques binlog",
        "proposal": "Ajouter une comparaison avant/après upgrade qui suit débit, latence, transactions, binlog et réglages associés pour objectiver les gains ou régressions MySQL 8.4/9.1.",
    },
    27783: {
        "title": "XtraBackup Docker: valider volumes, privilèges et logs de backup",
        "proposal": "Ajouter une checklist d'exécution XtraBackup en conteneur: mounts requis, droits minimaux, version outil/serveur, collecte logs, statut prepare et preuve de restore.",
    },
    25472: {
        "title": "XtraBackup + Vault KMIP: contrôler chiffrement et restaurabilité",
        "proposal": "Ajouter des contrôles pour backups chiffrés avec Vault KMIP: plugin keyring actif, accès aux clés, compatibilité serveur/outil, logs de prepare et test restore.",
    },
    19053: {
        "title": "keyring_vault MySQL 5.7: sécuriser backup et restore",
        "proposal": "Ajouter un audit keyring_vault pour MySQL 5.7: plugin chargé, configuration cohérente, backup compatible, restore testable et erreurs typiques exposées dans l'UI.",
    },
    34936: {
        "title": "InnoDB Cluster 8.0 -> 8.4: assistant d'upgrade sécurisé",
        "proposal": "Créer un assistant d'upgrade InnoDB Cluster 8.0 vers 8.4: prérequis, backup, état cluster, incompatibilités, ordre des nœuds et validation post-upgrade.",
    },
    27899: {
        "title": "ProxySQL: sauvegarder et restaurer config, dump admin et snapshot",
        "proposal": "Recentrer le ticket sur ProxySQL: proposer un parcours de sauvegarde/restauration de la configuration, base admin, snapshots, cohérence runtime/disk et test de rollback.",
    },
    18976: {
        "title": "Aurora/RDS MySQL: advisor réplication, backup et limites managées",
        "proposal": "Ajouter un advisor Aurora/RDS qui signale les écarts avec MySQL self-managed: paramètres non modifiables, réplication, backup, limitations XtraBackup et impacts ProxySQL/monitoring.",
    },
    17594: {
        "title": "MariaDB vs MySQL: matrice de compatibilité ProxySQL/MaxScale",
        "proposal": "Ajouter une matrice opérationnelle MariaDB/MySQL: fonctionnalités supportées, différences de réplication, compatibilité ProxySQL/MaxScale, variables et risques d'upgrade.",
    },
    26470: {
        "title": "Upgrade MySQL 8: vérifier les impacts backup avant migration",
        "proposal": "Ajouter une étape obligatoire de revue backup avant upgrade MySQL 8: compatibilité XtraBackup, redo/undo, keyring, restore test et fenêtre de rollback.",
    },
    25821: {
        "title": "MySQL sur Kubernetes: inventorier contraintes backup et configuration",
        "proposal": "Ajouter une fiche d'inventaire Kubernetes pour MySQL: stockage persistant, secrets, services, backup/restore, ressources et différences avec serveurs classiques.",
    },
    21406: {
        "title": "Backups logiques distribués MySQL: orchestrer dump, chunks et restore",
        "proposal": "Créer une vue d'orchestration des backups logiques distribués: découpage, parallélisme, cohérence, suivi des chunks, erreurs et procédure de restore.",
    },
    20046: {
        "title": "Chiffrement disque MySQL: mesurer overhead et impacts backup",
        "proposal": "Ajouter un tableau d'impact du chiffrement disque: latence I/O, CPU, temps backup/restore, keyring utilisé et comparaison avec baseline non chiffrée.",
    },
    18919: {
        "title": "PXC + SELinux: auditer politiques, ports et chemins requis",
        "proposal": "Ajouter un contrôle SELinux pour PXC: état enforcing/permissive, contextes datadir/logs, ports, SST, erreurs AVC et recommandations avant incident.",
    },
    17990: {
        "title": "Percona Server: diagnostiquer améliorations binlog et réplication",
        "proposal": "Ajouter un diagnostic binlog/réplication qui détecte options Percona Server, compression, group commit, lag, erreurs et compatibilité avec replicas.",
    },
    8630: {
        "title": "Online schema change: sécuriser les changements de schéma lourds",
        "proposal": "Ajouter un assistant de changement de schéma: estimation taille, locks, FK/triggers, pt-online-schema-change, seuils de lag et génération d'un plan opérateur.",
    },
    7920: {
        "title": "Backup locks Percona Server: superviser contention et durée",
        "proposal": "Ajouter un suivi des backup locks Percona Server: durée, sessions bloquées, DDL concurrentes, corrélation backup/job et alerte en cas de contention.",
    },
    7265: {
        "title": "RDS MySQL 5.6: contrôler variables, SSL et réplication managée",
        "proposal": "Ajouter un profil RDS MySQL qui distingue variables accessibles/non accessibles, SSL, réplication, paramètres dynamiques et limites d'exploitation.",
    },
    44623: {
        "title": "XtraBackup incremental prepare: tracer durée et gains de performance",
        "proposal": "Ajouter des métriques sur la phase prepare incrémentale XtraBackup: durée, taille incrémentale, version outil, stockage temporaire et écart avec historique.",
    },
    43824: {
        "title": "Percona Operator MySQL: suivre PITR, incrémentaux et compression",
        "proposal": "Ajouter une vue opérateur MySQL/PXC Operator pour PITR, backups incrémentaux, compression, rétention et validation de restore.",
    },
    43843: {
        "title": "PXC cross-site replication: superviser réplication inter-sites",
        "proposal": "Ajouter une vue cross-site PXC: site primaire, site secondaire, état réplication, latence inter-site, failover attendu et risques de split-brain.",
    },
    35021: {
        "title": "ProxySQL/HAProxy/ReadySet: comparer cache de requêtes et routage",
        "proposal": "Ajouter une expérimentation guidée pour comparer ProxySQL, HAProxy et cache de requêtes: latence, hit ratio, erreurs, routage et impact applicatif.",
    },
    29163: {
        "title": "XtraBackup: valider ordre déchiffrement/décompression",
        "proposal": "Ajouter un validateur de procédure restore XtraBackup qui contrôle l'ordre decrypt/decompress/prepare selon les options utilisées et signale les commandes incorrectes.",
    },
    28623: {
        "title": "Monitoring MySQL: tableau de bonnes pratiques et couverture PMM",
        "proposal": "Ajouter une checklist de monitoring MySQL: disponibilité, connexions, I/O, replication lag, locks, slow queries, backup freshness et couverture PMM/PmaControl.",
    },
    28726: {
        "title": "Réplication MySQL: diagnostiquer erreur 1236 / MY-013114",
        "proposal": "Ajouter un diagnostic guidé pour erreurs 1236/MY-013114: binlog manquant, position invalide, purge, GTID, source cible et actions de resynchronisation.",
    },
    27124: {
        "title": "KPI MySQL avec PMM: scorecards et seuils opérateur",
        "proposal": "Ajouter des scorecards KPI inspirées PMM: QPS, latence, connexions, buffer pool, replication lag, erreurs et seuils contextualisés par rôle serveur.",
    },
    24084: {
        "title": "MySQL 8: valider settings et dérives de configuration",
        "proposal": "Ajouter un validateur MySQL 8 qui compare configuration effective, valeurs par défaut, variables dépréciées, paramètres dangereux et recommandations par rôle.",
    },
    28208: {
        "title": "XtraBackup + AWS KMS: contrôler backups chiffrés table par table",
        "proposal": "Ajouter des contrôles AWS KMS pour XtraBackup: keyring, accès aux clés, tables chiffrées, logs de backup/prepare et test restore.",
    },
    27155: {
        "title": "MySQL vs PostgreSQL: formaliser périmètre et critères de choix",
        "proposal": "Créer une fiche comparative non intrusive pour documenter pourquoi PmaControl cible MySQL/MariaDB, quels signaux PostgreSQL sont hors périmètre et quels concepts restent réutilisables.",
    },
    27450: {
        "title": "Backup/recovery: checklist transversale RPO, RTO et restore test",
        "proposal": "Ajouter une checklist backup/recovery par environnement: RPO, RTO, fréquence, rétention, chiffrement, restore test, propriétaire et dernière preuve de restauration.",
    },
    27418: {
        "title": "Compression backup: détecter qpress/QuickLZ déprécié",
        "proposal": "Ajouter un contrôle des algorithmes de compression utilisés par les backups: qpress/QuickLZ, alternatives supportées, compatibilité restore et dette de migration.",
    },
    20382: {
        "title": "information_schema: limiter les requêtes lourdes et timeouts UI",
        "proposal": "Ajouter des garde-fous autour des requêtes information_schema: estimation coût, timeout, cache, pagination et signalement des écrans qui interrogent trop large.",
    },
    25763: {
        "title": "Percona Server + AWS KMS: auditer keyring et dépendances cloud",
        "proposal": "Ajouter un audit keyring AWS KMS: plugin, credentials, région, accès aux clés, dépendance réseau et impact backup/restore.",
    },
    25668: {
        "title": "Logs MySQL dynamiques: sécuriser chemins et collecte",
        "proposal": "Ajouter un contrôle des emplacements de logs dynamiques: chemins autorisés, droits fichiers, rotation, collecte PmaControl et prévention fuite de chemins/secrets.",
    },
    25540: {
        "title": "Auth plugin MySQL: diagnostiquer caching_sha2_password manquant",
        "proposal": "Ajouter un diagnostic d'authentification client/serveur: plugin requis, librairie cliente, version PHP/MySQL, erreur observable et action corrective.",
    },
    25402: {
        "title": "xbcloud Azure Blob: superviser backups objet et restaurabilité",
        "proposal": "Ajouter un suivi xbcloud/Azure Blob: bucket/container, upload, retries, taille, chiffrement, coûts, lifecycle et test restore depuis objet.",
    },
    23769: {
        "title": "Clone Plugin vs XtraBackup: conseiller méthode de copie MySQL",
        "proposal": "Ajouter un advisor de méthode de clonage: Clone Plugin, XtraBackup, dump logique, contraintes de version, locks, réseau et procédure de rollback.",
    },
    22381: {
        "title": "Backups MySQL: tableau de bonnes pratiques et preuves restore",
        "proposal": "Ajouter un tableau de conformité backup MySQL: stratégie, fréquence, rétention, chiffrement, test restore, monitoring de fraîcheur et risques connus.",
    },
    22394: {
        "title": "Percona Operator MySQL: superviser backup/restore PXC",
        "proposal": "Ajouter une intégration Operator/PXC pour suivre ressources de backup/restore, statut Kubernetes, erreurs, secrets, storage class et preuve de restauration.",
    },
    22378: {
        "title": "PXC encrypt-cluster-traffic: auditer TLS inter-nœuds",
        "proposal": "Ajouter un audit TLS PXC: pxc-encrypt-cluster-traffic, certificats, expiration, homogénéité cluster, erreurs SSL et impact SST/IST.",
    },
    22313: {
        "title": "XtraBackup 8.x / MySQL 8.0.20: vérifier compatibilité",
        "proposal": "Ajouter une règle de compatibilité XtraBackup/MySQL 8.0.20+: versions supportées, redo format, options incompatibles et message opérateur avant backup.",
    },
    19057: {
        "title": "keyring_vault: contrôler backup Percona Server chiffré",
        "proposal": "Ajouter une validation des backups sur Percona Server avec keyring_vault: disponibilité Vault, plugin, configuration, logs XtraBackup et restore test.",
    },
    19116: {
        "title": "Réplication MySQL 8.0 vers 5.7: signaler incompatibilités",
        "proposal": "Ajouter un advisor de réplication descendante MySQL 8.0 -> 5.7: binlog, GTID, charset, DDL, types non supportés et risque applicatif.",
    },
    18356: {
        "title": "Migration vers Amazon RDS avec XtraBackup: plan et limites",
        "proposal": "Ajouter un assistant migration RDS via XtraBackup: prérequis AWS, compatibilité version, taille, downtime, import, validation et rollback.",
    },
    17973: {
        "title": "MySQL auth_socket: auditer comptes et accès applicatifs",
        "proposal": "Ajouter un contrôle auth_socket: comptes concernés, méthode de connexion PmaControl, risques de lockout, différence OS/MySQL et action de remédiation.",
    },
    17652: {
        "title": "ZFS pour MySQL: suivre ARC, sync, snapshots et latence",
        "proposal": "Ajouter une vue ZFS orientée MySQL: ARC, sync, recordsize, snapshots, compression, latence I/O et corrélation avec workload.",
    },
    16660: {
        "title": "MariaDB dashboard PMM: importer les signaux utiles à PmaControl",
        "proposal": "Créer une comparaison PMM/PmaControl pour MariaDB: métriques absentes, deadlocks, transactions, variables, dashboards et priorités d'intégration.",
    },
    16146: {
        "title": "CVE XtraBackup encryption IV: détecter backups vulnérables",
        "proposal": "Ajouter une règle CVE XtraBackup qui identifie versions vulnérables, backups chiffrés concernés, exposition et action de rotation/restauration.",
    },
    9384: {
        "title": "Checklist DBA MySQL: reprendre les contrôles transverses d'administration",
        "proposal": "Transformer l'article comparatif en checklist DBA MySQL: backups, sécurité, modélisation, monitoring, capacity planning et runbooks PmaControl.",
    },
    14689: {
        "title": "MaxScale read-write split: mesurer routage et performance",
        "proposal": "Ajouter une vue MaxScale read-write split: services, rules, backend choisi, latence, erreurs, sessions et comparaison avec ProxySQL.",
    },
    9438: {
        "title": "Réplication parallèle MySQL 5.6: estimer bénéfice et limites",
        "proposal": "Ajouter un estimateur de bénéfice de réplication parallèle: workload, commits, lag, workers, contention et compatibilité version.",
    },
    9024: {
        "title": "CVE-2015-1027 Percona: enrichir l'inventaire sécurité",
        "proposal": "Ajouter une règle CVE dédiée Percona Server/XtraDB: versions affectées, preuve de version, exposition et remédiation recommandée.",
    },
    9151: {
        "title": "Risque d'exploitation DB: chiffrer coût de non-maintenance",
        "proposal": "Ajouter un score de risque exploitation: backups non testés, monitoring incomplet, versions obsolètes, incidents répétés et coût potentiel.",
    },
    9060: {
        "title": "PXC GTID + replicas async: assistant de topologie hybride",
        "proposal": "Ajouter un assistant topologie PXC avec GTID et replicas async: prérequis, configuration, statut, failover et risques de divergence.",
    },
    8035: {
        "title": "OpenSSL/Heartbleed: auditer exposition TLS historique",
        "proposal": "Ajouter une famille de contrôles TLS historiques: version OpenSSL, services exposés, certificats à renouveler, rotation secrets et exceptions documentées.",
    },
    6580: {
        "title": "GTID MySQL 5.6: créer/restaurer un replica proprement",
        "proposal": "Ajouter un runbook guidé de création/restauration replica GTID MySQL 5.6: backup source, coordonnées GTID, démarrage, validation et erreurs fréquentes.",
    },
    1872: {
        "title": "XtraBackup incrémental: historiser chaîne, bugs et restore",
        "proposal": "Ajouter une vue chaîne incrémentale XtraBackup: base full, incréments, versions outil, bugs connus, prepare, restore test et maillon manquant.",
    },
}


def load_remote_auth() -> tuple[str, str, str, tuple[str, str]]:
    url = subprocess.check_output(["git", "config", "--get", "remote.origin.url"], cwd=ROOT, text=True).strip()
    parsed = urlparse(url)
    owner, repo = parsed.path.strip("/").removesuffix(".git").split("/", 1)
    base = f"{parsed.scheme}://{parsed.hostname}"
    return base, owner, repo, (parsed.username or "", parsed.password or "")


def bullets(items: list[str]) -> str:
    return "\n".join(f"- {item}" for item in items)


def marker(candidate: dict[str, Any]) -> str:
    return f"percona_post_id:{candidate.get('post_id')};rule:{candidate.get('primary_rule')}"


def issue_title(candidate: dict[str, Any]) -> str:
    focus = FOCUS.get(int(candidate["post_id"]))
    title = focus["title"] if focus else candidate["title"]
    value = f"[Veille Percona] {title}"
    return value[:245]


def issue_body(candidate: dict[str, Any]) -> str:
    focus = FOCUS.get(int(candidate["post_id"]))
    proposal = focus["proposal"] if focus else "Recentrer l'amélioration sur le signal technique exact de l'article et le comparer au module PmaControl concerné."
    topics = ", ".join(candidate.get("topics") or [])
    tags = ", ".join((candidate.get("tags") or [])[:12])
    matched = ", ".join(item["key"] for item in candidate.get("matched_rules", [])[:5])
    local_path = f"docs/veille_techno/percona/{candidate['local_path']}"
    existing = {
        "backup_xtrabackup": "`Backup`, `Recover`, `Archives`, `Percona`, `Job`",
        "replication_diagnostics": "`Replication`, `Slave`, `Binlog`, `Galera`, `Dashboard`",
        "security_tls_auth": "`Audit`, `MysqlUser`, `Cve`, `CheckConfig`, `Alert`",
        "upgrade_advisor": "`Upgrade`, `Version`, `Schema`, `Variable`, `CheckConfig`",
        "proxysql_observability": "`ProxySQL`, `Dashboard`, `Monitoring`, `Digest`",
        "online_schema_change": "`Schema`, `MysqlTable`, `Partition`, `Job`",
        "config_drift": "`CheckConfig`, `CompareConfig`, `Variable`, `Server`",
        "disk_io_monitoring": "`Disk`, `StorageArea`, `Monitoring`, `Alert`",
        "lock_deadlock_monitoring": "`Monitoring`, `StatementAnalysis`, `Mysqlsys`, `PostMortem`",
        "maxscale_observability": "`MaxScale`, `Listener`, `Monitoring`, `Dot3`",
        "query_analysis": "`Digest`, `StatementAnalysis`, `Query`, `Explain`, `Myxplain`",
    }.get(candidate.get("primary_rule"), "`PmaControl`")

    acceptance = [
        "Le ticket expose le signal exact de l'article Percona, sans titre générique réutilisé.",
        "L'UI ou le rapport PmaControl proposé montre la preuve collectée, l'impact opérateur et l'action recommandée.",
        "Les résultats sont filtrables par serveur/cluster et exportables en Markdown ou JSON pour post-mortem.",
        "Les secrets, chemins sensibles et commandes sont redacted dans les vues et journaux.",
    ]
    implementation = [
        f"Comparer d'abord avec les modules existants: {existing}.",
        "Ajouter une collecte minimale et testable avant d'étendre l'UI.",
        "Prévoir une règle de déduplication par source Percona et domaine fonctionnel.",
    ]
    excerpt = candidate.get("excerpt") or "Non disponible dans la fiche de veille."
    structure = candidate.get("structure") or "Non disponible dans la fiche de veille."
    return f"""## Contexte veille

- Source Percona: {candidate.get('source_url')}
- Fiche locale: `{local_path}`
- Post Percona ID: `{candidate.get('post_id')}`
- Publication: {candidate.get('published_at')}
- Auteur source: {candidate.get('source_author') or 'Percona'}
- Thèmes: {topics}
- Tags: {tags}
- Score triage Codex: {candidate.get('score')}
- Règles détectées: {matched}

## Correction du triage Codex

Le premier passage avait utilisé une règle trop large pour plusieurs articles, notamment `backup_xtrabackup`.
Cette issue est recentrée sur le signal réel de l'article, afin que le titre et la proposition correspondent au contenu.

## Signal Percona retenu

Article: `{candidate.get('title')}`

Extrait public:

> {excerpt}

Structure détectée:

`{structure}`

## Comparaison avec PmaControl

Existant à vérifier: {existing}.

Écart à traiter: PmaControl doit transformer ce signal de veille en contrôle, écran, rapport ou assistant opérateur actionnable, plutôt qu'en entrée générique de backlog.

## Proposition d'amélioration recentrée

{proposal}

## Critères d'acceptation proposés

{bullets(acceptance)}

## Pistes d'implémentation

{bullets(implementation)}

## Passe Claude

Le commentaire Claude déjà présent dans ce thread reste la deuxième passe indépendante. Il doit être lu comme une critique de la première proposition et comme matériau pour la V2.

---

Marqueur de déduplication: `{marker(candidate)}`
""".strip()


def main() -> int:
    base, owner, repo, auth = load_remote_auth()
    records = [json.loads(line) for line in ISSUES_PATH.read_text(encoding="utf-8").splitlines() if line.strip()]
    missing = [record for record in records if int(record["candidate"]["post_id"]) not in FOCUS]
    if missing:
        raise RuntimeError(f"Missing focus mappings for {[record['issue_number'] for record in missing]}")

    for record in records:
        issue_number = int(record["issue_number"])
        candidate = record["candidate"]
        payload = {"title": issue_title(candidate), "body": issue_body(candidate)}
        response = requests.patch(
            f"{base}/api/v1/repos/{owner}/{repo}/issues/{issue_number}",
            auth=auth,
            json=payload,
            timeout=30,
        )
        if response.status_code not in (200, 201):
            print(f"#{issue_number}: {response.status_code} {response.text}")
            response.raise_for_status()
        print(f"updated #{issue_number}: {payload['title']}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
