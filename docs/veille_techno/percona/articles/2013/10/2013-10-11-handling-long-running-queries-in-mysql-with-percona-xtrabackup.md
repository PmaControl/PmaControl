---
title: Handling long-running queries in MySQL with Percona XtraBackup
source:
  name: Percona Blog
  url: https://www.percona.com/blog/handling-long-running-queries-in-mysql-with-percona-xtrabackup/
  post_id: 7454
source_author:
  name: Stephane Combaudon
  slug: stc
  url: https://www.percona.com/blog/author/stc/
  website: ''
published_at: '2013-10-11T13:00:41'
published_at_gmt: '2013-10-11T13:00:41'
modified_at: '2026-05-04T22:09:59'
modified_at_gmt: '2026-05-04T22:09:59'
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
- backup scripts
- FLUSH TABLES WITH READ LOCK
- long-running queries
- Percona XtraBackup
- Stephane Combaudon
tag_slugs:
- backup-scripts
- flush-tables-with-read-lock
- long-running-queries
- percona-xtrabackup
- stephane-combaudon
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Handling long-running queries in MySQL with Percona XtraBackup

Source: [Percona Blog](https://www.percona.com/blog/handling-long-running-queries-in-mysql-with-percona-xtrabackup/)

Auteur source: [Stephane Combaudon](https://www.percona.com/blog/author/stc/)

Publication: 2013-10-11T13:00:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently had a case where replication lag on a slave was caused by a backup script. First reaction was to incriminate the additional pressure on the disks, but it turned out to be more subtle: Percona XtraBackup was not able to execute FLUSH TABLES WITH READ LOCK due to a long-running query, and the … Continued

## Structure detectee

- H2: In short
- H2: Diagnosing the problem
- H2: Aborting the backup if long queries are running
- H2: Killing long running queries to allow the backup to complete
- H2: Conclusion

## Auteur source

Stéphane joined Percona in July 2012, after working as a MySQL DBA for leading French companies such as Dailymotion and France Telecom. In real life, he lives in Paris with his wife and their twin daughters. When not in front of a computer or not spending time with his family, he likes playing chess and hiking.
