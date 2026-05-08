---
title: Evaluating Group Replication Scaling for I/O Bound Workloads
source:
  name: Percona Blog
  url: https://www.percona.com/blog/evaluating-group-replication-scaling-for-i-o-bound-workloads/
  post_id: 22163
source_author:
  name: Vadim Tkachenko
  slug: vadim
  url: https://www.percona.com/blog/author/vadim/
  website: http://www.percona.com/
published_at: '2020-04-10T17:46:28'
published_at_gmt: '2020-04-10T17:46:28'
modified_at: '2026-05-05T16:27:40'
modified_at_gmt: '2026-05-05T16:27:40'
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
- Benchmarks
- MySQL
- Replication
tag_slugs:
- benchmarks
- mysql
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Group-Replication-Scaling-IO-Workload.png
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Evaluating Group Replication Scaling for I/O Bound Workloads

Source: [Percona Blog](https://www.percona.com/blog/evaluating-group-replication-scaling-for-i-o-bound-workloads/)

Auteur source: [Vadim Tkachenko](https://www.percona.com/blog/author/vadim/)

Publication: 2020-04-10T17:46:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, I want to evaluate Group Replication Scaling capabilities in cases when we increase the number of nodes and increase user connections. While this setup is identical to that in my post “Evaluating Group Replication Scaling Capabilities in MySQL”, in this case, I will use an I/O bound workload. For this test, I … Continued

## Structure detectee

- H2: Results
- H2: 3 nodes vs. 5 nodes
- H2: Handling Sustained Incoming Rate
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Evaluating Group Replication Scaling for I/O Bound Workloads](https://www.percona.com/wp-content/uploads/2026/03/Group-Replication-Scaling-IO-Workload.png)
- content / image: [image4-1-2.png](https://www.percona.com/wp-content/uploads/2026/03/image4-1-2.png)
- content / image: [Group Replication Scaling](https://www.percona.com/wp-content/uploads/2026/03/image6-1-2.png)
- content / image: [image3-1-2.png](https://www.percona.com/wp-content/uploads/2026/03/image3-1-2.png)
- content / image: [image8-1.png](https://www.percona.com/wp-content/uploads/2026/03/image8-1.png)
- content / image: [image1-1-2.png](https://www.percona.com/wp-content/uploads/2026/03/image1-1-2.png)
- content / image: [image5-1-2.png](https://www.percona.com/wp-content/uploads/2026/03/image5-1-2.png)
- content / image: [image7-2.png](https://www.percona.com/wp-content/uploads/2026/03/image7-2.png)
- content / image: [image2-1-2.png](https://www.percona.com/wp-content/uploads/2026/03/image2-1-2.png)

## Auteur source

Vadim Tkachenko co-founded Percona in 2006 and leads Percona Labs, which focuses on technology research and performance evaluations of Percona’s and third-party products. Vadim’s expertise in LAMP performance and multi-threaded programming help optimize MySQL and InnoDB internals to take full advantage of modern hardware. He also co-authored the book High Performance MySQL: Optimization, Backups, and Replication 3rd Edition.
