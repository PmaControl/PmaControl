---
title: Different flavors of InnoDB flushing
source:
  name: Percona Blog
  url: https://www.percona.com/blog/different-flavors-of-innodb-flushing/
  post_id: 2641
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2011-01-14T05:32:47'
published_at_gmt: '2011-01-14T05:32:47'
modified_at: '2026-04-28T21:22:46'
modified_at_gmt: '2026-04-28T21:22:46'
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
- InnoDB
- Percona Server for MySQL
tag_slugs:
- innodb
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/13G.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Different flavors of InnoDB flushing

Source: [Percona Blog](https://www.percona.com/blog/different-flavors-of-innodb-flushing/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2011-01-14T05:32:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my recent benchmarks, such as this one about the Virident TachIon card, I used different values for innodb_buffer_pool_size, like 13GB, 52GB, and 144GB, for testing the tpcc-mysql database with size 100G. This was needed in order to test different memory/dataset size ratios. But why is it important, and how does it affect howÂ InnoDB … Continued

## Images et graphiques reperes

- featured / image: [Different flavors of InnoDB flushing](https://www.percona.com/wp-content/uploads/2026/03/13G.png)
- content / image: [52G.png](https://www.percona.com/wp-content/uploads/2026/03/52G.png)
- content / image: [13G_ios.png](https://www.percona.com/wp-content/uploads/2026/03/13G_ios.png)
- content / image: [52G_ios.png](https://www.percona.com/wp-content/uploads/2026/03/52G_ios.png)
- content / image: [dirty.png](https://www.percona.com/wp-content/uploads/2026/03/dirty.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
