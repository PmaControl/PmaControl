---
title: Resharding in MongoDB 5.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/resharding-in-mongodb-5-0/
  post_id: 24701
source_author:
  name: Corrado Pandiani
  slug: corrado-pandiani
  url: https://www.percona.com/blog/author/corrado-pandiani/
  website: ''
published_at: '2021-08-24T13:55:05'
published_at_gmt: '2021-08-24T13:55:05'
modified_at: '2026-03-26T20:15:06'
modified_at_gmt: '2026-03-26T20:15:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- Insight for Developers
- MongoDB
category_slugs:
- insight-for-dbas
- insight-for-developers
- mongodb
tags:
- MongoDB
- resharding
- sharding
tag_slugs:
- mongodb
- resharding
- sharding
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Resharding-in-MongoDB-5.0.png
image_count: 7
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Resharding in MongoDB 5.0

Source: [Percona Blog](https://www.percona.com/blog/resharding-in-mongodb-5-0/)

Auteur source: [Corrado Pandiani](https://www.percona.com/blog/author/corrado-pandiani/)

Publication: 2021-08-24T13:55:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MongoDB 5.0 has been released with a bunch of new features. An important one is the capability to completely redefine the shard key of a collection in a sharded cluster. Resharding was a feature frequently requested by the customers. In MongoDB 4.4 only the refining of the shard was available; from now on you can … Continued

## Structure detectee

- H2: The Problem of Changing a Shard Key
- H2: Internals
- H2: Prerequisites for Resharding
- H2: Let’s Test Resharding in MongoDB
- H2: Some Graphs from Percona Monitoring and Management
- H3: Conclusions

## Images et graphiques reperes

- featured / image: [Resharding in MongoDB 5.0](https://www.percona.com/wp-content/uploads/2026/03/Resharding-in-MongoDB-5.0.png)
- content / image: [Resharding in MongoDB 5.0](https://www.percona.com/wp-content/uploads/2026/03/Resharding-in-MongoDB-5.0-300x157.png)
- content / image: [percona monitoring and management mongodb](https://www.percona.com/wp-content/uploads/2026/03/CPU-4-scaled.png)
- content / image: [Disk utilization](https://www.percona.com/wp-content/uploads/2026/03/disk_ops-scaled.png)
- content / image: [diosk_latency-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/diosk_latency-scaled.png)
- content / graph_or_chart: [mongodb-latency-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/mongodb-latency-scaled.png)
- content / image: [wt-transactions-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/wt-transactions-scaled.png)

## Auteur source

Prior to joining Percona as a Senior Consultant, Corrado spent more than 20 years in developing web sites and designing and administering MySQL. He is a MySQL enthusiast since version 3.23 and his skills are focused on performances and architectural design. He's also a trainer and a MongoDB consultant.
