---
title: Problems with Multiple XA Storage Engines in MySQL 5.6
source:
  name: Percona Blog
  url: https://www.percona.com/blog/problems-with-multiple-xa-storage-engines-in-mysql-5-6/
  post_id: 9816
source_author:
  name: Rich.Prohaska
  slug: rich-prohaska
  url: https://www.percona.com/blog/author/rich-prohaska/
  website: ''
published_at: '2013-10-23T19:49:17'
published_at_gmt: '2013-10-23T19:49:17'
modified_at: '2026-03-25T18:27:09'
modified_at_gmt: '2026-03-25T18:27:09'
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
- MySQL
- Storage Engine
- TokuDB
tag_slugs:
- mysql
- storage-engine
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Problems with Multiple XA Storage Engines in MySQL 5.6

Source: [Percona Blog](https://www.percona.com/blog/problems-with-multiple-xa-storage-engines-in-mysql-5-6/)

Auteur source: [Rich.Prohaska](https://www.percona.com/blog/author/rich-prohaska/)

Publication: 2013-10-23T19:49:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While integrating TokuDB into MySQL 5.6, we found that MySQL 5.6 does not support more than one XA storage engine. For example, there is an assert in the ha_recover function that fires when the total number of XA storage engines is greater than one. After disabling this assert, we found lots of bugs in the … Continued
