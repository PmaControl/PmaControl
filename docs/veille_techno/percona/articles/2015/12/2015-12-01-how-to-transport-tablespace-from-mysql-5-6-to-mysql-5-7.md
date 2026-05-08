---
title: Transporting tablespace from MySQL 5.6 to MySQL 5.7 (case study)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-transport-tablespace-from-mysql-5-6-to-mysql-5-7/
  post_id: 10225
source_author:
  name: Nilnandan Joshi
  slug: nilnandan-joshi-2
  url: https://www.percona.com/blog/author/nilnandan-joshi-2/
  website: ''
published_at: '2015-12-01T07:32:03'
published_at_gmt: '2015-12-01T07:32:03'
modified_at: '2026-04-28T22:45:58'
modified_at_gmt: '2026-04-28T22:45:58'
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
categories:
- MySQL
category_slugs:
- mysql
tags:
- alter table
- GTID
- IMPORT TABLESPACE
- MySQL 5.7
tag_slugs:
- alter-table
- gtid
- import-tablespace
- mysql-5-7
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/generatedata_installation_complete.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Transporting tablespace from MySQL 5.6 to MySQL 5.7 (case study)

Source: [Percona Blog](https://www.percona.com/blog/how-to-transport-tablespace-from-mysql-5-6-to-mysql-5-7/)

Auteur source: [Nilnandan Joshi](https://www.percona.com/blog/author/nilnandan-joshi-2/)

Publication: 2015-12-01T07:32:03

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently, I was working on a MySQL support ticket where a customer was facing an issue while transporting tablespace from MySQL 5.6 to MySQL 5.7. After closely reviewing the situation, I saw that while importing tablespace they were getting errors such as: Shell ERROR 1808 (HY000): Schema mismatch (Table flags don't match, server table has 0x10 and the meta-data file has 0x1) 1 ERROR 1808 ( HY000 ) : Schema mismatch ( Table flags don ' t match , server table has 0x10 and the meta - data file has 0x1 ) After some research, I found that there is a similar bug reported … Continued

## Images et graphiques reperes

- featured / image: [Transporting tablespace from MySQL 5.6 to MySQL 5.7 (case study)](https://www.percona.com/wp-content/uploads/2026/03/generatedata_installation_complete.png)
- content / image: [MySQL 5.7](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_290056829-300x200.jpg)

## Auteur source

Nilnandan officially started with Percona as a Support Engineer. Before joining Percona, he has worked as a MySQL Database administrator with different types of service based companies managing high-traffic websites and web applications. Nilnandan has extensive experience in database design and development, database management, client management, security/documentations/training, implementing DRM solutions, automating backups and high availability. Nilnandan is based at Pune (India). In his spare time, he likes to listen Indian classical/semi-classical music, watching tv, playing cricket/badminton and hang out with his family.
