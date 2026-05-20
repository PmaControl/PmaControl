---
title: max_allowed_packet and binary log corruption in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/max_allowed_packet-and-binary-log-corruption-in-mysql/
  post_id: 8122
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2014-05-14T08:00:59'
published_at_gmt: '2014-05-14T08:00:59'
modified_at: '2026-04-28T22:04:50'
modified_at_gmt: '2026-04-28T22:04:50'
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
- binary log corruption
- got fatal error 1236 from master
- max_allowed_packet
- Miguel Angel Nieto
- slave_max_allowed_packet
tag_slugs:
- binary-log-corruption
- got-fatal-error-1236-from-master
- max_allowed_packet
- miguel-angel-nieto
- slave_max_allowed_packet
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# max_allowed_packet and binary log corruption in MySQL

Source: [Percona Blog](https://www.percona.com/blog/max_allowed_packet-and-binary-log-corruption-in-mysql/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2014-05-14T08:00:59

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Replication Constraint The combination of max_allowed_packet variable and replication in MySQL is a common source of headaches. In a nutshell, max_allowed_packet is the maximum size of a MySQL network protocol packet that the server can create or read. It has a default value of 1MB (<= 5.6.5) or 4MB (>= 5.6.6) and a maximum size … Continued

## Structure detectee

- H2: Replication Constraint
- H2: Binary Log Corruption Example

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
