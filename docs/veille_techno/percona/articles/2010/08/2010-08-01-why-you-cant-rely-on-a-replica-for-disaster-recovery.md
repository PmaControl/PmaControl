---
title: Why you can’t rely on a replica for disaster recovery
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-you-cant-rely-on-a-replica-for-disaster-recovery/
  post_id: 2427
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-08-01T01:26:48'
published_at_gmt: '2010-08-01T01:26:48'
modified_at: '2026-03-23T21:44:20'
modified_at_gmt: '2026-03-23T21:44:20'
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
- Backups
- drbd
- ext2
- InnoDB
- Replication
- SAN
tag_slugs:
- backups
- drbd
- ext2
- innodb
- replication
- san
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why you can’t rely on a replica for disaster recovery

Source: [Percona Blog](https://www.percona.com/blog/why-you-cant-rely-on-a-replica-for-disaster-recovery/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-08-01T01:26:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A couple of weeks ago one of my colleagues and I worked on a data corruption case that reminded me that sometimes people make unsafe assumptions without knowing it. This one involved SAN snapshotting that was unsafe. In a nutshell, the client used SAN block-level replication to maintain a standby/failover MySQL system, and there was … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
