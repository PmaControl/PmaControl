---
title: Solve Query Failures Regarding ONLY_FULL_GROUP_BY SQL Mode
source:
  name: Percona Blog
  url: https://www.percona.com/blog/solve-query-failures-regarding-only_full_group_by-sql-mode/
  post_id: 20276
source_author:
  name: Corrado Pandiani
  slug: corrado-pandiani
  url: https://www.percona.com/blog/author/corrado-pandiani/
  website: ''
published_at: '2019-05-13T13:30:07'
published_at_gmt: '2019-05-13T13:30:07'
modified_at: '2026-05-05T20:27:53'
modified_at_gmt: '2026-05-05T20:27:53'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Open Source
- Percona Software
category_slugs:
- mysql
- open-source
- percona-software
tags:
- group_by
- MySQL
- MySQL 5.7
- only_full_group_by
- sql_mode
tag_slugs:
- group_by
- mysql
- mysql-5-7
- only_full_group_by
- sql_mode
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Solve-Query-Failures-SQL-mode.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Solve Query Failures Regarding ONLY_FULL_GROUP_BY SQL Mode

Source: [Percona Blog](https://www.percona.com/blog/solve-query-failures-regarding-only_full_group_by-sql-mode/)

Auteur source: [Corrado Pandiani](https://www.percona.com/blog/author/corrado-pandiani/)

Publication: 2019-05-13T13:30:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

“Hey, what’s going on with my applications? I installed a newer version of MySQL. I have queries that perfectly run with the older version and now I have a lot of errors.” This is a question some customers have asked me after upgrading MySQL. In this article, we’ll see what one of the most frequent … Continued

## Structure detectee

- H2: SQL_MODE
- H2: The ONLY_FULL_GROUP_BY issue
- H3: Solution 1 – rewrite the query
- H3: Solution 2 – step back to the forgiving mode
- H3: Solution 3 – use of aggregation functions
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Solve Query Failures Regarding ONLY_FULL_GROUP_BY SQL Mode](https://www.percona.com/wp-content/uploads/2026/03/Solve-Query-Failures-SQL-mode.jpg)

## Auteur source

Prior to joining Percona as a Senior Consultant, Corrado spent more than 20 years in developing web sites and designing and administering MySQL. He is a MySQL enthusiast since version 3.23 and his skills are focused on performances and architectural design. He's also a trainer and a MongoDB consultant.
