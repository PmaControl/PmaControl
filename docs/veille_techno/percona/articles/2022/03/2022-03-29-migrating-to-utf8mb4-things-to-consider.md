---
title: 'Migrating to utf8mb4: Things to Consider'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/migrating-to-utf8mb4-things-to-consider/
  post_id: 25522
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2022-03-29T12:00:17'
published_at_gmt: '2022-03-29T12:00:17'
modified_at: '2026-05-05T16:43:01'
modified_at_gmt: '2026-05-05T16:43:01'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- Character Sets
- MySQL
- MySQL character se
- mysql-and-variants
- unicode
- utf8
- utf8mb4
tag_slugs:
- character-sets
- mysql
- mysql-character-se
- mysql-and-variants
- unicode
- utf8
- utf8mb4
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Migrating-to-utf8mb4.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Migrating to utf8mb4: Things to Consider

Source: [Percona Blog](https://www.percona.com/blog/migrating-to-utf8mb4-things-to-consider/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2022-03-29T12:00:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The utf8mb4 character set is the new default as of MySQL 8.0, and this change neither affects existing data nor forces any upgrades. Migration to utf8mb4 has many advantages including: It can store more symbols, including emojis It has new collations for Asian languages It is faster than utf8mb3 Still, you may wonder how migration … Continued

## Structure detectee

- H2: Storage Requirements
- H2: Maximum Length of the Column
- H2: Index Storage Requirement
- H2: Temporary Tables
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Migrating to utf8mb4: Things to Consider](https://www.percona.com/wp-content/uploads/2026/03/Migrating-to-utf8mb4.png)
- content / image: [Migrating to utf8mb4](https://www.percona.com/wp-content/uploads/2026/03/Migrating-to-utf8mb4-300x169.png)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
