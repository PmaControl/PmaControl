---
title: Another reason why SQL_SLAVE_SKIP_COUNTER is bad in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/another-reason-why-sql_slave_skip_counter-is-bad-in-mysql/
  post_id: 7207
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2013-07-23T13:41:32'
published_at_gmt: '2013-07-23T13:41:32'
modified_at: '2026-05-05T16:51:32'
modified_at_gmt: '2026-05-05T16:51:32'
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
- MySQL
category_slugs:
- mysql
tags:
- ROW-based replication
- SQL_SLAVE_SKIP_COUNTER
tag_slugs:
- row-based-replication
- sql_slave_skip_counter
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Another reason why SQL_SLAVE_SKIP_COUNTER is bad in MySQL

Source: [Percona Blog](https://www.percona.com/blog/another-reason-why-sql_slave_skip_counter-is-bad-in-mysql/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2013-07-23T13:41:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It is everywhere in the world of MySQL that if your replication is broken because an event caused a duplicate key or a row was not found and it cannot be updated or deleted, then you can use ‘ STOP SLAVE ; SET GLOBAL SQL_SLAVE_SKIP_COUNTER = 1 ; START SLAVE ; ‘ and be done with it. In some cases this is fine and you can repair … Continued

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
