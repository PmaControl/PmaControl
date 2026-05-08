---
title: Recovery deleted ibdata1
source:
  name: Percona Blog
  url: https://www.percona.com/blog/recovery-deleted-ibdata1/
  post_id: 3712
source_author:
  name: Aleksandr Kuzminsky
  slug: akuzminsky
  url: https://www.percona.com/blog/author/akuzminsky/
  website: ''
published_at: '2012-08-10T10:04:15'
published_at_gmt: '2012-08-10T10:04:15'
modified_at: '2026-05-04T21:47:50'
modified_at_gmt: '2026-05-04T21:47:50'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- innodb recovery
tag_slugs:
- innodb-recovery
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Recovery deleted ibdata1

Source: [Percona Blog](https://www.percona.com/blog/recovery-deleted-ibdata1/)

Auteur source: [Aleksandr Kuzminsky](https://www.percona.com/blog/author/akuzminsky/)

Publication: 2012-08-10T10:04:15

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I had a case when a customer deleted the InnoDB main table space – ibdata1 – and redo logs – ib_logfile*. MySQL keeps InnoDB files open all the time. The following recovery technique is based on this fact and it allowed to salvage the database. Actually, the files were deleted long time ago – … Continued

## Structure detectee

- H2: Conclusions

## Auteur source

Aleksandr is a consultant and data recovery specialist. He is a former Percona employee.
