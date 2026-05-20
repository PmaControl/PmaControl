---
title: 'MongoDB 101: Tuning WiredTiger Cache After a Memory Upgrade'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-101-how-to-tune-your-mongodb-configuration-after-upgrading-to-more-memory/
  post_id: 23725
source_author:
  name: Mike Grayson
  slug: mike-grayson
  url: https://www.percona.com/blog/author/mike-grayson/
  website: ''
published_at: '2025-03-08T14:55:39'
published_at_gmt: '2025-03-08T14:55:39'
modified_at: '2026-03-26T20:13:37'
modified_at_gmt: '2026-03-26T20:13:37'
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
- MongoDB
- Open Source
category_slugs:
- insight-for-dbas
- mongodb
- open-source
tags:
- insight for DBAs
- MongoDB
tag_slugs:
- insight-for-dbas
- mongodb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Tune-Your-MongoDB-Configuration.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB 101: Tuning WiredTiger Cache After a Memory Upgrade

Source: [Percona Blog](https://www.percona.com/blog/mongodb-101-how-to-tune-your-mongodb-configuration-after-upgrading-to-more-memory/)

Auteur source: [Mike Grayson](https://www.percona.com/blog/author/mike-grayson/)

Publication: 2025-03-08T14:55:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post was originally published in January 2021 and was updated in March 2025. Adding more memory to your MongoDB deployment is a common vertical scaling strategy. But simply increasing RAM isn’t enough; you also need to tune MongoDB’s WiredTiger cache to effectively utilize that new memory. This post in our MongoDB 101 series will … Continued

## Structure detectee

- H2: Why might you need to add more memory?
- H2: Understanding MongoDB memory utilization & WiredTiger Cache
- H3: Key WiredTiger Cache metrics from serverStatus
- H2: Example: Tuning WiredTiger cacheSizeGB after memory upgrade
- H3: Summary

## Images et graphiques reperes

- featured / image: [MongoDB 101: Tuning WiredTiger Cache After a Memory Upgrade](https://www.percona.com/wp-content/uploads/2026/03/Tune-Your-MongoDB-Configuration.png)
- content / image: [MongoDB Alternative](https://www.percona.com/wp-content/uploads/2026/03/Percona-MongoDB-Hub.png)

## Auteur source

Mike is a database engineer who focuses on MongoDB for the Percona Managed Services Team. He helps keep our Managed Services customers MongoDB databases available and performant. He is AWS and Azure certified.
