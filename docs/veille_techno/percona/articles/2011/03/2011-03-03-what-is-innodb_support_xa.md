---
title: What is innodb_support_xa?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/what-is-innodb_support_xa/
  post_id: 2731
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2011-03-03T00:28:39'
published_at_gmt: '2011-03-03T00:28:39'
modified_at: '2026-03-23T21:53:05'
modified_at_gmt: '2026-03-23T21:53:05'
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

# What is innodb_support_xa?

Source: [Percona Blog](https://www.percona.com/blog/what-is-innodb_support_xa/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2011-03-03T00:28:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A common misunderstanding about innodb_support_xa is that it enables user-initiated XA transactions, that is, transactions that are prepared and then committed on multiple systems, with an external transaction coordinator. This is actually not precisely what this option is for. It enables two-phase commit in InnoDB (prepare, then commit). This is necessary not only for user-initiated … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
