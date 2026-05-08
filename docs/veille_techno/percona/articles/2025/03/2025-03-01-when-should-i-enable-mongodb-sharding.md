---
title: A Tutorial on MongoDB Sharding Best Practices & When To Enable It
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-should-i-enable-mongodb-sharding/
  post_id: 17533
source_author:
  name: Adamo Tonete
  slug: adamo-tonete
  url: https://www.percona.com/blog/author/adamo-tonete/
  website: ''
published_at: '2025-03-01T10:00:47'
published_at_gmt: '2025-03-01T10:00:47'
modified_at: '2026-03-26T20:13:39'
modified_at_gmt: '2026-03-26T20:13:39'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- MongoDB
- Percona Software
category_slugs:
- insight-for-dbas
- mongodb
- percona-software
tags:
- Backups
- disaster recovery
- MongoDB
- sharding
tag_slugs:
- backups
- disaster-recovery
- mongodb
- sharding
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mongodb-sharding-1.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Tutorial on MongoDB Sharding Best Practices & When To Enable It

Source: [Percona Blog](https://www.percona.com/blog/when-should-i-enable-mongodb-sharding/)

Auteur source: [Adamo Tonete](https://www.percona.com/blog/author/adamo-tonete/)

Publication: 2025-03-01T10:00:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog was originally published in October 2017 and was updated in March 2025. Is your MongoDB database struggling with growth? Sharding might be the answer, but it’s a complex architectural change. Understanding MongoDB sharding best practices is crucial before you decide to implement it. This blog post explores the key drivers for sharding a … Continued

## Structure detectee

- H2: What is sharding in MongoDB?
- H2: How sharding works in MongoDB: A tutorial
- H3: Start the config server replica set
- H3: Set up the shards
- H3: Start the mongos
- H3: Turn on and configure sharding for the database
- H2: When to enable MongoDB sharding
- H3: 1) Disaster recovery plan
- H3: 2) Hardware limitations
- H3: 3) Storage engine limitations
- H3: 4) Hot data vs. cold data
- H3: 5) Geo-distributed data
- H3: 6) Infrastructure limitations
- H3: 7) Failure isolation
- H3: 8) Speed up queries
- H2: Best Practices for MongoDB Sharding
- H3: Choose a shard key with high cardinality
- H3: Use a hashed sharding key scheme
- H3: Shard early
- H3: Strategically run the shard balancer
- H2: Final words on MongoDB sharding best practices
- H2: FAQs
- H3: What is the difference between sharding and partitioning in MongoDB?
- H3: What is the difference between replication and sharding in MongoDB?
- H3: How does auto sharding work in MongoDB?

## Images et graphiques reperes

- featured / image: [A Tutorial on MongoDB Sharding Best Practices & When To Enable It](https://www.percona.com/wp-content/uploads/2026/03/mongodb-sharding-1.jpg)
- content / image: [MongoDB Alternative](https://www.percona.com/wp-content/uploads/2026/03/Percona-MongoDB-Hub.png)
- content / image: [Image Source: MongoDB](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2023-08-03-at-9.57.27-AM-1024x747.png)
  Caption: Image Source: MongoDB

## Auteur source

Adamo joined Percona in 2015, after working as a MongoDB/MySQL Database Administrator for three years. As the main database member of a startup, he was responsible for suggesting the best architecture and data flows for a worldwide company in a 7/24 environment. Before that, he worked as a Microsoft SQL Server DBA in a large e-commerce company, mainly on performance tuning and automation. Adamo has almost eight years of experience working as a DBA and in the past three years he has moved to NoSQL technologies without giving up relational databases.
