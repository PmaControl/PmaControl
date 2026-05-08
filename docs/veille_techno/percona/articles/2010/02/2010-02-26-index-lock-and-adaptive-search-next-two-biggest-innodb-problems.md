---
title: Index lock and adaptive search – next two biggest InnoDB problems
source:
  name: Percona Blog
  url: https://www.percona.com/blog/index-lock-and-adaptive-search-next-two-biggest-innodb-problems/
  post_id: 2237
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2010-02-26T00:33:14'
published_at_gmt: '2010-02-26T00:33:14'
modified_at: '2026-03-23T21:38:51'
modified_at_gmt: '2026-03-23T21:38:51'
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
- InnoDB
tag_slugs:
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Index lock and adaptive search – next two biggest InnoDB problems

Source: [Percona Blog](https://www.percona.com/blog/index-lock-and-adaptive-search-next-two-biggest-innodb-problems/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2010-02-26T00:33:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Running many benchmarks on fast storage (FusionIO, SSDs) and multi-cores CPUs system I constantly face two contention problems. So I suspect it’s going to be next biggest issues to make InnoDB scaling on high-end system. This is also reason why in benchmarks I posted previously CPU usage is only about 50%, leaving other 50% in … Continued

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
