---
title: Recover BLOB fields
source:
  name: Percona Blog
  url: https://www.percona.com/blog/recover-blob-fields/
  post_id: 2395
source_author:
  name: Aleksandr Kuzminsky
  slug: akuzminsky
  url: https://www.percona.com/blog/author/akuzminsky/
  website: ''
published_at: '2010-07-01T18:00:50'
published_at_gmt: '2010-07-01T18:00:50'
modified_at: '2026-04-28T21:15:07'
modified_at_gmt: '2026-04-28T21:15:07'
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
- Backups
- InnoDB
- Recovery
tag_slugs:
- backups
- innodb
- recovery
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/record-format.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Recover BLOB fields

Source: [Percona Blog](https://www.percona.com/blog/recover-blob-fields/)

Auteur source: [Aleksandr Kuzminsky](https://www.percona.com/blog/author/akuzminsky/)

Publication: 2010-07-01T18:00:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

For a long time long types like BLOB, TEXT were not supported by Percona InnoDB Recovery Tool. The reason consists in a special way InnoDB stores BLOBs. An InnoDB table is stored in a clustered index called PRIMARY. It must exist even if a user hasn’t defined the primary index. The PRIMARY index pages are … Continued

## Images et graphiques reperes

- featured / image: [Recover BLOB fields](https://www.percona.com/wp-content/uploads/2026/03/record-format.png)
- content / image: [record-format1.png](https://www.percona.com/wp-content/uploads/2026/03/record-format1.png)

## Auteur source

Aleksandr is a consultant and data recovery specialist. He is a former Percona employee.
