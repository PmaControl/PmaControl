---
title: Why delayed flushing can result in less work
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-delayed-flushing-can-result-in-less-work/
  post_id: 2162
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2009-12-10T05:44:37'
published_at_gmt: '2009-12-10T05:44:37'
modified_at: '2026-03-23T21:37:04'
modified_at_gmt: '2026-03-23T21:37:04'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags:
- InnoDB
- InnoDB Adaptive Flushing
tag_slugs:
- innodb
- innodb-adaptive-flushing
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why delayed flushing can result in less work

Source: [Percona Blog](https://www.percona.com/blog/why-delayed-flushing-can-result-in-less-work/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2009-12-10T05:44:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I can think of at least two major reasons why systems delay flushing changes to durable storage: 1. So they can do the work when it’s more convenient.2. So they can do less work in total. Let’s look at how the second property can be true.

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
