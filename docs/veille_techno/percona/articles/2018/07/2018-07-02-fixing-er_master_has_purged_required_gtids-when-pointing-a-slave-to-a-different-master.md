---
title: Fixing ER_MASTER_HAS_PURGED_REQUIRED_GTIDS when pointing a slave to a different master
source:
  name: Percona Blog
  url: https://www.percona.com/blog/fixing-er_master_has_purged_required_gtids-when-pointing-a-slave-to-a-different-master/
  post_id: 18958
source_author:
  name: Jaime Sicam
  slug: jaimesicam
  url: https://www.percona.com/blog/author/jaimesicam/
  website: ''
published_at: '2018-07-02T11:50:08'
published_at_gmt: '2018-07-02T11:50:08'
modified_at: '2026-05-05T19:28:50'
modified_at_gmt: '2026-05-05T19:28:50'
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
- GTID-based replication
- GTID-replication
- MySQL
- MySQL GTID
- Replication
tag_slugs:
- gtid-based-replication
- gtid-replication
- mysql
- mysql-gtid
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/gtid-auto-position.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Fixing ER_MASTER_HAS_PURGED_REQUIRED_GTIDS when pointing a slave to a different master

Source: [Percona Blog](https://www.percona.com/blog/fixing-er_master_has_purged_required_gtids-when-pointing-a-slave-to-a-different-master/)

Auteur source: [Jaime Sicam](https://www.percona.com/blog/author/jaimesicam/)

Publication: 2018-07-02T11:50:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

GTID replication has made it convenient to setup and maintain MySQL replication. You need not worry about binary log file and position thanks to GTID and auto-positioning. However, things can go wrong when pointing a slave to a different master. Consider a situation where the new master has executed transactions that haven’t been executed on … Continued

## Structure detectee

- H4: The scenario
- H2: Solution
- H2: Summary

## Images et graphiques reperes

- featured / image: [Fixing ER_MASTER_HAS_PURGED_REQUIRED_GTIDS when pointing a slave to a different master](https://www.percona.com/wp-content/uploads/2026/03/gtid-auto-position.jpg)
- content / image: [gtid auto position](https://www.percona.com/wp-content/uploads/2026/03/gtid-auto-position-300x201.jpg)

## Auteur source

Jaime is a Senior Support Engineer at Percona. Prior to joining Percona, Jaime worked as a remote system administrator managing high-traffic websites and consultant for several local companies. He also conducted Linux trainings in several schools. Jaime is based in the Philippines. He enjoys road trips and photography in his spare time.
