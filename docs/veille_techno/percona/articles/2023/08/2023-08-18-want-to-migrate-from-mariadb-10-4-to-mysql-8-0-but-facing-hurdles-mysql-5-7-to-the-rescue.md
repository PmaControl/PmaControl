---
title: Want to Migrate From MariaDB 10.4 to MySQL 8.0 but Facing Hurdles? MySQL 5.7 to the Rescue!
source:
  name: Percona Blog
  url: https://www.percona.com/blog/want-to-migrate-from-mariadb-10-4-to-mysql-8-0-but-facing-hurdles-mysql-5-7-to-the-rescue/
  post_id: 27385
source_author:
  name: Rituja Borse
  slug: rituja-borse
  url: https://www.percona.com/blog/author/rituja-borse/
  website: ''
published_at: '2023-08-18T12:15:20'
published_at_gmt: '2023-08-18T12:15:20'
modified_at: '2026-03-26T20:29:11'
modified_at_gmt: '2026-03-26T20:29:11'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
categories:
- Insight for DBAs
- MariaDB
- MySQL
category_slugs:
- insight-for-dbas
- mariadb
- mysql
tags:
- MariaDB
- MySQL
- mysql-and-variants
tag_slugs:
- mariadb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Migrate-From-MariaDB-10.4-to-MySQL-8.0.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Want to Migrate From MariaDB 10.4 to MySQL 8.0 but Facing Hurdles? MySQL 5.7 to the Rescue!

Source: [Percona Blog](https://www.percona.com/blog/want-to-migrate-from-mariadb-10-4-to-mysql-8-0-but-facing-hurdles-mysql-5-7-to-the-rescue/)

Auteur source: [Rituja Borse](https://www.percona.com/blog/author/rituja-borse/)

Publication: 2023-08-18T12:15:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Note that MariaDB 10.4 is not the latest version and has had new releases since 10.4. The client requirement was to move to MySQL 8.0 on the cloud for specific RDS features. Caution: It is important to verify that you are not using any specific MariaDB features before migrating to MySQL 8.0.x. Recently, we had … Continued

## Structure detectee

- H2: The incompatibility roadblock
- H2: Importing tablespace — risky along with downtime
- H2: The logical backup/restore approach — but how to keep data in sync?
- H2: Exploring the next option — MySQL 5.7 intermediate replica
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Want to Migrate From MariaDB 10.4 to MySQL 8.0 but Facing Hurdles? MySQL 5.7 to the Rescue!](https://www.percona.com/wp-content/uploads/2026/03/Migrate-From-MariaDB-10.4-to-MySQL-8.0.jpg)
- content / image: [MariaDB to MySQL](https://www.percona.com/wp-content/uploads/2026/03/image-15-1-1024x513.png)

## Auteur source

Rituja joined Percona in 2019 and is on the Percona Managed Services team, focusing on improving MySQL database performance.
