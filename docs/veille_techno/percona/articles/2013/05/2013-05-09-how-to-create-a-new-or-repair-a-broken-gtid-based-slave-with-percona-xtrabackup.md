---
title: How to Create a New (or Repair a Broken) GTID-Based Slave with Percona XtraBackup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-create-a-new-or-repair-a-broken-gtid-based-slave-with-percona-xtrabackup/
  post_id: 6953
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2013-05-09T10:00:25'
published_at_gmt: '2013-05-09T10:00:25'
modified_at: '2026-05-04T22:04:11'
modified_at_gmt: '2026-05-04T22:04:11'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
- tag:percona-xtrabackup:330
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- GTID-based slave
- Percona XtraBackup
tag_slugs:
- gtid-based-slave
- percona-xtrabackup
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Create a New (or Repair a Broken) GTID-Based Slave with Percona XtraBackup

Source: [Percona Blog](https://www.percona.com/blog/how-to-create-a-new-or-repair-a-broken-gtid-based-slave-with-percona-xtrabackup/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2013-05-09T10:00:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona XtraBackup 2.0.7 has been published with support for GTID based replication. As promised, here is the step-by-step guide on how to create a new GTID based slave (or repair a broken one) using XtraBackup. The process is pretty straightforward. 1- Take a backup from any server on the replication environment, master or slave: # innobackupex /destination/ 1 # innobackupex /destination/ … Continued

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
