---
title: Avoiding auto-increment holes on InnoDB with INSERT IGNORE
source:
  name: Percona Blog
  url: https://www.percona.com/blog/avoiding-auto-increment-holes-on-innodb-with-insert-ignore/
  post_id: 3194
source_author:
  name: Miguel Angel Nieto
  slug: miguelangelnieto
  url: https://www.percona.com/blog/author/miguelangelnieto/
  website: http://www.percona.com
published_at: '2011-11-29T14:23:36'
published_at_gmt: '2011-11-29T14:23:36'
modified_at: '2026-03-23T22:08:55'
modified_at_gmt: '2026-03-23T22:08:55'
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

# Avoiding auto-increment holes on InnoDB with INSERT IGNORE

Source: [Percona Blog](https://www.percona.com/blog/avoiding-auto-increment-holes-on-innodb-with-insert-ignore/)

Auteur source: [Miguel Angel Nieto](https://www.percona.com/blog/author/miguelangelnieto/)

Publication: 2011-11-29T14:23:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Are you using InnoDB tables on MySQL version 5.1.22 or newer? If so, you probably have gaps in your auto-increment columns. A simple INSERT IGNORE query creates gaps for every ignored insert, but this is undocumented behavior. This documentation bug is already submitted. Firstly, we will start with a simple question. Why do we have … Continued

## Structure detectee

- H2: How can I solve this problem for INSERT IGNORE?

## Auteur source

Miguel joined Percona in October 2011. He has worked as a System Administrator for a Free Software consultant and in the supporting area of the biggest hosting company in Spain. His current focus is improving MySQL and helping the community of Free Software to grow.
