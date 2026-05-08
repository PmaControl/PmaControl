---
title: MySQL Backup tools used by Percona Remote DBA for MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-backup-tools-used-by-percona-remote-dba-for-mysql/
  post_id: 6680
source_author:
  name: Ryan Huddleston
  slug: ryan-huddleston
  url: https://www.percona.com/blog/author/ryan-huddleston/
  website: http://www.percona.com/products/mysql-remote-dba
published_at: '2013-03-13T14:08:07'
published_at_gmt: '2013-03-13T14:08:07'
modified_at: '2026-03-25T16:44:53'
modified_at_gmt: '2026-03-25T16:44:53'
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
- Cloud
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- Amazon S3
- data loss
- database outage
- database performance
- MySQL
- MySQL Backup tools
- Percona Remote DBA
- Percona XtraBackup
- Rryan Huddleston
tag_slugs:
- amazon-s3
- data-loss
- database-outage
- database-performance
- mysql
- mysql-backup-tools
- percona-remote-dba
- percona-xtrabackup
- rryan-huddleston
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Remote-DBA-for-MySQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Backup tools used by Percona Remote DBA for MySQL

Source: [Percona Blog](https://www.percona.com/blog/mysql-backup-tools-used-by-percona-remote-dba-for-mysql/)

Auteur source: [Ryan Huddleston](https://www.percona.com/blog/author/ryan-huddleston/)

Publication: 2013-03-13T14:08:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As part of Percona Remote DBA for MySQL service we recognize that reliable backups are one of the most important things we can bring to the table. In my experience handling emergencies, the single worst thing that can happen is finding out you don’t have backups available when some sort of data loss or catastrophic … Continued

## Structure detectee

- H2: What kind of outages can happen?
- H2: What tools do we use in Remote DBA?
- H2: Philosophy on backups
- H2: How do we use these components to give our customers reliable backups?
- H3: Percona XtraBackup for MySQL for binary backups.
- H3: Mydumper for logical backups
- H3: mysqlbinlog 5.6
- H3: Amazon S3 for MySQL
- H2: Monitoring
- H2: Other details on Percona Remote DBA for MySQL backup systems for future posts

## Images et graphiques reperes

- featured / image: [MySQL Backup tools used by Percona Remote DBA for MySQL](https://www.percona.com/wp-content/uploads/2026/03/Percona-Remote-DBA-for-MySQL.jpg)

## Auteur source

Ryan is a former Percona employee.
