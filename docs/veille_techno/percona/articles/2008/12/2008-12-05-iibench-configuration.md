---
title: iiBench configuration
source:
  name: Percona Blog
  url: https://www.percona.com/blog/iibench-configuration/
  post_id: 9450
source_author:
  name: kuszmaul
  slug: kuszmaul
  url: https://www.percona.com/blog/author/kuszmaul/
  website: ''
published_at: '2008-12-05T20:48:00'
published_at_gmt: '2008-12-05T20:48:00'
modified_at: '2026-05-04T19:44:08'
modified_at_gmt: '2026-05-04T19:44:08'
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

# iiBench configuration

Source: [Percona Blog](https://www.percona.com/blog/iibench-configuration/)

Auteur source: [kuszmaul](https://www.percona.com/blog/author/kuszmaul/)

Publication: 2008-12-05T20:48:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A tip of the hat to Mark Callaghan, who suggested I post our my.cnf settings for iiBench. Instead of fiddling around with the configuration file, we adjusted everything on the command line. Here’s the relevant script from iiBench/scripts/start_mysql.sh:
