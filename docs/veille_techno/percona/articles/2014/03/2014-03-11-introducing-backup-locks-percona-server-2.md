---
title: Introducing backup locks in Percona Server
source:
  name: Percona Blog
  url: https://www.percona.com/blog/introducing-backup-locks-percona-server-2/
  post_id: 7920
source_author:
  name: Alexey Kopytov
  slug: alexey-kopytov
  url: https://www.percona.com/blog/author/alexey-kopytov/
  website: ''
published_at: '2014-03-11T12:00:07'
published_at_gmt: '2014-03-11T12:00:07'
modified_at: '2026-05-04T22:16:20'
modified_at_gmt: '2026-05-04T22:16:20'
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
- Alexey Kopytov
- backup locks
- mydumper
- mylvmbackup
- mysqldump
- Online backups
- Percona Server for MySQL
- Percona XtraBackup
tag_slugs:
- alexey-kopytov
- backup-locks
- mydumper
- mylvmbackup
- mysqldump
- online-backups
- percona-server
- percona-xtrabackup
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Introducing backup locks in Percona Server

Source: [Percona Blog](https://www.percona.com/blog/introducing-backup-locks-percona-server-2/)

Auteur source: [Alexey Kopytov](https://www.percona.com/blog/author/alexey-kopytov/)

Publication: 2014-03-11T12:00:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

TL;DR version: The backup locks feature introduced in Percona Server 5.6.16-64.0 is a lightweight alternative to FLUSH TABLES WITH READ LOCK and can be used to take both physical and logical backups with less downtime on busy servers. To employ the feature with mysqldump, use mysqldump –lock-for-backup –single-transaction. The next release of Percona XtraBackup will … Continued

## Structure detectee

- H2: In the beginning…
- H2: Online backups
- H2: Present
- H2: What’s the problem with FTWRL anyway?
- H2: To FLUSH or not to FLUSH ?
- H2: Backup locks
- H3: LOCK TABLES FOR BACKUP
- H3: LOCK BINLOG FOR BACKUP
- H2: mysqldump
- H2: Percona XtraBackup
- H2: mylvmbackup
- H2: mydumper

## Auteur source

Alexey Kopytov is a Principal Software Engineer at Percona. Before joining Percona in 2010 he was a member of the MySQL development team at Oracle. His focus at Percona is development of both Percona Server and Percona XtraBackup.
