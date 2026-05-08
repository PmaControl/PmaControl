---
title: 'Hot Column Addition and Deletion Part II: How it works'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/hot-column-addition-and-deletion-part-ii-how-it-works/
  post_id: 9555
source_author:
  name: Martin.FarachColton
  slug: martin-farachcolton
  url: https://www.percona.com/blog/author/martin-farachcolton/
  website: ''
published_at: '2011-04-07T16:31:24'
published_at_gmt: '2011-04-07T16:31:24'
modified_at: '2026-04-28T22:39:23'
modified_at_gmt: '2026-04-28T22:39:23'
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
- alter table
- hot column addition
- InnoDB
- MySQL
- TokuDB
tag_slugs:
- alter-table
- hot-column-addition
- innodb
- mysql
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Hot Column Addition and Deletion Part II: How it works

Source: [Percona Blog](https://www.percona.com/blog/hot-column-addition-and-deletion-part-ii-how-it-works/)

Auteur source: [Martin.FarachColton](https://www.percona.com/blog/author/martin-farachcolton/)

Publication: 2011-04-07T16:31:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Hot Column Addition and Deletion (HCAD) In the previous HCAD post, I described HCAD and showed that it can reduce the downtime of column addition (or deletion) from 18 hours to 3 seconds. In fact, the downtime of InnoDB is proportional to the size of the database, whereas the downtime for TokuDB 5.0 depends on … Continued

## Structure detectee

- H3: Hot Column Addition and Deletion (HCAD)
- H3: Under the hood
- H3: Trying it out
- H3: Learning more
