---
title: mydumper [less] locking
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mydumper-less-locking/
  post_id: 8246
source_author:
  name: Max Bubenick
  slug: max-bubenick
  url: https://www.percona.com/blog/author/max-bubenick/
  website: ''
published_at: '2014-06-13T14:15:21'
published_at_gmt: '2014-06-13T14:15:21'
modified_at: '2026-03-25T17:33:55'
modified_at_gmt: '2026-03-25T17:33:55'
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
tags:
- backup tools
- Max Bubenick
- mydumper
tag_slugs:
- backup-tools
- max-bubenick
- mydumper
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# mydumper [less] locking

Source: [Percona Blog](https://www.percona.com/blog/mydumper-less-locking/)

Auteur source: [Max Bubenick](https://www.percona.com/blog/author/max-bubenick/)

Publication: 2014-06-13T14:15:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post I would like to review how my dumper for MySQL works from the point of view of locks. Since 0.6 serie we have different options, so I will try to explain how they work As you may know mydumper is multithreaded and this adds a lot of complexity compared with other logical … Continued

## Auteur source

Max Bubenick has been working with MySQL for more than 10 years. Before joining Percona's Remote DBA team in 2013 he worked as lead DBA for one of the biggest social gaming companies at that time. Also he maintains mydumper.
