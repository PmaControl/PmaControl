---
title: Statement based replication with Stored Functions, Triggers and Events
source:
  name: Percona Blog
  url: https://www.percona.com/blog/statement-based-replication-with-stored-functions-triggers-and-events/
  post_id: 3258
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2011-12-16T16:08:58'
published_at_gmt: '2011-12-16T16:08:58'
modified_at: '2026-03-23T22:11:46'
modified_at_gmt: '2026-03-23T22:11:46'
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

# Statement based replication with Stored Functions, Triggers and Events

Source: [Percona Blog](https://www.percona.com/blog/statement-based-replication-with-stored-functions-triggers-and-events/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2011-12-16T16:08:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Statement based replication writes the queries that modify data in the Binary Log to replicate them on the slave or to use it as a PITR recovery. Here we will see what is the behavior of the MySQL when it needs to log “not usual” queries like Events, Functions, Stored Procedures, Local Variables, etc. We’ll … Continued

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
