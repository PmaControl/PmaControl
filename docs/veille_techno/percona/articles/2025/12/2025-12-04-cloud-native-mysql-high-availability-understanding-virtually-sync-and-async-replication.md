---
title: 'Cloud-Native MySQL High Availability: Understanding Virtually SYNC and ASYNC Replication'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/cloud-native-mysql-high-availability-understanding-virtually-sync-and-async-replication/
  post_id: 35407
source_author:
  name: Edith Puclla
  slug: edith-puclla
  url: https://www.percona.com/blog/author/edith-puclla/
  website: ''
published_at: '2025-12-04T14:21:54'
published_at_gmt: '2025-12-04T14:21:54'
modified_at: '2026-03-26T20:25:10'
modified_at_gmt: '2026-03-26T20:25:10'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Cloud
- MySQL
category_slugs:
- cloud
- mysql
tags:
- cloud-native
- kubernetes operators
- MySQL
- mysql-and-variants
- Open Source
tag_slugs:
- cloud-native
- kubernetes-operators
- mysql
- mysql-and-variants
- open-source
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Cloud-Native-MySQL-High-Availability-Understanding-Virtually-SYNC-and-ASYNC-Replication.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Cloud-Native MySQL High Availability: Understanding Virtually SYNC and ASYNC Replication

Source: [Percona Blog](https://www.percona.com/blog/cloud-native-mysql-high-availability-understanding-virtually-sync-and-async-replication/)

Auteur source: [Edith Puclla](https://www.percona.com/blog/author/edith-puclla/)

Publication: 2025-12-04T14:21:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When we run databases in Kubernetes, we quickly learn one important truth: things will fail, and we need to be prepared for this. Pods are ephemeral; nodes can come and go, storage is abstracted behind PersistentVolumes and can be either local to a node or backed by network storage, and Kubernetes moves workloads as needed … Continued

## Structure detectee

- H2: Replication in a Cloud-Native World
- H2: Virtually Synchronous Replication (SYNC)
- H2: Asynchronous Replication (ASYNC)
- H2: Why Cloud-Native Replication Behaves Differently
- H2: Choosing the Right Approach
- H2: What Comes Next

## Images et graphiques reperes

- featured / image: [Cloud-Native MySQL High Availability: Understanding Virtually SYNC and ASYNC Replication](https://www.percona.com/wp-content/uploads/2026/03/Cloud-Native-MySQL-High-Availability-Understanding-Virtually-SYNC-and-ASYNC-Replication.jpg)
- content / image: [introblog-1024x327.png](https://www.percona.com/wp-content/uploads/2026/03/introblog-1024x327.png)
- content / image: [Virtually Synchronous Replication (SYNC)](https://www.percona.com/wp-content/uploads/2026/03/SYNCC-scaled-1.png)
- content / image: [Asynchronous Replication (ASYNC)](https://www.percona.com/wp-content/uploads/2026/03/ASYNC-scaled-1.png)

## Auteur source

Edith Puclla is a Technology Evangelist at Percona Corporation, a CNCF Ambassador, an open source contributor with a background in DevOps, and a Docker and Kubernetes enthusiast.
