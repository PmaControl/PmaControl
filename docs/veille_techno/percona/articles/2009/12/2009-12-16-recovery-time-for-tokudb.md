---
title: Recovery Time for TokuDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/recovery-time-for-tokudb/
  post_id: 9478
source_author:
  name: Tokutek
  slug: tokutek
  url: https://www.percona.com/blog/author/tokutek/
  website: ''
published_at: '2009-12-16T16:56:53'
published_at_gmt: '2009-12-16T16:56:53'
modified_at: '2026-04-28T22:25:07'
modified_at_gmt: '2026-04-28T22:25:07'
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
- InnoDB
- MySQL
- TokuDB
tag_slugs:
- innodb
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Recovery Time for TokuDB

Source: [Percona Blog](https://www.percona.com/blog/recovery-time-for-tokudb/)

Auteur source: [Tokutek](https://www.percona.com/blog/author/tokutek/)

Publication: 2009-12-16T16:56:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Last week Tokutek released version 3.0.0 of TokuDB, adding ACID transactions to its list of features. This post discusses an experiment we ran to measure recovery time following a system crash. In summary, while actively inserting records into a MySQL database using iiBench, we compared the time to recover from a power-cord pull for both … Continued

## Structure detectee

- H3: The experiment
