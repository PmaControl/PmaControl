---
title: Can MySQL Parallel Replication Help My Slave?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/can-mysql-parallel-replication-help-my-slave/
  post_id: 19458
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2018-10-17T12:50:13'
published_at_gmt: '2018-10-17T12:50:13'
modified_at: '2026-05-05T20:22:27'
modified_at_gmt: '2026-05-05T20:22:27'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- Replication
tag_slugs:
- mysql
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pmm-mysql-row-operations-per-hour-graph.png
image_count: 3
graph_or_chart_count: 3
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Can MySQL Parallel Replication Help My Slave?

Source: [Percona Blog](https://www.percona.com/blog/can-mysql-parallel-replication-help-my-slave/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2018-10-17T12:50:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Parallel replication has been around for a few years now but is still not that commonly used. I had a customer where the master had a very large write workload. The slave could not keep up so I recommended to use parallel slave threads. But how can I measure if it really helps and is … Continued

## Structure detectee

- H3: Conclusion

## Images et graphiques reperes

- featured / graph_or_chart: [Can MySQL Parallel Replication Help My Slave?](https://www.percona.com/wp-content/uploads/2026/03/pmm-mysql-row-operations-per-hour-graph.png)
- content / graph_or_chart: [MySQL Replication Delay graph from PMM](https://www.percona.com/wp-content/uploads/2026/03/pmm-mysql-replication-delay-graph.png)
- content / graph_or_chart: [InnoDB Row Operations graph from PMM](https://www.percona.com/wp-content/uploads/2026/03/pmm-innodb-operations-graph.png)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
