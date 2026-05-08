---
title: InnoDB vs MyISAM vs Falcon benchmarks – part 1
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-vs-myisam-vs-falcon-benchmarks-part-1/
  post_id: 1335
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2007-01-08T08:58:37'
published_at_gmt: '2007-01-08T08:58:37'
modified_at: '2026-04-28T20:17:17'
modified_at_gmt: '2026-04-28T20:17:17'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
categories:
- Benchmarks
category_slugs:
- benchmarks
tags:
- GitHub
- InnoDB
- MyISAM
tag_slugs:
- github
- innodb
- myisam
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/7c27dc64-d150-470f-8c0f-bb604c0ee660.png
image_count: 14
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB vs MyISAM vs Falcon benchmarks – part 1

Source: [Percona Blog](https://www.percona.com/blog/innodb-vs-myisam-vs-falcon-benchmarks-part-1/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2007-01-08T08:58:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Several days ago MySQL AB made new storage engine Falcon available for wide auditory. We cannot miss this event and executed several benchmarks to see how Falcon performs in comparison to InnoDB and MyISAM. The second goal of benchmark was a popular myth that MyISAM is faster than InnoDB in reads, as InnoDB is transactional, … Continued

## Images et graphiques reperes

- featured / image: [InnoDB vs MyISAM vs Falcon benchmarks – part 1](https://www.percona.com/wp-content/uploads/2026/03/7c27dc64-d150-470f-8c0f-bb604c0ee660.png)
- content / image: [READ_PK_POINT](https://www.percona.com/blog/files/benchmarks/img/READ_PK_POINT.png)
- content / image: [READ_KEY_POINT](https://www.percona.com/blog/files/benchmarks/img/READ_KEY_POINT.png)
- content / image: [READ_KEY_POINT_LIMIT](https://www.percona.com/blog/files/benchmarks/img/READ_KEY_POINT_LIMIT.png)
- content / image: [READ_KEY_POINT_NO_DATA](https://www.percona.com/blog/files/benchmarks/img/READ_KEY_POINT_NO_DATA.png)
- content / image: [READ_KEY_POINT_NO_DATA_LIMIT](https://www.percona.com/blog/files/benchmarks/img/READ_KEY_POINT_NO_DATA_LIMIT.png)
- content / image: [READ_PK_POINT_INDEX](https://www.percona.com/blog/files/benchmarks/img/READ_PK_POINT_INDEX.png)
- content / image: [READ_PK_RANGE](https://www.percona.com/blog/files/benchmarks/img/READ_PK_RANGE.png)
- content / image: [READ_PK_RANGE_INDEX](https://www.percona.com/blog/files/benchmarks/img/READ_PK_RANGE_INDEX.png)
- content / image: [READ_KEY_RANGE](https://www.percona.com/blog/files/benchmarks/img/READ_KEY_RANGE.png)
- content / image: [READ_KEY_RANGE_LIMIT](https://www.percona.com/blog/files/benchmarks/img/READ_KEY_RANGE_LIMIT.png)
- content / image: [READ_KEY_RANGE_NO_DATA](https://www.percona.com/blog/files/benchmarks/img/READ_KEY_RANGE_NO_DATA.png)
- content / image: [READ_KEY_RANGE_NO_DATA_LIMIT](https://www.percona.com/blog/files/benchmarks/img/READ_KEY_RANGE_NO_DATA_LIMIT.png)
- content / image: [READ_FTS](https://www.percona.com/blog/files/benchmarks/img/READ_FTS.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
