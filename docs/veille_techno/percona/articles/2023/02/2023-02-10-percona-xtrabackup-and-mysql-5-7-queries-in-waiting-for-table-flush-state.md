---
title: Percona XtraBackup and MySQL 5.7 Queries in Waiting for Table Flush State
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtrabackup-and-mysql-5-7-queries-in-waiting-for-table-flush-state/
  post_id: 26579
source_author:
  name: Lalit Choudhary
  slug: lalit-choudhary
  url: https://www.percona.com/blog/author/lalit-choudhary/
  website: ''
published_at: '2023-02-10T15:23:06'
published_at_gmt: '2023-02-10T15:23:06'
modified_at: '2026-03-26T20:30:13'
modified_at_gmt: '2026-03-26T20:30:13'
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
- Storage Engine
category_slugs:
- insight-for-dbas
- mysql
- percona-software
- storage-engine
tags:
- Locking
- MySQL
- mysql-and-variants
- Percona XtraBackup
tag_slugs:
- locking
- mysql
- mysql-and-variants
- percona-xtrabackup
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraBackup and MySQL 5.7 Queries in Waiting for Table Flush State

Source: [Percona Blog](https://www.percona.com/blog/percona-xtrabackup-and-mysql-5-7-queries-in-waiting-for-table-flush-state/)

Auteur source: [Lalit Choudhary](https://www.percona.com/blog/author/lalit-choudhary/)

Publication: 2023-02-10T15:23:06

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona XtraBackup is an open source hot backup utility for MySQL-based servers. To take consistent and hot backup, it uses various locking methods, especially for non-transactional storage engine tables. This blog post discusses the cause and possible solution for queries with ​Waiting for table flush state in processlist when taking backups using Percona XtraBackup. Only … Continued

## Structure detectee

- H2: Type of locks taken by Percona XtraBackup
- H2: Root cause
- H2: Possible solutions

## Auteur source

Lalit works as a Database Engineer in Percona. His main professional interests are problem-solving, working on database issues and testing.
