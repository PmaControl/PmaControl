---
title: 'The MySQL Clone Wars: Plugin vs. Percona XtraBackup'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-mysql-clone-wars-plugin-vs-percona-xtrabackup/
  post_id: 23769
source_author:
  name: Pep Pla
  slug: pep-pla
  url: https://www.percona.com/blog/author/pep-pla/
  website: ''
published_at: '2021-01-19T17:20:55'
published_at_gmt: '2021-01-19T17:20:55'
modified_at: '2026-03-23T18:14:04'
modified_at_gmt: '2026-03-23T18:14:04'
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
- Benchmarks
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
- percona-software
tags:
- InnoDB
- MySQL
- mysql-and-variants
- Percona Software
- Percona XtraBackup
- Performance
tag_slugs:
- innodb
- mysql
- mysql-and-variants
- percona-software
- percona-xtrabackup
- performance
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Plugin-vs.-Percona-XtraBackup.png
image_count: 8
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The MySQL Clone Wars: Plugin vs. Percona XtraBackup

Source: [Percona Blog](https://www.percona.com/blog/the-mysql-clone-wars-plugin-vs-percona-xtrabackup/)

Auteur source: [Pep Pla](https://www.percona.com/blog/author/pep-pla/)

Publication: 2021-01-19T17:20:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Large replication topologies are quite common nowadays, and this kind of architecture often requires a quick method to rebuild a replica from another server. The Clone Plugin, available since MySQL 8.0.17, is a great feature that allows cloning databases out of the box. It is easy to rebuild a replica or to add new nodes … Continued

## Structure detectee

- H2: Test Characteristics
- H2: Method
- H3: Clone
- H3: Percona XtraBackup
- H2: Results
- H3: Clone
- H3: Percona XtraBackup
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [The MySQL Clone Wars: Plugin vs. Percona XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Plugin-vs.-Percona-XtraBackup.png)
- content / image: [MySQL Plugin vs. Percona XtraBackup](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Plugin-vs.-Percona-XtraBackup-300x157.png)
- content / image: [Clone Plugin performance without compression.](https://www.percona.com/wp-content/uploads/2026/03/Clone-without-compression-1.png)
- content / image: [clone plugin with compression](https://www.percona.com/wp-content/uploads/2026/03/Clone-with-compression-1.png)
- content / image: [Percona Xtrabackup stream without compression](https://www.percona.com/wp-content/uploads/2026/03/Xtrabackup-stream-without-compression-1.png)
- content / image: [Percona Xtrabackup stream with compression](https://www.percona.com/wp-content/uploads/2026/03/Xtrabackup-stream-performance-compression-1.png)
- content / image: [Xtrabackup vs. Clone plugin - results summary](https://www.percona.com/wp-content/uploads/2026/03/Xtrabackup-vs-Clone-Results-Summary.png)
- content / image: [Worst-and-Best-2-1.png](https://www.percona.com/wp-content/uploads/2026/03/Worst-and-Best-2-1.png)

## Auteur source

Pep has been working with databases all his life. Born in a small village by the Mediterranean, he currently lives in Barcelona. He loves tech, traveling, good food, music and, all things NASA. He hates talking about himself in the third person and has a particular sense of humor. Happily married, he is the father of three boys and three cats.
