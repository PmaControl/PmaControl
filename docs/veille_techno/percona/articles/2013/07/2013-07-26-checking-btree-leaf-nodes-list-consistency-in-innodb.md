---
title: Checking B+tree leaf nodes list consistency in InnoDB
source:
  name: Percona Blog
  url: https://www.percona.com/blog/checking-btree-leaf-nodes-list-consistency-in-innodb/
  post_id: 7224
source_author:
  name: Aleksandr Kuzminsky
  slug: akuzminsky
  url: https://www.percona.com/blog/author/akuzminsky/
  website: ''
published_at: '2013-07-26T13:40:41'
published_at_gmt: '2013-07-26T13:40:41'
modified_at: '2026-04-28T21:53:58'
modified_at_gmt: '2026-04-28T21:53:58'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- Aleksandr Kuzminsky
- constraints_parser
- index_chk
- innodb recovery
- kuzminsky
- page_parser
- percona-data-recovery-tool-for-innodb
- Recovery
- recovery tool
tag_slugs:
- aleksandr-kuzminsky
- constraints_parser
- index_chk
- innodb-recovery
- kuzminsky
- page_parser
- percona-data-recovery-tool-for-innodb
- recovery
- recovery-tool
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/slide-6-728.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Checking B+tree leaf nodes list consistency in InnoDB

Source: [Percona Blog](https://www.percona.com/blog/checking-btree-leaf-nodes-list-consistency-in-innodb/)

Auteur source: [Aleksandr Kuzminsky](https://www.percona.com/blog/author/akuzminsky/)

Publication: 2013-07-26T13:40:41

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If we have InnoDB pages there are two ways to learn how many records they contain: PAGE_N_RECS field in the page header Count records while walking over the list of records from infimum to supremum In some previous revision of the recovery tool a short summary was added to a dump which … Continued

## Images et graphiques reperes

- featured / image: [Checking B+tree leaf nodes list consistency in InnoDB](https://www.percona.com/wp-content/uploads/2026/03/slide-6-728.jpg)

## Auteur source

Aleksandr is a consultant and data recovery specialist. He is a former Percona employee.
