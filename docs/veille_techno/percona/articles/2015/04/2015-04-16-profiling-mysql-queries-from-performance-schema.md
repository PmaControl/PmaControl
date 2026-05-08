---
title: Profiling MySQL queries from Performance Schema
source:
  name: Percona Blog
  url: https://www.percona.com/blog/profiling-mysql-queries-from-performance-schema/
  post_id: 9195
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2015-04-16T17:49:13'
published_at_gmt: '2015-04-16T17:49:13'
modified_at: '2026-04-28T22:18:59'
modified_at_gmt: '2026-04-28T22:18:59'
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
tags:
- Jervin Real
- MySQL queries
- Percona Server for MySQL
- Performance Schema
- Primary
tag_slugs:
- jervin-real
- mysql-queries
- percona-server
- performance-schema
- primary
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Profiling MySQL queries from Performance Schema

Source: [Percona Blog](https://www.percona.com/blog/profiling-mysql-queries-from-performance-schema/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2015-04-16T17:49:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When optimizing queries and investigating performance issues, MySQL comes with built in support for profiling queries aka SET profiling = 1 ; . This is already awesome and simple to use, but why the PERFORMANCE_SCHEMA alternative? Because profiling will be removed soon (already deprecated on MySQL 5.6 ad 5.7); the built-in profiling capability can only be enabled per session. … Continued

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
