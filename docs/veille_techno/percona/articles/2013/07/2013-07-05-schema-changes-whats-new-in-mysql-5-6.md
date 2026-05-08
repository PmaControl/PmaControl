---
title: Schema changes – what’s new in MySQL 5.6?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/schema-changes-whats-new-in-mysql-5-6/
  post_id: 7070
source_author:
  name: Przemysław Malkowski
  slug: przemek-malkowski
  url: https://www.percona.com/blog/author/przemek-malkowski/
  website: ''
published_at: '2013-07-05T14:08:01'
published_at_gmt: '2013-07-05T14:08:01'
modified_at: '2026-05-04T22:05:57'
modified_at_gmt: '2026-05-04T22:05:57'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- '5.6'
- alter table
- ddl
- High Availability
- MySQL 5.6
- pt-online-schema-change
tag_slugs:
- 5-6
- alter-table
- ddl
- high-availability
- mysql-5-6
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PL-17-02-e1494968115624.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Schema changes – what’s new in MySQL 5.6?

Source: [Percona Blog](https://www.percona.com/blog/schema-changes-whats-new-in-mysql-5-6/)

Auteur source: [Przemysław Malkowski](https://www.percona.com/blog/author/przemek-malkowski/)

Publication: 2013-07-05T14:08:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Among many of the improvements you can enjoy in MySQL 5.6, there is one that addresses a huge operational problem that most DBAs and System Administrators encounter in their life: schema changes. While it is usually not a problem for small tables or those in early stages of product life cycle, schema changes become a … Continued

## Structure detectee

- H2: PITA
- H2: WORKAROUNDS
- H2: LONG STORY
- H2: ONLINE(!) DDL in MySQL 5.6
- H3: Let’s see some examples in practice
- H3: Example 1 – reset auto-increment value for a column
- H3: Example 2 – DROP COLUMN
- H3: Example 3 – RENAME COLUMN
- H3: Example 4 – NEW ALTER TABLE OPTIONS
- H2: NEW DIAGNOSTICS
- H2: IS ONLINE DDL GOOD ENOUGH IN MySQL 5.6?
- H2: OVERHEAD
- H2: BUGS
- H2: CONCLUSION

## Images et graphiques reperes

- featured / image: [Schema changes – what’s new in MySQL 5.6?](https://www.percona.com/wp-content/uploads/2026/03/PL-17-02-e1494968115624.png)
- content / image: [MySQL 5.6](https://www.percona.com/wp-content/uploads/2026/03/MySQL_5-6-300x115.jpg)

## Auteur source

Przemek joined Support Team at Percona in August 2012. Before that he spent over five years working for Wikia.com (Quantcast Top 50) as System Administrator where he was a key person responsible for seamless building up MySQL powered database infrastructure. Besides MySQL he worked on maintaining all other parts of LAMP stack, with main focus on automation, monitoring and backups.
