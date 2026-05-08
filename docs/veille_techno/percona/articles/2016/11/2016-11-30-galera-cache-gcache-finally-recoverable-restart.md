---
title: Galera Cache (gcache) is finally recoverable on restart
source:
  name: Percona Blog
  url: https://www.percona.com/blog/galera-cache-gcache-finally-recoverable-restart/
  post_id: 15976
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2016-11-30T22:38:18'
published_at_gmt: '2016-11-30T22:38:18'
modified_at: '2026-05-05T19:42:56'
modified_at_gmt: '2026-05-05T19:42:56'
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
- Gcache
- gcache.recover
- persistent gcache
- revive gcache
tag_slugs:
- gcache
- gcache-recover
- persistent-gcache
- revive-gcache
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/gcache-2.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Galera Cache (gcache) is finally recoverable on restart

Source: [Percona Blog](https://www.percona.com/blog/galera-cache-gcache-finally-recoverable-restart/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2016-11-30T22:38:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post describes how to recover Galera Cache (or gcache) on restart. Recently Codership introduced (with Galera 3.19) a very important and long awaited feature. Now users can recover Galera cache on restart. Need If you gracefully shutdown cluster nodes one after another, with some lag time between nodes, then the last node to shutdown … Continued

## Structure detectee

- H3: Need
- H3: How does this help ?
- H3: gcache.recover in action
- H3: gcache revive doesn’t work if . . .
- H3: Summing it up

## Images et graphiques reperes

- featured / image: [Galera Cache (gcache) is finally recoverable on restart](https://www.percona.com/wp-content/uploads/2026/03/gcache-2.jpg)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
