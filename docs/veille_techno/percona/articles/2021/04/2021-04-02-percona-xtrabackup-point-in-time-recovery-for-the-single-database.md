---
title: Percona XtraBackup Point-In-Time Recovery for the Single Database
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtrabackup-point-in-time-recovery-for-the-single-database/
  post_id: 24051
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2021-04-02T16:15:42'
published_at_gmt: '2021-04-02T16:15:42'
modified_at: '2026-04-27T22:24:34'
modified_at_gmt: '2026-04-27T22:24:34'
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
- backup
- InnoDB
- MySQL
- mysql-and-variants
- Percona XtraBackup
- pitr
- restore
tag_slugs:
- backup
- innodb
- mysql
- mysql-and-variants
- percona-xtrabackup
- pitr
- restore
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-Point-In-Time-Recovery.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraBackup Point-In-Time Recovery for the Single Database

Source: [Percona Blog](https://www.percona.com/blog/percona-xtrabackup-point-in-time-recovery-for-the-single-database/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2021-04-02T16:15:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recovering to a particular time in the past is called Point-In-Time Recovery (PITR). With PITR you can rollback unwanted DELETE without WHERE clause or any other harmful command. PITR with Percona XtraBackup is pretty straightforward and perfectly described in the user manual. You need to restore the data from the backup, then apply all binary … Continued

## Structure detectee

- H2: Percona XtraBackup Point-In-Time Recovery
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Percona XtraBackup Point-In-Time Recovery for the Single Database](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-Point-In-Time-Recovery.png)
- content / image: [Percona XtraBackup Point-In-Time Recovery](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraBackup-Point-In-Time-Recovery-300x157.png)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
