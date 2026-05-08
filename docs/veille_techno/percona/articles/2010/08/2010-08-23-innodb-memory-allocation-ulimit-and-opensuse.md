---
title: InnoDB memory allocation, ulimit, and OpenSUSE
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-memory-allocation-ulimit-and-opensuse/
  post_id: 2432
source_author:
  name: Sasha Pachev
  slug: sasha
  url: https://www.percona.com/blog/author/sasha/
  website: http://www.percona.com/
published_at: '2010-08-23T20:55:52'
published_at_gmt: '2010-08-23T20:55:52'
modified_at: '2026-05-04T21:28:26'
modified_at_gmt: '2026-05-04T21:28:26'
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

# InnoDB memory allocation, ulimit, and OpenSUSE

Source: [Percona Blog](https://www.percona.com/blog/innodb-memory-allocation-ulimit-and-opensuse/)

Auteur source: [Sasha Pachev](https://www.percona.com/blog/author/sasha/)

Publication: 2010-08-23T20:55:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently encountered an interesting case. A customer reported that mysqld crashed on start on OpenSUSE 11.2 kernel 2.6.31.12-0.2-desktop x86_64 Â with 96 GB RAM when the innodb_buffer_pool_size was set to anything more than 62 GB. I decided to try it with 76 GB. The error message was an assert due to a failed malloc() … Continued

## Auteur source

Sasha is a former Percona employee. Sasha formerly worked at MySQL, where he was the original creator of MySQL's native replication. He is the author of two books: MySQL Enterprise Solutions and Understanding MySQL Internals.
