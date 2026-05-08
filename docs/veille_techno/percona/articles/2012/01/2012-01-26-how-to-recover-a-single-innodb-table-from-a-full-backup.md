---
title: How to Recover a Single InnoDB Table from a Full Backup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-recover-a-single-innodb-table-from-a-full-backup/
  post_id: 3323
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2012-01-26T02:50:09'
published_at_gmt: '2012-01-26T02:50:09'
modified_at: '2026-03-23T22:14:04'
modified_at_gmt: '2026-03-23T22:14:04'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-toolkit
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Recover a Single InnoDB Table from a Full Backup

Source: [Percona Blog](https://www.percona.com/blog/how-to-recover-a-single-innodb-table-from-a-full-backup/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2012-01-26T02:50:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Sometimes we need to restore only some tables from a full backup maybe because your data loss affect a small number of your tables. In this particular scenario is faster to recover single tables than a full backup. This is easy with MyISAM but if your tables are InnoDB the process is a little bit … Continued

## Structure detectee

- H2: Prerequisites to Recover an InnoDB Table
- H2: Recovering the Table from the InnoDB Backup
- H2: Using Percona Server for MySQL makes Single Table Recovery Easier

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
