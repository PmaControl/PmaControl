---
title: New variable slave_max_allowed_packet for slave servers
source:
  name: Percona Blog
  url: https://www.percona.com/blog/new-variable-slave_max_allowed_packet-for-slave-servers/
  post_id: 3701
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2012-07-24T14:13:43'
published_at_gmt: '2012-07-24T14:13:43'
modified_at: '2026-03-23T22:24:12'
modified_at_gmt: '2026-03-23T22:24:12'
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

# New variable slave_max_allowed_packet for slave servers

Source: [Percona Blog](https://www.percona.com/blog/new-variable-slave_max_allowed_packet-for-slave-servers/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2012-07-24T14:13:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One month ago I wrote about how a big read_buffer_size could break the replication. The bug is not solved but now there is an official workaround to ease this problem using a new configuration variable: slave_max_allowed_packet This new variable will be available in 5.1.64, 5.5.26, and 5.6.6 and can establish a different limit on the … Continued

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
