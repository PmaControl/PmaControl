---
title: Addressing Data Size Differences in MongoDB Replica Set Nodes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/addressing-data-size-differences-in-mongodb-replica-set-nodes/
  post_id: 28917
source_author:
  name: Vinicius Grippa
  slug: vinicius-grippa
  url: https://www.percona.com/blog/author/vinicius-grippa/
  website: ''
published_at: '2024-08-23T13:17:00'
published_at_gmt: '2024-08-23T13:17:00'
modified_at: '2026-03-26T20:13:55'
modified_at_gmt: '2026-03-26T20:13:55'
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
category_slugs:
- insight-for-dbas
- mongodb
tags:
- MongoDB
tag_slugs:
- mongodb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MongoDB-replication-data-size-.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Addressing Data Size Differences in MongoDB Replica Set Nodes

Source: [Percona Blog](https://www.percona.com/blog/addressing-data-size-differences-in-mongodb-replica-set-nodes/)

Auteur source: [Vinicius Grippa](https://www.percona.com/blog/author/vinicius-grippa/)

Publication: 2024-08-23T13:17:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When working with MongoDB replication in environments using the WiredTiger storage engine, you may encounter data size discrepancies between PRIMARY and SECONDARY nodes. When this problem arises, the SECONDARY node uses significantly more disk space than the PRIMARY instance. When this issue first appeared at Percona Support a few years ago, the most notable discussion … Continued

## Structure detectee

- H2: Identifying the problem
- H2: Addressing the data size issue
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Addressing Data Size Differences in MongoDB Replica Set Nodes](https://www.percona.com/wp-content/uploads/2026/03/MongoDB-replication-data-size-.jpg)
- content / image: [wiredtiger](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-08-22-at-16.16.50-1024x390.png)
- content / image: [Screenshot-2024-08-22-at-16.16.59-1024x161.png](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2024-08-22-at-16.16.59-1024x161.png)

## Auteur source

Vinicius Grippa is a Lead Database Engineer at Percona, an Oracle ACE Director, MySQL Rockstar, and co-author of Learning MySQL. With a Bachelor’s degree in Computer Science and 18 years of experience, he specializes in designing databases for mission-critical applications, focusing on MySQL and MongoDB ecosystems. As part of Percona’s Support team, he has assisted customers in resolving complex database challenges across a wide range of scenarios. An active member of the open-source community, he leads the MySQL User Group in Brazil and engages in knowledge sharing through Slack, Meetups, and international conferences, including FOSDEM, Percona Live, and events across Europe, Asia, and the Americas.
