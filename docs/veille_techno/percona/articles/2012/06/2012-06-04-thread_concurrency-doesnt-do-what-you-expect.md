---
title: thread_concurrency doesn’t do what you expect
source:
  name: Percona Blog
  url: https://www.percona.com/blog/thread_concurrency-doesnt-do-what-you-expect/
  post_id: 3621
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2012-06-04T20:43:24'
published_at_gmt: '2012-06-04T20:43:24'
modified_at: '2026-03-23T22:21:54'
modified_at_gmt: '2026-03-23T22:21:54'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# thread_concurrency doesn’t do what you expect

Source: [Percona Blog](https://www.percona.com/blog/thread_concurrency-doesnt-do-what-you-expect/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2012-06-04T20:43:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Over the last months I’ve seen lots of customers trying to tune the thread concurrency inside MySQL with the variable thread_concurrency. Our advice is: stop wasting your time, it does nothing on GNU/Linux 🙂 Some of the biggest GNU/Linux distributions includes the variable thread_concurrency in their my.cnf file by default. One example is Debian and … Continued

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
