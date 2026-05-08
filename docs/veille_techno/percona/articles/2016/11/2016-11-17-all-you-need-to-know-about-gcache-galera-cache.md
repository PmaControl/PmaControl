---
title: All You Need to Know About GCache (Galera-Cache)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/all-you-need-to-know-about-gcache-galera-cache/
  post_id: 15711
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2016-11-17T01:21:40'
published_at_gmt: '2016-11-17T01:21:40'
modified_at: '2026-03-20T21:12:06'
modified_at_gmt: '2026-03-20T21:12:06'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- galera.cache
- Gcache
- gcache.page_size
- gcache.size
tag_slugs:
- galera-cache
- gcache
- gcache-page_size
- gcache-size
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/GCache.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# All You Need to Know About GCache (Galera-Cache)

Source: [Percona Blog](https://www.percona.com/blog/all-you-need-to-know-about-gcache-galera-cache/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2016-11-17T01:21:40

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog discusses some important aspects of GCache. Why do we need GCache? Percona XtraDB Cluster is a multi-master topology, where a transaction executed on one node is replicated on another node(s) of the cluster. This transaction is then copied over from the group channel to Galera-Cache followed by apply action. The cache can be discarded immediately … Continued

## Structure detectee

- H2: Why do we need GCache?
- H2: How is GCache managed?
- H2: Where are GCache files located?
- H2: What if one of the node is DESYNCED and PAUSED?

## Images et graphiques reperes

- featured / image: [All You Need to Know About GCache (Galera-Cache)](https://www.percona.com/wp-content/uploads/2026/03/GCache.jpg)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
