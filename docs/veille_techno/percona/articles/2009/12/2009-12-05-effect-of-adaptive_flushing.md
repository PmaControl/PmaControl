---
title: Effect of adaptive_flushing
source:
  name: Percona Blog
  url: https://www.percona.com/blog/effect-of-adaptive_flushing/
  post_id: 2141
source_author:
  name: Devananda van der Veen
  slug: deva
  url: https://www.percona.com/blog/author/deva/
  website: http://www.percona.com/
published_at: '2009-12-05T00:36:48'
published_at_gmt: '2009-12-05T00:36:48'
modified_at: '2026-05-04T21:26:39'
modified_at_gmt: '2026-05-04T21:26:39'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- MySQL
category_slugs:
- benchmarks
- mysql
tags:
- benchmark
- InnoDB
- MySQL
- Tips
- Tuning
tag_slugs:
- benchmark
- innodb
- mysql
- tips
- tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/db01-buffer-pool-1d.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Effect of adaptive_flushing

Source: [Percona Blog](https://www.percona.com/blog/effect-of-adaptive_flushing/)

Auteur source: [Devananda van der Veen](https://www.percona.com/blog/author/deva/)

Publication: 2009-12-05T00:36:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently had the chance to witness the effects of innodb_adaptive_flushing on the performance of InnoDB Plugin 1.0.5 in the wild, which Yasufumi wrote about previously here and here. The server in question was Solaris 10 with 8 disk RAID10 and 2 32GB SSDs used for ZIL and L2ARC, 72G RAM and 40G buffer pool. … Continued

## Images et graphiques reperes

- featured / image: [Effect of adaptive_flushing](https://www.percona.com/wp-content/uploads/2026/03/db01-buffer-pool-1d.png)
- content / image: [db01-checkpt-age-1d](https://www.percona.com/wp-content/uploads/2026/03/db01-checkpt-age-1d1.png)
- content / image: [db01-innodb-trx-1d](https://www.percona.com/wp-content/uploads/2026/03/db01-innodb-trx-1d.png)
- content / image: [db01-buffer-pool-act-1d](https://www.percona.com/wp-content/uploads/2026/03/db01-buffer-pool-act-1d1.png)
- content / image: [db01-zfs-iostat-1d](https://www.percona.com/wp-content/uploads/2026/03/db01-zfs-iostat-1d.png)

## Auteur source

Deva is a former Percona employee. Deva joined Percona in July 2009 as a Principal Consultant, with a focus on scalability and performance tuning. Deva has a background in physics and computer science from UC Santa Barbara. Prior to joining Percona, he was the DBA at HydraNetwork.
