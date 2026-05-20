---
title: How to recover table structure from InnoDB dictionary
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-recover-table-structure-from-innodb-dictionary/
  post_id: 6859
source_author:
  name: Aleksandr Kuzminsky
  slug: akuzminsky
  url: https://www.percona.com/blog/author/akuzminsky/
  website: ''
published_at: '2013-04-22T10:00:30'
published_at_gmt: '2013-04-22T10:00:30'
modified_at: '2026-05-04T22:03:19'
modified_at_gmt: '2026-05-04T22:03:19'
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
- InnoDB
- kuzminsky
- Recovery
- SYS_COLUMNS
- SYS_FIELDS
- SYS_INDEXES
- SYS_TABLES
tag_slugs:
- innodb
- kuzminsky
- recovery
- sys_columns
- sys_fields
- sys_indexes
- sys_tables
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to recover table structure from InnoDB dictionary

Source: [Percona Blog](https://www.percona.com/blog/how-to-recover-table-structure-from-innodb-dictionary/)

Auteur source: [Aleksandr Kuzminsky](https://www.percona.com/blog/author/akuzminsky/)

Publication: 2013-04-22T10:00:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

To recover a dropped or corrupt table with Percona Data Recovery Tool for InnoDB you need two things: media with records(ibdata1, *.ibd, disk image, etc.) and a table structure. Indeed, there is no information about the table structure in an InnoDB page. Normally we either recover the structure from .frm files or take it from … Continued

## Auteur source

Aleksandr is a consultant and data recovery specialist. He is a former Percona employee.
