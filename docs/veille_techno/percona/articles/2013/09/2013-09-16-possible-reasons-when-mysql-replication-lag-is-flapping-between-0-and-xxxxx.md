---
title: 5 reasons why MySQL replication lag is flapping between 0 and XXXXX
source:
  name: Percona Blog
  url: https://www.percona.com/blog/possible-reasons-when-mysql-replication-lag-is-flapping-between-0-and-xxxxx/
  post_id: 7280
source_author:
  name: Roman Vynar
  slug: weber
  url: https://www.percona.com/blog/author/weber/
  website: ''
published_at: '2013-09-16T10:00:20'
published_at_gmt: '2013-09-16T10:00:20'
modified_at: '2026-03-25T17:06:23'
modified_at_gmt: '2026-03-25T17:06:23'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL Replication Lag
- Replication
tag_slugs:
- mysql-replication-lag
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/nagios-delay.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# 5 reasons why MySQL replication lag is flapping between 0 and XXXXX

Source: [Percona Blog](https://www.percona.com/blog/possible-reasons-when-mysql-replication-lag-is-flapping-between-0-and-xxxxx/)

Auteur source: [Roman Vynar](https://www.percona.com/blog/author/weber/)

Publication: 2013-09-16T10:00:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Working day to day with Percona Remote DBA customers, we have been facing an issue from time to time when MySQL replication lag is flapping between 0 and XXXXX constantly – i.e. Seconds_Behind_Master is 0 for a few secs, then it’s like 6287 or 25341, again 0 and so on. I would like to note … Continued

## Structure detectee

- H4: 1. Duplicate server-ids on two or more slaves.
- H4: 2. Dual-master setup, “log_slave_updates” enabled, server-ids changed.
- H4: 3. MySQL options “sync_relay_log”, “sync_relay_log_info”, “sync_master_info”.
- H4: 4. Network latency.
- H4: 5. Late committed transactions.

## Images et graphiques reperes

- featured / image: [5 reasons why MySQL replication lag is flapping between 0 and XXXXX](https://www.percona.com/wp-content/uploads/2026/03/nagios-delay.png)

## Auteur source

Lead Platform Engineer at Percona. Developing monitoring tools, automated scripts and leading Percona Monitoring and Management project.
