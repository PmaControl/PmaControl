---
title: When Do InnoDB Table Statistics Update?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-does-innodb-update-table-statistics-and-when-it-can-bite/
  post_id: 3091
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2011-10-07T04:26:29'
published_at_gmt: '2011-10-07T04:26:29'
modified_at: '2026-04-28T21:28:40'
modified_at_gmt: '2026-04-28T21:28:40'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Table-Statistics.jpeg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# When Do InnoDB Table Statistics Update?

Source: [Percona Blog](https://www.percona.com/blog/when-does-innodb-update-table-statistics-and-when-it-can-bite/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2011-10-07T04:26:29

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

InnoDB table statistics are used for JOIN optimizations and helping the MySQL optimizer choose the appropriate index for a query. If a table’s statistics or index cardinality becomes outdated, you might see queries which previously performed well suddenly show up on slow query log until InnoDB again updates the statistics. But when does InnoDB perform … Continued

## Images et graphiques reperes

- featured / image: [When Do InnoDB Table Statistics Update?](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Table-Statistics.jpeg)

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
