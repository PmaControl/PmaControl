---
title: Logging Foreign Key errors
source:
  name: Percona Blog
  url: https://www.percona.com/blog/logging-foreign-key-errors/
  post_id: 3812
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2012-09-27T14:19:09'
published_at_gmt: '2012-09-27T14:19:09'
modified_at: '2026-04-28T21:40:21'
modified_at_gmt: '2026-04-28T21:40:21'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
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

# Logging Foreign Key errors

Source: [Percona Blog](https://www.percona.com/blog/logging-foreign-key-errors/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2012-09-27T14:19:09

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In the last blog post I wrote about how to log deadlock errors using Percona Toolkit. Foreign key errors have the same problems. InnoDB only logs the last error in the output of SHOW ENGINE INNODB STATUS, so we need another similar tool in order to have historical data. pt-fk-error-logger This is a tool very … Continued

## Structure detectee

- H3: pt-fk-error-logger

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
