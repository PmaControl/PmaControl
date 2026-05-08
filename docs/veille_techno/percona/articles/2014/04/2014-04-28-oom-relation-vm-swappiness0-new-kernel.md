---
title: OOM relation to vm.swappiness=0 in new kernel
source:
  name: Percona Blog
  url: https://www.percona.com/blog/oom-relation-vm-swappiness0-new-kernel/
  post_id: 7966
source_author:
  name: Ovais Tariq
  slug: ovaistariq
  url: https://www.percona.com/blog/author/ovaistariq/
  website: http://www.percona.com/blog/
published_at: '2014-04-28T14:52:36'
published_at_gmt: '2014-04-28T14:52:36'
modified_at: '2026-05-04T20:55:58'
modified_at_gmt: '2026-05-04T20:55:58'
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
- kernel
- memory
- oom
- swap
- swappiness
tag_slugs:
- kernel
- memory
- oom
- swap
- swappiness
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# OOM relation to vm.swappiness=0 in new kernel

Source: [Percona Blog](https://www.percona.com/blog/oom-relation-vm-swappiness0-new-kernel/)

Auteur source: [Ovais Tariq](https://www.percona.com/blog/author/ovaistariq/)

Publication: 2014-04-28T14:52:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I have recently been involved in diagnosing the reasons behind OOM invocation that would kill the MySQL server process. Of course these servers were primarily running MySQL. As such the MySQL server process was the one with the largest amount of memory allocated. But the strange thing was that in all the cases, there was … Continued
