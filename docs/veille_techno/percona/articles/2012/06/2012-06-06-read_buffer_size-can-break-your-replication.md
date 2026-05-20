---
title: read_buffer_size can break your replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/read_buffer_size-can-break-your-replication/
  post_id: 3622
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2012-06-06T21:00:45'
published_at_gmt: '2012-06-06T21:00:45'
modified_at: '2026-03-23T22:21:57'
modified_at_gmt: '2026-03-23T22:21:57'
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

# read_buffer_size can break your replication

Source: [Percona Blog](https://www.percona.com/blog/read_buffer_size-can-break-your-replication/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2012-06-06T21:00:45

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are some variables that can affect the replication behavior and sometimes cause some big troubles. In this post I’m going to talk about read_buffer_size and how this variable together with max_allowed_packet can break your replication. The setup is a master-master replication with the following values: max_allowed_packet = 32Mread_buffer_size = 100M To break the replication … Continued

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
