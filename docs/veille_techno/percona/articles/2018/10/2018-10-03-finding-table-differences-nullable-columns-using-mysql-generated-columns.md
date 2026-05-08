---
title: Finding Table Differences on Nullable Columns Using MySQL Generated Columns
source:
  name: Percona Blog
  url: https://www.percona.com/blog/finding-table-differences-nullable-columns-using-mysql-generated-columns/
  post_id: 19070
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2018-10-03T13:56:47'
published_at_gmt: '2018-10-03T13:56:47'
modified_at: '2026-05-05T19:19:17'
modified_at_gmt: '2026-05-05T19:19:17'
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
- MySQL Query Tuning
- query tuning
tag_slugs:
- mysql-query-tuning
- query-tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-generated-columns-1.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Finding Table Differences on Nullable Columns Using MySQL Generated Columns

Source: [Percona Blog](https://www.percona.com/blog/finding-table-differences-nullable-columns-using-mysql-generated-columns/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2018-10-03T13:56:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Some time ago, a customer had a performance issue with an internal process. He was comparing, finding, and reporting the rows that were different between two tables. This is simple if you use a LEFT JOIN and an IS NULL comparison over the second table in the WHERE clause, but what if the column could be … Continued

## Structure detectee

- H2: The challenge in more detail
- H2: Solution
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [Finding Table Differences on Nullable Columns Using MySQL Generated Columns](https://www.percona.com/wp-content/uploads/2026/03/MySQL-generated-columns-1.jpg)
- content / image: [MySQL generated columns](https://www.percona.com/wp-content/uploads/2026/03/MySQL-generated-columns-1-300x200.jpg)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
