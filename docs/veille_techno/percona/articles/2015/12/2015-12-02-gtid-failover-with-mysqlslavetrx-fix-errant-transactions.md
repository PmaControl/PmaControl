---
title: Fixing errant transactions with mysqlslavetrx prior to a GTID failover
source:
  name: Percona Blog
  url: https://www.percona.com/blog/gtid-failover-with-mysqlslavetrx-fix-errant-transactions/
  post_id: 10228
source_author:
  name: Daniel Guzmán Burgos
  slug: daniel-guzman-burgos
  url: https://www.percona.com/blog/author/daniel-guzman-burgos/
  website: ''
published_at: '2015-12-02T18:05:13'
published_at_gmt: '2015-12-02T18:05:13'
modified_at: '2026-05-05T22:32:31'
modified_at_gmt: '2026-05-05T22:32:31'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Errant transactions
- GTID
- GTID-replication
- MySQL Utilities
tag_slugs:
- errant-transactions
- gtid
- gtid-replication
- mysql-utilities
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_287206316.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Fixing errant transactions with mysqlslavetrx prior to a GTID failover

Source: [Percona Blog](https://www.percona.com/blog/gtid-failover-with-mysqlslavetrx-fix-errant-transactions/)

Auteur source: [Daniel Guzmán Burgos](https://www.percona.com/blog/author/daniel-guzman-burgos/)

Publication: 2015-12-02T18:05:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Errant transactions are a major issue when using GTID replication. Although this isn’t something new, the drawbacks are more notorious with GTID than with regular replication. The situation where errant transaction bites you is a common DBA task: Failover. Now that tools like MHA have support for GTID replication (starting from 0.56 version), this protocol … Continued

## Images et graphiques reperes

- featured / image: [Fixing errant transactions with mysqlslavetrx prior to a GTID failover](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_287206316.jpg)
- content / image: [GTID and errant transactions](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_287206316-300x300.jpg)

## Auteur source

Daniel studied Electronic Engineering, but quickly becomes interested in all data things. He has worked as a DBA since 2007 for several companies. Working for Percona since 2014, he is the PMM Tech Lead
