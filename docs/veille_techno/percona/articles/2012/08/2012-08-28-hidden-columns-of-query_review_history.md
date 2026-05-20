---
title: Hidden columns of query_review_history table
source:
  name: Percona Blog
  url: https://www.percona.com/blog/hidden-columns-of-query_review_history/
  post_id: 3728
source_author:
  name: Roman Vynar
  slug: weber
  url: https://www.percona.com/blog/author/weber/
  website: ''
published_at: '2012-08-28T08:10:18'
published_at_gmt: '2012-08-28T08:10:18'
modified_at: '2026-03-20T06:37:55'
modified_at_gmt: '2026-03-20T06:37:55'
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
- pt-query-digest
tag_slugs:
- pt-query-digest
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/anemometer3.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Hidden columns of query_review_history table

Source: [Percona Blog](https://www.percona.com/blog/hidden-columns-of-query_review_history/)

Auteur source: [Roman Vynar](https://www.percona.com/blog/author/weber/)

Publication: 2012-08-28T08:10:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

You can use pt-query-digest to process a MySQL slow query log and store historical values for review trend analysis into query_review_history table. According to its official documentation you can populate many columns in that table but there are other important ones such as ‘user’, ‘host’, ‘db’ which are not included by default. I will explain … Continued

## Images et graphiques reperes

- featured / image: [Hidden columns of query_review_history table](https://www.percona.com/wp-content/uploads/2026/03/anemometer3.png)

## Auteur source

Lead Platform Engineer at Percona. Developing monitoring tools, automated scripts and leading Percona Monitoring and Management project.
