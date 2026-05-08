---
title: 'New TokuDB 2.2.0 feature: more query progress information'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/new-tokudb-2-2-0-feature-more-query-progress-information/
  post_id: 9476
source_author:
  name: Zardosht.Kasheff
  slug: zardosht-kasheff
  url: https://www.percona.com/blog/author/zardosht-kasheff/
  website: ''
published_at: '2009-11-17T20:05:16'
published_at_gmt: '2009-11-17T20:05:16'
modified_at: '2026-05-04T22:40:43'
modified_at_gmt: '2026-05-04T22:40:43'
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
- features
- MySQL
- query+progress
- TokuDB
tag_slugs:
- features
- mysql
- queryprogress
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# New TokuDB 2.2.0 feature: more query progress information

Source: [Percona Blog](https://www.percona.com/blog/new-tokudb-2-2-0-feature-more-query-progress-information/)

Auteur source: [Zardosht.Kasheff](https://www.percona.com/blog/author/zardosht-kasheff/)

Publication: 2009-11-17T20:05:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Last spring, we added a feature that allows the user to see the progress of writes in a statement. Vadim liked it. In 2.2.0, in “show processlist”, we add progress information on reads. Here is an example of what “show processlist” displays on an update: mysql> show processlist G *************************** 1. row *************************** Id: 1 User: root Host: localhost db: test Command: Query Time: 7 State: Queried about 1576008 rows, Updated about 197000 rows Info: update foo set a=9 where a=8 1 2 3 4 5 6 7 8 9 10 mysql > show processlist G * * * * * * * * * * * * * * * * * * * * * * * * * * * 1. row * * * * * * * * * * * * * * * * * * * * * * * * * * * Id : 1 User : root Host : localhost db : test Command : Query Time : 7 State : Queried about 1576008 rows , Updated about 197000 rows Info : update foo set a = 9 where a = 8 Here is an example of what “show processlist” … Continued
