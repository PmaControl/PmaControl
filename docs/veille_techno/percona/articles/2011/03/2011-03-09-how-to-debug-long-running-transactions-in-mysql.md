---
title: How to debug long-running transactions in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-debug-long-running-transactions-in-mysql/
  post_id: 2736
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2011-03-09T02:51:57'
published_at_gmt: '2011-03-09T02:51:57'
modified_at: '2026-05-04T20:50:01'
modified_at_gmt: '2026-05-04T20:50:01'
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
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to debug long-running transactions in MySQL

Source: [Percona Blog](https://www.percona.com/blog/how-to-debug-long-running-transactions-in-mysql/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2011-03-09T02:51:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Among the many things that can cause a “server stall” is a long-running transaction. If a transaction remains open for a very long time without committing, and has modified data, then other transactions could block and fail with a lock wait timeout. The problem is, it can be very difficult to find the offending code … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
