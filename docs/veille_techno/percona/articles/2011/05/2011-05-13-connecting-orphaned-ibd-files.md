---
title: Connecting orphaned .ibd files
source:
  name: Percona Blog
  url: https://www.percona.com/blog/connecting-orphaned-ibd-files/
  post_id: 2940
source_author:
  name: Aleksandr Kuzminsky
  slug: akuzminsky
  url: https://www.percona.com/blog/author/akuzminsky/
  website: ''
published_at: '2011-05-13T07:00:01'
published_at_gmt: '2011-05-13T07:00:01'
modified_at: '2026-05-04T21:33:45'
modified_at_gmt: '2026-05-04T21:33:45'
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

# Connecting orphaned .ibd files

Source: [Percona Blog](https://www.percona.com/blog/connecting-orphaned-ibd-files/)

Auteur source: [Aleksandr Kuzminsky](https://www.percona.com/blog/author/akuzminsky/)

Publication: 2011-05-13T07:00:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are two ways InnoDB can organize tablespaces. First is when all data, indexes and system buffers are stored in a single tablespace. This is typicaly one or several ibdata files. A well known innodb_file_per_table option brings the second one. Tables and system areas are split into different files. Usually system tablespace is located in … Continued

## Auteur source

Aleksandr is a consultant and data recovery specialist. He is a former Percona employee.
