---
title: Galera 4 Streaming Replication in Percona XtraDB Cluster 8.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/galera-4-streaming-replication-in-percona-xtradb-cluster-8-0/
  post_id: 21886
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2020-05-13T17:00:46'
published_at_gmt: '2020-05-13T17:00:46'
modified_at: '2026-05-04T21:06:13'
modified_at_gmt: '2026-05-04T21:06:13'
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
- Galera 4
- MySQL
- mysql-and-variants
- Percona XtraDB Cluster
- Streaming Replication
tag_slugs:
- galera-4
- mysql
- mysql-and-variants
- percona-xtradb-cluster
- streaming-replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Galera-4-Streaming-Replication-in-Percona-XtraDB-Cluster.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Galera 4 Streaming Replication in Percona XtraDB Cluster 8.0

Source: [Percona Blog](https://www.percona.com/blog/galera-4-streaming-replication-in-percona-xtradb-cluster-8-0/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2020-05-13T17:00:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I was testing the latest Percona XtraDB Cluster 8.0 (PXC) release which has the Galera 4 plugin, and I would like to share my experiences and thoughts on the Streaming Replication feature so far. What Is Streaming Replication, in One Sentence? In Galera 4, the large transaction could split into smaller fragments, and even before … Continued

## Structure detectee

- H3: What Is Streaming Replication, in One Sentence?
- H3: Will Streaming Replication Decrease This Delay?
- H3: Why Could It Be Slower With Streaming Replication?
- H3: Does the Fragment Size Matter?
- H3: Locking
- H3: Metrics/Monitoring
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Galera 4 Streaming Replication in Percona XtraDB Cluster 8.0](https://www.percona.com/wp-content/uploads/2026/03/Galera-4-Streaming-Replication-in-Percona-XtraDB-Cluster.png)
- content / image: [Galera 4 Streaming Replication in Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Galera-4-Streaming-Replication-in-Percona-XtraDB-Cluster-300x168.png)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
