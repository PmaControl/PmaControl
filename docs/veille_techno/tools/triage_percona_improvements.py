#!/usr/bin/env python3
"""Triage Percona watch notes into actionable PmaControl improvement issues.

The script analyzes every imported Percona Markdown note, scores it against
PmaControl feature domains, writes a candidate report, and can create Forgejo
issues with a Codex proposal plus an independent Claude comment.
"""

from __future__ import annotations

import argparse
import json
import os
import re
import subprocess
import sys
import textwrap
import time
from dataclasses import dataclass
from pathlib import Path
from typing import Any
from urllib.parse import urlparse

import requests
import yaml


ROOT = Path(__file__).resolve().parents[3]
WATCH_ROOT = Path(__file__).resolve().parents[1]
PERCONA_ROOT = WATCH_ROOT / "percona"
REPORT_PATH = PERCONA_ROOT / "_data" / "improvement_candidates.jsonl"
REPORT_MD_PATH = PERCONA_ROOT / "improvement_candidates.md"
CREATED_PATH = PERCONA_ROOT / "_data" / "issues_created.jsonl"
CLAUDE_ENV_PATH = ROOT / ".env_claude"

LABEL_NAME = "enhancement"
LABEL_COLOR = "84b6eb"
LABEL_DESCRIPTION = "Feature request or improvement"

EXCLUDE_RE = re.compile(
    r"\b("
    r"webinar|q\s*&\s*a|q&a|podcast|conference|slides?|video|meetup|expo|"
    r"release roundup|release notes?|released|is now available|now available|announc|percona live|"
    r"training|survey|recap|call for papers|sponsoring|hiring"
    r")\b",
    re.I,
)

NON_TARGET_TITLE_RE = re.compile(r"\b(mongodb|postgresql|postgres|kubernetes|operator|valkey|redis|pg_stat|pgvector)\b", re.I)
TARGET_TITLE_RE = re.compile(
    r"\b(mysql|mariadb|proxysql|maxscale|xtrabackup|pmm|qan|percona toolkit|pt-online-schema-change|pt-query-digest)\b",
    re.I,
)


@dataclass(frozen=True)
class Rule:
    key: str
    title: str
    domain: str
    weight: int
    patterns: tuple[str, ...]
    existing: tuple[str, ...]
    gap: str
    proposal: str
    acceptance: tuple[str, ...]
    implementation: tuple[str, ...]


RULES: tuple[Rule, ...] = (
    Rule(
        key="query_analysis",
        title="Ajouter une analyse QAN-like des requêtes et fingerprints",
        domain="Monitoring / Query",
        weight=6,
        patterns=(r"\bquery analysis\b", r"\bqan\b", r"\bfingerprint", r"\bpt-query-digest\b", r"\bdigest\b"),
        existing=("Pmm", "Digest", "DigestReport", "StatementAnalysis", "Query", "Explain", "Myxplain"),
        gap=(
            "PmaControl possède déjà des écrans autour des requêtes, digest et EXPLAIN, "
            "mais l'expérience PMM/QAN orientée fingerprint, latence, charge et drill-down n'est pas encore formalisée comme parcours opérateur unique."
        ),
        proposal=(
            "Créer une vue d'analyse de requêtes par fingerprint: volume, temps cumulé, latence p95/p99 si disponible, lignes examinées, "
            "serveur/service, période, lien EXPLAIN/Myxplain, et comparaison avant/après incident."
        ),
        acceptance=(
            "Une page liste les fingerprints avec filtres période, serveur, base et utilisateur.",
            "Chaque fingerprint ouvre un détail avec exemples SQL redacted, métriques agrégées et lien vers EXPLAIN/Myxplain.",
            "La vue signale les régressions par rapport à une période de référence.",
        ),
        implementation=(
            "Réutiliser les contrôleurs `Digest`, `StatementAnalysis`, `Query`, `Explain` et la logique Chart.js existante.",
            "Stocker les agrégats dans les tables time-series existantes si la volumétrie brute est trop élevée.",
        ),
    ),
    Rule(
        key="online_schema_change",
        title="Ajouter un assistant online DDL / pt-online-schema-change",
        domain="Schema / Tooling",
        weight=7,
        patterns=(r"\bpt-online-schema-change\b", r"\bonline schema change\b", r"\balter table\b", r"\bonline ddl\b"),
        existing=("Schema", "MysqlTable", "Partition", "CheckDataOnCluster", "Job"),
        gap=(
            "PmaControl sait inspecter schémas, tables et partitions, mais ne propose pas de parcours guidé pour sécuriser un ALTER lourd "
            "avec estimation, prérequis réplication et suivi d'exécution."
        ),
        proposal=(
            "Créer un assistant d'ALTER online: estimation taille/table, vérification clé primaire/FK/triggers, choix méthode native ou pt-online-schema-change, "
            "dry-run, génération de commande, exécution job, suivi chunk/lag et rollback documenté."
        ),
        acceptance=(
            "Un opérateur peut prévisualiser un ALTER et obtenir les risques bloquants avant exécution.",
            "Le job suit le lag réplication et peut suspendre/arrêter selon seuil.",
            "L'historique conserve commande redacted, durée, résultat et métriques principales.",
        ),
        implementation=(
            "S'appuyer sur `Schema`, `MysqlTable`, `Job` et les helpers shell sécurisés.",
            "Ne jamais concaténer directement l'ALTER fourni; passer par une validation stricte et audit trail.",
        ),
    ),
    Rule(
        key="replication_diagnostics",
        title="Enrichir le diagnostic réplication, GTID, binlog et lag",
        domain="Replication",
        weight=6,
        patterns=(r"\breplication\b", r"\bgtid\b", r"\bbinlog\b", r"\brelay log\b", r"\bparallel replication\b", r"\bsemi.?sync\b", r"\blag\b"),
        existing=("Replication", "Slave", "Binlog", "MysqlRouter", "Dashboard"),
        gap=(
            "PmaControl expose déjà des vues réplication, slave/source et binlog, mais plusieurs diagnostics restent dispersés: GTID, compression binlog, lag multi-source et causes de blocage."
        ),
        proposal=(
            "Ajouter une page diagnostic réplication qui consolide état GTID, dernier événement relay/binlog, threads SQL/IO, lag par canal, erreurs récentes, "
            "risques de divergence et recommandations opérateur."
        ),
        acceptance=(
            "La vue affiche un statut par canal et différencie retard réseau, SQL thread bloqué et configuration incohérente.",
            "Les erreurs récentes sont reliées aux logs collectés et au serveur concerné.",
            "Un export Markdown/JSON peut être joint à un post-mortem.",
        ),
        implementation=(
            "Étendre `Replication`, `Slave`, `Binlog` et `MysqlLogCollector`.",
            "Prévoir compatibilité MySQL `SOURCE` et MariaDB `MASTER` selon les helpers déjà documentés.",
        ),
    ),
    Rule(
        key="proxysql_observability",
        title="Renforcer l'observabilité ProxySQL",
        domain="ProxySQL",
        weight=6,
        patterns=(r"\bproxysql\b", r"\bquery rules?\b", r"\bmysql_query_rules\b", r"\bconnection pool\b", r"\bhostgroup\b"),
        existing=("ProxySQL", "Dashboard", "Monitoring", "Digest"),
        gap=(
            "PmaControl a un contrôleur ProxySQL, mais peut mieux relier règles, hostgroups, backend health et statistiques de requêtes dans une vue opérateur cohérente."
        ),
        proposal=(
            "Ajouter un tableau ProxySQL orienté exploitation: dérive de configuration runtime/disk, règles actives, hostgroups, pool connexions, erreurs backend, "
            "top requêtes routées et alertes sur serveurs shunnés/offline."
        ),
        acceptance=(
            "La page compare runtime, memory et disk pour signaler une configuration non sauvegardée.",
            "Les query rules sont navigables avec compteur de hits et destination hostgroup.",
            "Les backends shunnés/offline déclenchent un signal visible dans Dashboard/Alert.",
        ),
        implementation=(
            "Réutiliser `ProxySQL`, `Monitoring`, `Digest` et `Alert`.",
            "Ajouter des collectes périodiques des tables `stats_*` de ProxySQL si absentes.",
        ),
    ),
    Rule(
        key="maxscale_observability",
        title="Renforcer l'observabilité MaxScale",
        domain="MaxScale",
        weight=6,
        patterns=(r"\bmaxscale\b", r"\bread.?write split\b", r"\bmaxscale router\b", r"\bmaxscale listener\b"),
        existing=("MaxScale", "Listener", "Dashboard", "Monitoring"),
        gap=(
            "PmaControl possède déjà des écrans MaxScale et Listener, mais l'analyse routeur/service/listener peut être mieux corrélée aux symptômes MySQL."
        ),
        proposal=(
            "Ajouter une vue MaxScale consolidée: services, listeners, routers, états backend, top erreurs, sessions actives, latence routeur et comparaison configuration déclarée/active."
        ),
        acceptance=(
            "Un écran affiche le chemin client -> listener -> service -> serveur backend.",
            "Les backends indisponibles ou désynchronisés sont reliés aux statuts Galera/Réplication.",
            "Les changements de configuration MaxScale sont audités et diffables.",
        ),
        implementation=(
            "Étendre `MaxScale`, `Listener`, `Monitoring` et la cartographie `Dot3`.",
            "Collecter via MaxScale REST API quand disponible, avec fallback CLI si nécessaire.",
        ),
    ),
    Rule(
        key="backup_xtrabackup",
        title="Ajouter un cockpit XtraBackup: compatibilité, validation et restore drill",
        domain="Backup",
        weight=6,
        patterns=(r"\bxtrabackup\b", r"\bpercona xtrabackup\b", r"\bbackup\b", r"\brestore\b", r"\bprepare\b", r"\bxbstream\b"),
        existing=("Backup", "Archives", "Recover", "Percona", "Job"),
        gap=(
            "PmaControl dispose déjà de domaines Backup/Recover, mais la compatibilité version serveur/XtraBackup, la validation des sauvegardes et les drills de restore peuvent être rendus explicites."
        ),
        proposal=(
            "Créer un cockpit XtraBackup avec matrice version serveur/outil, validation des logs de backup/prepare, estimation durée, statut chiffrement/compression, "
            "et workflow de test restore périodique."
        ),
        acceptance=(
            "Chaque backup affiche sa version outil, version serveur, type full/incrémental et statut prepare/restore-test.",
            "Une alerte apparaît si l'outil n'est pas compatible avec la version MySQL/MariaDB cible.",
            "Un job de restore drill produit un rapport attachable au serveur ou cluster.",
        ),
        implementation=(
            "Relier `Backup`, `Recover`, `Archives`, `Percona` et `Job`.",
            "Parser les logs XtraBackup sans exposer secrets ni chemins sensibles.",
        ),
    ),
    Rule(
        key="upgrade_advisor",
        title="Ajouter un conseiller d'upgrade MySQL/MariaDB",
        domain="Upgrade",
        weight=5,
        patterns=(r"\bupgrade\b", r"\bmysql 8\b", r"\bmysql 8\.4\b", r"\bmysql 5\.7\b", r"\bmariadb 10\b", r"\breserved words?\b", r"\bdeprecated\b"),
        existing=("Upgrade", "Version", "CheckConfig", "Schema", "Variable", "Cve"),
        gap=(
            "PmaControl sait inventorier versions, variables et schémas, mais ne formalise pas encore un rapport d'upgrade applicatif avec incompatibilités SQL, variables dépréciées et mots réservés."
        ),
        proposal=(
            "Ajouter un conseiller d'upgrade qui compare version courante/cible, détecte mots réservés dans les objets, variables supprimées/dépréciées, "
            "syntaxes à risque, plugins incompatibles et prérequis de backup."
        ),
        acceptance=(
            "Un rapport liste les bloqueurs, avertissements et actions recommandées par serveur/base.",
            "Les objets impactés par mots réservés ou types dépréciés sont cliquables.",
            "Le rapport peut être relancé après correction pour suivre la dette restante.",
        ),
        implementation=(
            "Étendre `Upgrade`, `Version`, `Schema`, `Variable` et `CheckConfig`.",
            "Maintenir une table de règles par version cible, testée sur fixtures SQL.",
        ),
    ),
    Rule(
        key="security_tls_auth",
        title="Ajouter un audit sécurité TLS, auth et LOCAL INFILE",
        domain="Security",
        weight=6,
        patterns=(r"\bssl\b", r"\btls\b", r"\bauthentication\b", r"\bauth plugin\b", r"\blocal infile\b", r"\bprivileges?\b", r"\baudit\b", r"\bsecurity\b"),
        existing=("Audit", "MysqlUser", "Cve", "CheckConfig", "Alert"),
        gap=(
            "PmaControl couvre déjà audit, utilisateurs MySQL et CVE, mais certains contrôles opérationnels comme TLS effectif, plugins d'auth et LOCAL INFILE peuvent devenir des checks standardisés."
        ),
        proposal=(
            "Créer un audit sécurité MySQL/MariaDB qui vérifie TLS requis/effectif, LOCAL INFILE, plugins d'authentification, comptes anonymes/partagés, "
            "droits globaux dangereux et cohérence avec les CVE applicables."
        ),
        acceptance=(
            "Un rapport classe les findings par criticité et serveur.",
            "Chaque finding explique la requête de preuve et l'action de remédiation.",
            "Les exceptions validées peuvent être historisées pour éviter les faux positifs récurrents.",
        ),
        implementation=(
            "Étendre `Audit`, `MysqlUser`, `Cve`, `CheckConfig` et `Alert`.",
            "Redacter tout secret et ne jamais stocker de mot de passe ou certificat privé.",
        ),
    ),
    Rule(
        key="lock_deadlock_monitoring",
        title="Ajouter une console locks, deadlocks et metadata locks",
        domain="Monitoring / InnoDB",
        weight=6,
        patterns=(r"\bdeadlock", r"\block wait\b", r"\bmetadata lock", r"\bmdl\b", r"\bconcurrency\b", r"\btransaction\b", r"\binnodb status\b"),
        existing=("Monitoring", "StatementAnalysis", "Mysqlsys", "Log", "PostMortem"),
        gap=(
            "PmaControl collecte et affiche déjà plusieurs métriques MySQL, mais l'analyse locks/deadlocks n'est pas exposée comme console dédiée corrélant transactions, requêtes et logs."
        ),
        proposal=(
            "Ajouter une console locks/deadlocks: transactions longues, waits actifs, metadata locks, dernier deadlock, requêtes impliquées, utilisateur, host, "
            "et action opérateur recommandée."
        ),
        acceptance=(
            "La page montre les blockers/waiters en graphe ou table hiérarchique.",
            "Le dernier deadlock est parsé depuis InnoDB status ou logs et associé aux requêtes.",
            "Un snapshot peut être capturé pour post-mortem avant kill éventuel.",
        ),
        implementation=(
            "Utiliser Performance Schema / Information Schema selon version et droits.",
            "Relier à `PostMortem`, `StatementAnalysis`, `Log` et `Mysqlsys`.",
        ),
    ),
    Rule(
        key="schema_health",
        title="Ajouter un audit santé schéma: PK, auto_increment, FK et tables volumineuses",
        domain="Schema",
        weight=5,
        patterns=(r"\bprimary key\b", r"\bwithout primary key\b", r"\bauto_increment\b", r"\bibdata1\b", r"\bforeign key\b", r"\bfragmentation\b", r"\bbig tables?\b"),
        existing=("Schema", "MysqlTable", "ForeignKey", "Partition", "Disk"),
        gap=(
            "PmaControl dispose d'écrans schéma/table/FK, mais les signaux de santé schéma peuvent être regroupés en scoring actionnable."
        ),
        proposal=(
            "Ajouter un audit santé schéma avec tables sans PK, auto_increment proche limite, FK incohérentes, tables très volumineuses, partitionnement absent et croissance disque."
        ),
        acceptance=(
            "Le rapport classe les tables par risque et indique la preuve SQL.",
            "Les seuils auto_increment et taille table sont configurables.",
            "Chaque finding propose une action: ajout PK, BIGINT, partitionnement, purge ou archivage.",
        ),
        implementation=(
            "Étendre `Schema`, `MysqlTable`, `ForeignKey`, `Partition` et `Disk`.",
            "Conserver un historique pour détecter les tendances de croissance.",
        ),
    ),
    Rule(
        key="config_drift",
        title="Ajouter un advisor configuration MySQL/MariaDB",
        domain="Configuration",
        weight=5,
        patterns=(r"\bmy\.cnf\b", r"\bconfiguration\b", r"\bvariables?\b", r"\bsysvar\b", r"\bdefaults?\b", r"\bparameter\b", r"\btuning\b"),
        existing=("CheckConfig", "CompareConfig", "Variable", "Percona", "Server"),
        gap=(
            "PmaControl compare déjà des configurations et variables, mais il manque un advisor qui transforme les écarts et paramètres à risque en recommandations priorisées."
        ),
        proposal=(
            "Créer un advisor de configuration: dérive entre serveurs comparables, valeurs non défaut critiques, variables dépréciées, paramètres dangereux et cohérence avec workload/role."
        ),
        acceptance=(
            "La page regroupe les serveurs par rôle et compare leurs variables effectives.",
            "Les écarts sont classés en info/warning/blocker avec justification.",
            "Les recommandations sont exportables et historisées.",
        ),
        implementation=(
            "Étendre `CheckConfig`, `CompareConfig`, `Variable` et `Server`.",
            "Ajouter une base de règles versionnée par produit et version majeure.",
        ),
    ),
    Rule(
        key="crash_postmortem",
        title="Ajouter un pipeline crash/core dump/post-mortem MySQL",
        domain="PostMortem",
        weight=5,
        patterns=(r"\bcrash\b", r"\bcore dump", r"\bcoredump", r"\bsigterm\b", r"\bsigkill\b", r"\bassertion\b", r"\bsegfault\b"),
        existing=("PostMortem", "Log", "IntegrateLog", "Alert", "MysqlLogCollector"),
        gap=(
            "PmaControl a déjà un domaine PostMortem et des collectes de logs, mais les crashes MySQL peuvent être mieux structurés: timeline, preuves, core dump et corrélation métriques."
        ),
        proposal=(
            "Ajouter un pipeline post-mortem crash: détection signatures crash, collecte logs autour de l'événement, statut core dump, version binaire, uptime, charge, "
            "et génération d'un rapport opérateur."
        ),
        acceptance=(
            "Un crash ouvre un événement avec timeline avant/après.",
            "Le rapport inclut version serveur, dernier signal, extrait de logs redacted et métriques clés.",
            "Les répétitions de même signature sont groupées.",
        ),
        implementation=(
            "Étendre `PostMortem`, `Log`, `IntegrateLog`, `Alert` et `MysqlLogCollector`.",
            "Ne pas ingérer de core dump complet dans l'application; stocker seulement métadonnées et chemin sécurisé.",
        ),
    ),
    Rule(
        key="disk_io_monitoring",
        title="Ajouter une vue corrélation disque, filesystem et performance MySQL",
        domain="Disk / Monitoring",
        weight=5,
        patterns=(r"\bdisk\b", r"\bio\b", r"\bi/o\b", r"\bfilesystem\b", r"\bext4\b", r"\bxfs\b", r"\bssd\b", r"\bnvme\b", r"\bfsync\b"),
        existing=("Disk", "Monitoring", "StorageArea", "Alert", "Dashboard"),
        gap=(
            "PmaControl dispose de domaines Disk/Monitoring/StorageArea, mais les symptômes MySQL liés au filesystem et à l'I/O peuvent être corrélés plus directement."
        ),
        proposal=(
            "Ajouter une vue corrélation I/O: latence disque, saturation, filesystems, fsync, croissance datadir/binlogs/tmp, et impact sur requêtes, réplication et backups."
        ),
        acceptance=(
            "La page affiche les métriques disque pertinentes par serveur et volume.",
            "Les alertes disque sont corrélées à lag réplication, lenteur requêtes ou backup long.",
            "Les seuils tiennent compte du rôle serveur et du type de volume.",
        ),
        implementation=(
            "Étendre `Disk`, `StorageArea`, `Monitoring`, `Alert` et `Dashboard`.",
            "Ajouter une normalisation des noms de volumes pour relier datadir, tmpdir et binlog dir.",
        ),
    ),
)


def split_front_matter(path: Path) -> tuple[dict[str, Any], str]:
    text = path.read_text(encoding="utf-8")
    if not text.startswith("---\n"):
        return {}, text
    end = text.index("\n---\n", 4)
    return yaml.safe_load(text[4:end]) or {}, text[end + 5 :]


def normalize_space(value: str) -> str:
    return re.sub(r"\s+", " ", value).strip()


def clean_template(value: str) -> str:
    return re.sub(r"\n[ \t]{8}", "\n", textwrap.dedent(value).strip())


def extract_section(body: str, heading: str, limit: int = 900) -> str:
    pattern = re.compile(rf"^## {re.escape(heading)}\s*$", re.M)
    match = pattern.search(body)
    if not match:
        return ""
    rest = body[match.end() :]
    next_heading = re.search(r"^##\s+", rest, re.M)
    section = rest[: next_heading.start()] if next_heading else rest
    lines = [line.strip("- ").strip() for line in section.splitlines() if line.strip()]
    return normalize_space(" ".join(lines))[:limit]


def load_articles(percona_root: Path) -> list[dict[str, Any]]:
    articles: list[dict[str, Any]] = []
    for path in sorted((percona_root / "articles").rglob("*.md")):
        meta, body = split_front_matter(path)
        title = str(meta.get("title") or "")
        excerpt = extract_section(body, "Extrait public")
        structure = extract_section(body, "Structure detectee", limit=1200)
        images = extract_section(body, "Images et graphiques reperes", limit=900)
        haystack = normalize_space(
            " ".join(
                [
                    title,
                    excerpt,
                    structure,
                    images,
                    " ".join(meta.get("tags") or []),
                    " ".join(meta.get("categories") or []),
                    " ".join(meta.get("matched_topics") or []),
                ]
            )
        )
        articles.append(
            {
                "path": path,
                "relative_path": path.relative_to(percona_root).as_posix(),
                "meta": meta,
                "body": body,
                "title": title,
                "excerpt": excerpt,
                "structure": structure,
                "haystack": haystack,
            }
        )
    return articles


def score_article(article: dict[str, Any]) -> dict[str, Any] | None:
    haystack = article["haystack"]
    title = article["title"]
    meta = article["meta"]
    if NON_TARGET_TITLE_RE.search(title) and not TARGET_TITLE_RE.search(title):
        return None
    if EXCLUDE_RE.search(title) and not re.search(r"\b(best practices?|troubleshoot|diagnos|how to|guide|performance|security|audit)\b", title, re.I):
        return None

    matches: list[dict[str, Any]] = []
    for rule in RULES:
        hit_patterns = [pattern for pattern in rule.patterns if re.search(pattern, haystack, re.I)]
        if not hit_patterns:
            continue
        if rule.key == "backup_xtrabackup" and not re.search(
            r"\b(xtrabackup|innobackupex|xbstream|backup locks?)\b", haystack, re.I
        ):
            continue
        if rule.key == "online_schema_change" and not re.search(
            r"\b(pt-online-schema-change|online schema change|alter table|schema changes?|online ddl)\b",
            title,
            re.I,
        ):
            continue
        if rule.key == "maxscale_observability" and not re.search(r"\bmaxscale\b", haystack, re.I):
            continue
        if rule.key == "proxysql_observability" and not re.search(r"\bproxysql\b", haystack, re.I):
            continue
        rule_score = rule.weight + min(4, len(hit_patterns))
        if re.search(r"\b(best practices?|troubleshoot|diagnos|how to|guide|performance|monitor|security|backup|restore|audit)\b", haystack, re.I):
            rule_score += 2
        matches.append({"rule": rule, "score": rule_score, "patterns": hit_patterns})

    if not matches:
        return None

    title_penalty = 0
    if EXCLUDE_RE.search(title):
        title_penalty += 5
    if re.search(r"\b(postgresql|mongodb|kubernetes|operator)\b", haystack, re.I) and not re.search(
        r"\b(mysql|mariadb|proxysql|maxscale|xtrabackup|pmm)\b", haystack, re.I
    ):
        title_penalty += 8

    best = max(matches, key=lambda item: item["score"])
    rule: Rule = best["rule"]
    secondary_bonus = min(6, sum(1 for item in matches if item["rule"].key != rule.key and item["score"] >= 8) * 2)
    score = best["score"] + secondary_bonus - title_penalty
    if score < 8 or best["score"] < 7:
        return None

    return {
        "post_id": meta.get("source", {}).get("post_id"),
        "title": title,
        "source_url": meta.get("source", {}).get("url"),
        "published_at": meta.get("published_at"),
        "source_author": meta.get("source_author", {}).get("name"),
        "topics": meta.get("matched_topics") or [],
        "tags": meta.get("tags") or [],
        "local_path": article["relative_path"],
        "score": score,
        "primary_rule": rule.key,
        "primary_rule_title": rule.title,
        "domain": rule.domain,
        "matched_rules": [
            {
                "key": item["rule"].key,
                "title": item["rule"].title,
                "score": item["score"],
                "patterns": item["patterns"],
            }
            for item in sorted(matches, key=lambda item: item["score"], reverse=True)
        ],
        "excerpt": article["excerpt"][:900],
        "structure": article["structure"][:900],
    }


def render_issue_body(candidate: dict[str, Any]) -> str:
    rule = rule_by_key(candidate["primary_rule"])
    source_url = candidate.get("source_url") or ""
    local_path = f"docs/veille_techno/percona/{candidate['local_path']}"
    topics = ", ".join(candidate.get("topics") or [])
    tags = ", ".join((candidate.get("tags") or [])[:12])
    matched = ", ".join(item["key"] for item in candidate["matched_rules"][:5])
    excerpt = candidate.get("excerpt") or "Non disponible dans la fiche de veille."
    structure = candidate.get("structure") or "Non disponible dans la fiche de veille."
    return clean_template(
        f"""\
        ## Contexte veille

        - Source Percona: {source_url}
        - Fiche locale: `{local_path}`
        - Post Percona ID: `{candidate.get('post_id')}`
        - Publication: {candidate.get('published_at')}
        - Auteur source: {candidate.get('source_author') or 'Percona'}
        - Thèmes: {topics}
        - Tags: {tags}
        - Score triage Codex: {candidate['score']}
        - Règles détectées: {matched}

        ## Analyse Codex

        L'article `{candidate['title']}` porte un signal exploitable pour PmaControl dans le domaine **{rule.domain}**.

        Extrait public retenu pour la veille:

        > {excerpt}

        Structure détectée:

        `{structure}`

        ## Comparaison avec PmaControl

        Existant identifié: `{', '.join(rule.existing)}`.

        Écart: {rule.gap}

        ## Proposition d'amélioration

        {rule.proposal}

        ## Critères d'acceptation proposés

        {chr(10).join(f'- {item}' for item in rule.acceptance)}

        ## Pistes d'implémentation

        {chr(10).join(f'- {item}' for item in rule.implementation)}

        ## Passe Claude attendue

        Claude doit poster dans ce même thread sa propre analyse indépendante du même article, puis confirmer, nuancer ou corriger la proposition ci-dessus.

        ---

        Marqueur de déduplication: `percona_post_id:{candidate.get('post_id')};rule:{rule.key}`
        """
    )


def render_claude_prompt(candidate: dict[str, Any]) -> str:
    rule = rule_by_key(candidate["primary_rule"])
    return clean_template(
        f"""\
        Tu es Claude, deuxième passe indépendante du workflow PmaControl.

        Analyse le même article Percona et réponds en français par un commentaire Git/Forgejo prêt à poster.
        Tu dois signer clairement avec ton nom dans le contenu, avec un titre "## Analyse Claude".
        Ne recopie pas le texte complet de l'article. Utilise seulement les métadonnées, l'extrait public et la structure ci-dessous.

        Article:
        - Titre: {candidate['title']}
        - Source: {candidate.get('source_url')}
        - Date: {candidate.get('published_at')}
        - Thèmes: {', '.join(candidate.get('topics') or [])}
        - Tags: {', '.join((candidate.get('tags') or [])[:12])}
        - Extrait: {candidate.get('excerpt') or 'Non disponible'}
        - Structure: {candidate.get('structure') or 'Non disponible'}

        Proposition Codex à challenger:
        - Domaine: {rule.domain}
        - Existant PmaControl: {', '.join(rule.existing)}
        - Proposition: {rule.proposal}

        Réponds avec:
        1. Ton verdict sur la pertinence pour PmaControl.
        2. Les risques ou angles morts.
        3. Ta proposition V2 ou les ajustements à faire.
        4. 2 à 4 critères d'acceptation.

        Format court, technique, actionnable.
        """
    )


def rule_by_key(key: str) -> Rule:
    for rule in RULES:
        if rule.key == key:
            return rule
    raise KeyError(key)


def load_remote_auth() -> tuple[str, str, str, tuple[str, str]]:
    url = subprocess.check_output(["git", "config", "--get", "remote.origin.url"], cwd=ROOT, text=True).strip()
    parsed = urlparse(url)
    owner, repo = parsed.path.strip("/").removesuffix(".git").split("/", 1)
    base = f"{parsed.scheme}://{parsed.hostname}"
    return base, owner, repo, (parsed.username or "", parsed.password or "")


def ensure_label(base: str, owner: str, repo: str, auth: tuple[str, str]) -> int:
    labels_url = f"{base}/api/v1/repos/{owner}/{repo}/labels"
    response = requests.get(labels_url, auth=auth, timeout=30)
    response.raise_for_status()
    for item in response.json():
        if item["name"] == LABEL_NAME:
            return int(item["id"])
    response = requests.post(
        labels_url,
        auth=auth,
        json={"name": LABEL_NAME, "color": LABEL_COLOR, "description": LABEL_DESCRIPTION},
        timeout=30,
    )
    response.raise_for_status()
    return int(response.json()["id"])


def existing_issue_markers(base: str, owner: str, repo: str, auth: tuple[str, str]) -> set[str]:
    markers: set[str] = set()
    page = 1
    while True:
        response = requests.get(
            f"{base}/api/v1/repos/{owner}/{repo}/issues",
            auth=auth,
            params={"state": "all", "limit": 100, "page": page, "q": "percona_post_id:"},
            timeout=30,
        )
        response.raise_for_status()
        items = response.json()
        if not items:
            break
        for issue in items:
            body = issue.get("body") or ""
            markers.update(re.findall(r"percona_post_id:\d+;rule:[a-z0-9_]+", body))
        page += 1
    return markers


def create_issue(
    base: str,
    owner: str,
    repo: str,
    auth: tuple[str, str],
    candidate: dict[str, Any],
    label_id: int,
) -> dict[str, Any]:
    rule = rule_by_key(candidate["primary_rule"])
    title = f"[Veille Percona] {rule.title} — {candidate['title'][:80]}"
    body = render_issue_body(candidate)
    response = requests.post(
        f"{base}/api/v1/repos/{owner}/{repo}/issues",
        auth=auth,
        json={"title": title, "body": body, "labels": [label_id]},
        timeout=30,
    )
    response.raise_for_status()
    return response.json()


def load_claude_env() -> dict[str, str]:
    env: dict[str, str] = {}
    if not CLAUDE_ENV_PATH.exists():
        raise RuntimeError(f"Missing Claude env file: {CLAUDE_ENV_PATH}")
    for line in CLAUDE_ENV_PATH.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        if "${" in value:
            continue
        env[key] = value
    return env


def verify_claude(cl_env: dict[str, str], base: str, owner: str, repo: str) -> None:
    token = cl_env.get("GITEA_TOKEN")
    if not token:
        raise RuntimeError("Missing GITEA_TOKEN in Claude env")
    response = requests.get(f"{base}/api/v1/user", headers={"Authorization": f"token {token}"}, timeout=30)
    response.raise_for_status()
    login = response.json().get("login")
    if login != "claude":
        raise RuntimeError(f"Claude preflight failed: token login is {login!r}")
    repo_response = requests.get(
        f"{base}/api/v1/repos/{owner}/{repo}",
        headers={"Authorization": f"token {token}"},
        timeout=30,
    )
    repo_response.raise_for_status()


def generate_claude_comment(candidate: dict[str, Any], max_budget_usd: str) -> str:
    prompt = render_claude_prompt(candidate)
    result = subprocess.run(
        [
            "claude",
            "-p",
            prompt,
            "--max-budget-usd",
            max_budget_usd,
            "--permission-mode",
            "dontAsk",
            "--tools",
            "",
        ],
        cwd=ROOT,
        text=True,
        capture_output=True,
        timeout=180,
        check=False,
    )
    if result.returncode != 0:
        raise RuntimeError(result.stderr.strip() or result.stdout.strip() or f"Claude exited {result.returncode}")
    comment = result.stdout.strip()
    if "Claude" not in comment[:200]:
        comment = "## Analyse Claude\n\n" + comment
    return comment


def post_claude_comment(
    base: str,
    owner: str,
    repo: str,
    cl_env: dict[str, str],
    issue_number: int,
    body: str,
) -> dict[str, Any]:
    response = requests.post(
        f"{base}/api/v1/repos/{owner}/{repo}/issues/{issue_number}/comments",
        headers={"Authorization": f"token {cl_env['GITEA_TOKEN']}"},
        json={"body": body},
        timeout=30,
    )
    response.raise_for_status()
    return response.json()


def write_reports(candidates: list[dict[str, Any]]) -> None:
    REPORT_PATH.parent.mkdir(parents=True, exist_ok=True)
    with REPORT_PATH.open("w", encoding="utf-8") as stream:
        for candidate in candidates:
            serializable = dict(candidate)
            stream.write(json.dumps(serializable, ensure_ascii=False, sort_keys=True) + "\n")

    lines = [
        "# Candidats d'amélioration issus de la veille Percona",
        "",
        f"Nombre de candidats: {len(candidates)}",
        "",
        "Chaque ligne est issue d'une analyse des fiches Markdown importées depuis Percona.",
        "",
    ]
    for idx, candidate in enumerate(candidates, 1):
        lines.extend(
            [
                f"## {idx}. {candidate['primary_rule_title']}",
                "",
                f"- Article: [{candidate['title']}]({candidate['source_url']})",
                f"- Fiche locale: `{candidate['local_path']}`",
                f"- Score: {candidate['score']}",
                f"- Domaine: {candidate['domain']}",
                f"- Règles: {', '.join(item['key'] for item in candidate['matched_rules'][:5])}",
                "",
            ]
        )
    REPORT_MD_PATH.write_text("\n".join(lines), encoding="utf-8")


def create_issues(candidates: list[dict[str, Any]], limit: int, max_budget_usd: str, skip_claude: bool) -> None:
    base, owner, repo, auth = load_remote_auth()
    label_id = ensure_label(base, owner, repo, auth)
    markers = existing_issue_markers(base, owner, repo, auth)

    cl_env: dict[str, str] = {}
    if not skip_claude:
        cl_env = load_claude_env()
        verify_claude(cl_env, base, owner, repo)

    created = 0
    CREATED_PATH.parent.mkdir(parents=True, exist_ok=True)
    with CREATED_PATH.open("a", encoding="utf-8") as stream:
        for candidate in candidates:
            marker = f"percona_post_id:{candidate.get('post_id')};rule:{candidate['primary_rule']}"
            if marker in markers:
                continue
            issue = create_issue(base, owner, repo, auth, candidate, label_id)
            issue_number = int(issue["number"])
            issue_url = issue.get("html_url") or issue.get("url")
            print(f"created issue #{issue_number}: {issue_url}", file=sys.stderr)

            claude_comment_url = ""
            if not skip_claude:
                comment_body = generate_claude_comment(candidate, max_budget_usd)
                comment = post_claude_comment(base, owner, repo, cl_env, issue_number, comment_body)
                claude_comment_url = comment.get("html_url") or comment.get("url") or ""
                print(f"posted Claude comment on #{issue_number}", file=sys.stderr)

            stream.write(
                json.dumps(
                    {
                        "issue_number": issue_number,
                        "issue_url": issue_url,
                        "claude_comment_url": claude_comment_url,
                        "marker": marker,
                        "candidate": candidate,
                    },
                    ensure_ascii=False,
                    sort_keys=True,
                )
                + "\n"
            )
            markers.add(marker)
            created += 1
            if limit and created >= limit:
                break
            time.sleep(0.3)

    print(f"issues created: {created}", file=sys.stderr)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--percona-root", default=str(PERCONA_ROOT))
    parser.add_argument("--min-score", type=int, default=11)
    parser.add_argument("--limit", type=int, default=0, help="Limit issue creation; report still contains all candidates.")
    parser.add_argument("--create-issues", action="store_true")
    parser.add_argument("--skip-claude", action="store_true")
    parser.add_argument("--claude-budget", default="0.80")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    percona_root = Path(args.percona_root).resolve()
    articles = load_articles(percona_root)
    candidates = [candidate for article in articles if (candidate := score_article(article))]
    candidates = [candidate for candidate in candidates if candidate["score"] >= args.min_score]
    candidates.sort(key=lambda item: (item["score"], item.get("published_at") or ""), reverse=True)
    write_reports(candidates)
    print(f"articles analyzed: {len(articles)}", file=sys.stderr)
    print(f"candidates: {len(candidates)}", file=sys.stderr)
    print(f"report: {REPORT_MD_PATH}", file=sys.stderr)

    if args.create_issues:
        create_issues(candidates, args.limit, args.claude_budget, args.skip_claude)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
