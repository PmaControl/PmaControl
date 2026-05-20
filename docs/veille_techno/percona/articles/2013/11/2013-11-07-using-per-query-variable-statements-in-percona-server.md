---
title: Using per-query variable statements in Percona Server
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-per-query-variable-statements-in-percona-server/
  post_id: 7538
source_author:
  name: Hrvoje Matijakovic
  slug: hrvojem
  url: https://www.percona.com/blog/author/hrvojem/
  website: ''
published_at: '2013-11-07T18:20:52'
published_at_gmt: '2013-11-07T18:20:52'
modified_at: '2026-03-25T17:14:15'
modified_at_gmt: '2026-03-25T17:14:15'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- per-query variable statements
- Percona Server for MySQL
tag_slugs:
- per-query-variable-statements
- percona-server
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using per-query variable statements in Percona Server

Source: [Percona Blog](https://www.percona.com/blog/using-per-query-variable-statements-in-percona-server/)

Auteur source: [Hrvoje Matijakovic](https://www.percona.com/blog/author/hrvojem/)

Publication: 2013-11-07T18:20:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona Server has implemented per-query variable statement support in version 5.6.14-62.0. This feature provides the ability to set variable values only for a certain query, after execution of which the previous values will be restored. Per-query variable values can be set up with the following command: MySQL mysql> SET STATEMENT <variable=value> FOR <statement>; 1 mysql > SET STATEMENT < variable = value > FOR < statement > ; Example: If we want to increase the sort_buffer_size value just … Continued

## Structure detectee

- H3: Using the per-query variable statements with Statement Timeout feature
