---
title: Quick comparison of MyISAM, Infobright, and MonetDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/quick-comparison-of-myisam-infobright-and-monetdb/
  post_id: 2028
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2009-09-30T02:56:58'
published_at_gmt: '2009-09-30T02:56:58'
modified_at: '2026-04-28T21:03:10'
modified_at_gmt: '2026-04-28T21:03:10'
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
category_slugs:
- mysql
tags:
- Infobright
- MonetDB
- MyISAM
- Optimizer
tag_slugs:
- infobright
- monetdb
- myisam
- optimizer
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/load_time.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Quick comparison of MyISAM, Infobright, and MonetDB

Source: [Percona Blog](https://www.percona.com/blog/quick-comparison-of-myisam-infobright-and-monetdb/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2009-09-30T02:56:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I was doing a little work for a client who has MyISAM tables with many columns (the same one Peter wrote about recently). The client’s performance is suffering in part because of the number of columns, which is over 200. The queries are generally pretty simple (sums of columns), but they’re ad-hoc (can access … Continued

## Structure detectee

- H3: The tests
- H3: Notes on Infobright
- H3: Notes on MonetDB

## Images et graphiques reperes

- featured / image: [Quick comparison of MyISAM, Infobright, and MonetDB](https://www.percona.com/wp-content/uploads/2026/03/load_time.png)
- content / image: [Table Size in Bytes](https://www.percona.com/wp-content/uploads/2026/03/table_size_bytes.png)
- content / image: [MonetDB vs Infobright Query Time](https://www.percona.com/wp-content/uploads/2026/03/monetdb_infobright_query_time1.png)

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
