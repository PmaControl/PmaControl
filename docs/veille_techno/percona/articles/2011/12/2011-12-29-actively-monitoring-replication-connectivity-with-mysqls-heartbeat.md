---
title: Actively monitoring replication connectivity with MySQL’s heartbeat
source:
  name: Percona Blog
  url: https://www.percona.com/blog/actively-monitoring-replication-connectivity-with-mysqls-heartbeat/
  post_id: 3278
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2011-12-29T18:37:02'
published_at_gmt: '2011-12-29T18:37:02'
modified_at: '2026-03-23T22:12:38'
modified_at_gmt: '2026-03-23T22:12:38'
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

# Actively monitoring replication connectivity with MySQL’s heartbeat

Source: [Percona Blog](https://www.percona.com/blog/actively-monitoring-replication-connectivity-with-mysqls-heartbeat/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2011-12-29T18:37:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Until MySQL 5.5 the only variable used to identify a network connectivity problem between Master and Slave was slave-net-timeout. This variable specifies the number of seconds to wait for more Binary Logs events from the master before abort the connection and establish it again. With a default value of 3600 this has been a historically … Continued

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
