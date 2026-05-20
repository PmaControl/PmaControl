---
title: A Guide to Better Understanding MySQL Charset Levels
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-guide-to-better-understanding-mysql-charset-levels/
  post_id: 28343
source_author:
  name: Roberto De Bem
  slug: roberto-garciadebem
  url: https://www.percona.com/blog/author/roberto-garciadebem/
  website: ''
published_at: '2024-04-16T13:24:05'
published_at_gmt: '2024-04-16T13:24:05'
modified_at: '2026-03-26T20:26:27'
modified_at_gmt: '2026-03-26T20:26:27'
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
- Character Sets
- MySQL
- MySQL Character Sets
- mysql-and-variants
- Percona Server for MySQL
- utf8mb4
tag_slugs:
- character-sets
- mysql
- mysql-character-sets
- mysql-and-variants
- percona-server
- utf8mb4
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Understanding-Charset-Levels-in-MySQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Guide to Better Understanding MySQL Charset Levels

Source: [Percona Blog](https://www.percona.com/blog/a-guide-to-better-understanding-mysql-charset-levels/)

Auteur source: [Roberto De Bem](https://www.percona.com/blog/author/roberto-garciadebem/)

Publication: 2024-04-16T13:24:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

We usually receive and see some questions regarding the charset levels in MySQL, especially after the deprecation of utf8mb3 and the new default uf8mb4. If you understand how the charset works on MySQL but have some questions regarding this change, please check out Migrating to utf8mb4: Things to Consider by Sveta Smirnova. Some of the … Continued

## Structure detectee

- H2: Server character set
- H2: Database charset
- H2: Table charset
- H2: Column charset
- H3: Keeping it simple
- H3: What can I modify?
- H2: Client charset

## Images et graphiques reperes

- featured / image: [A Guide to Better Understanding MySQL Charset Levels](https://www.percona.com/wp-content/uploads/2026/03/Understanding-Charset-Levels-in-MySQL.jpg)
