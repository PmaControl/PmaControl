---
title: MySQL 5.6 Transportable Tablespaces best practices
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-5-6-transportable-tablespaces-best-practices/
  post_id: 8621
source_author:
  name: Muhammad Irfan
  slug: mirfan
  url: https://www.percona.com/blog/author/mirfan/
  website: ''
published_at: '2014-12-09T08:00:08'
published_at_gmt: '2014-12-09T08:00:08'
modified_at: '2026-04-28T22:11:57'
modified_at_gmt: '2026-04-28T22:11:57'
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
category_slugs:
- insight-for-dbas
- mysql
tags:
- InnoDB tablespace
- Muhammad Irfan
- MySQL
- Percona Server for MySQL
- Percona XtraBackup
- Primary
- Transportable Tablespace
- Vadim Tkachenko
tag_slugs:
- innodb-tablespace
- muhammad-irfan
- mysql
- percona-server
- percona-xtrabackup
- primary
- transportable-tablespace
- vadim-tkachenko
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-5.6-Transportable-Tablespaces-with-Percona-XtraBackup.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 5.6 Transportable Tablespaces best practices

Source: [Percona Blog](https://www.percona.com/blog/mysql-5-6-transportable-tablespaces-best-practices/)

Auteur source: [Muhammad Irfan](https://www.percona.com/blog/author/mirfan/)

Publication: 2014-12-09T08:00:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In MySQL 5.6 Oracle introduced a Transportable Tablespace feature (copying tablespaces to another server) and Percona Server adopted it for partial backups which means you can now take individual database or table backups and your destination server can be a vanilla MySQL server. Moreover, since Percona Server 5.6, innodb_import_table_from_xtrabackup is obsolete as Percona Server also … Continued

## Images et graphiques reperes

- featured / image: [MySQL 5.6 Transportable Tablespaces best practices](https://www.percona.com/wp-content/uploads/2026/03/MySQL-5.6-Transportable-Tablespaces-with-Percona-XtraBackup.jpg)

## Auteur source

Muhammad Irfan is vastly experienced in LAMP Stack. Prior to joining Percona Support, he worked in the role of MySQL DBA & LAMP Administrator, maintained high traffic websites, and worked as a Consultant. His professional interests focus on MySQL scalability and on performance optimization.
