---
title: 'BEWARE: Increasing fc_limit can affect SELECT latency'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/beware-increasing-fc_limit-can-affect-select-latency/
  post_id: 16702
source_author:
  name: Krunal Bauskar
  slug: krunal-bauskar
  url: https://www.percona.com/blog/author/krunal-bauskar/
  website: ''
published_at: '2017-04-23T02:22:36'
published_at_gmt: '2017-04-23T02:22:36'
modified_at: '2026-05-05T19:46:35'
modified_at_gmt: '2026-05-05T19:46:35'
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
- data freshness
- fc_limit
- select latency
- wsrep_sync_wait
tag_slugs:
- data-freshness
- fc_limit
- select-latency
- wsrep_sync_wait
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/SELECT-Latency.jpg
image_count: 1
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# BEWARE: Increasing fc_limit can affect SELECT latency

Source: [Percona Blog](https://www.percona.com/blog/beware-increasing-fc_limit-can-affect-select-latency/)

Auteur source: [Krunal Bauskar](https://www.percona.com/blog/author/krunal-bauskar/)

Publication: 2017-04-23T02:22:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how increasing the fc_limit can affect SELECT latency. Introduction Recent Percona XtraDB Cluster optimizations have exposed fc_limit contention. It was always there, but was never exposed as the Commit Monitor contention was more significant. As it happens with any optimization, once we solve the bigger contention issues, smaller … Continued

## Structure detectee

- H3: Introduction
- H3: What is FC_LIMIT?
- H3: Increasing fc_limit
- H3: Conclusion

## Images et graphiques reperes

- featured / graph_or_chart: [BEWARE: Increasing fc_limit can affect SELECT latency](https://www.percona.com/wp-content/uploads/2026/03/SELECT-Latency.jpg)

## Auteur source

Krunal is PXC lead at Percona. He is responsible for day-day PXC development, what goes into PXC, bug fixes, releases, etc.. Before joining Percona he use to work as part of InnoDB team at MySQL/Oracle. He authored most of the temporary table revamp work, undo log truncate, atomic truncate and lot of other features. In past he was associated with Yahoo! Labs researching on bigdata problems and database startup which is now part of Teradata. His interest mainly includes data-management at any scale and has been practicing it for more than decade now.
