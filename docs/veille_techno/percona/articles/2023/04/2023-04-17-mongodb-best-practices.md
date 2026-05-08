---
title: 'MongoDB Best Practices: Security, Data Modeling, & Schema Design'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mongodb-best-practices/
  post_id: 21893
source_author:
  name: Vinicius Grippa
  slug: vinicius-grippa
  url: https://www.percona.com/blog/author/vinicius-grippa/
  website: ''
published_at: '2023-04-17T17:00:21'
published_at_gmt: '2023-04-17T17:00:21'
modified_at: '2026-03-26T20:14:26'
modified_at_gmt: '2026-03-26T20:14:26'
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
category_slugs:
- insight-for-dbas
- mongodb
tags:
- insight for DBAs
- Linux
- MongoDB
- MongoDB Best Practices
tag_slugs:
- insight-for-dbas
- linux
- mongodb
- mongodb-best-practices
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/unnamed-file-scaled-1.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MongoDB Best Practices: Security, Data Modeling, & Schema Design

Source: [Percona Blog](https://www.percona.com/blog/mongodb-best-practices/)

Auteur source: [Vinicius Grippa](https://www.percona.com/blog/author/vinicius-grippa/)

Publication: 2023-04-17T17:00:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will discuss the best practices on the MongoDB ecosystem applied at the Operating System (OS) and MongoDB levels. We’ll also go over some best practices for MongoDB security as well as MongoDB data modeling. The main objective of this post is to share my experience over the past years tuning … Continued

## Structure detectee

- H2: Operating System (OS) settings
- H3: Swappiness
- H3: NUMA architecture
- H4: zone_reclaim_mode
- H3: IO scheduler
- H3: Transparent Huge Pages
- H3: Dirty ratio
- H3: Filesystems mount options
- H3: Unix ulimit settings
- H3: Network stack
- H3: NTP daemon
- H2: MongoDB settings
- H3: Journal commit interval
- H3: WiredTiger cache
- H3: Read/Write tickets
- H3: Pitfalls for mongos in containers
- H2: Additional MongoDB Best Practices for Security and data modeling
- H3: MongoDB security: Enable authorization and authentication on your database from deployment
- H3: MongoDB security: Take regular MongoDB backups
- H3: MongoDB security: Monitor MongoDB performance regularly
- H2: MongoDB data modeling Best Practices
- H3: MongoDB data modeling: Understand schema differences from relational databases
- H3: MongoDB data modeling: Understand embedding vs. referencing data
- H3: MongoDB data modeling: Use replication or sharding when scaling
- H2: Stay on top of MongoDB Best Practices with Percona Monitoring and Management
- H2: Useful Resources

## Images et graphiques reperes

- featured / image: [MongoDB Best Practices: Security, Data Modeling, & Schema Design](https://www.percona.com/wp-content/uploads/2026/03/unnamed-file-scaled-1.jpg)
- content / image: [Screen-Shot-2020-03-24-at-21.38.10-1024x534.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-03-24-at-21.38.10-1024x534.png)
- content / image: [Screen-Shot-2020-03-24-at-21.44.50-1024x490.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-03-24-at-21.44.50-1024x490.png)

## Auteur source

Vinicius Grippa is a Lead Database Engineer at Percona, an Oracle ACE Director, MySQL Rockstar, and co-author of Learning MySQL. With a Bachelor’s degree in Computer Science and 18 years of experience, he specializes in designing databases for mission-critical applications, focusing on MySQL and MongoDB ecosystems. As part of Percona’s Support team, he has assisted customers in resolving complex database challenges across a wide range of scenarios. An active member of the open-source community, he leads the MySQL User Group in Brazil and engages in knowledge sharing through Slack, Meetups, and international conferences, including FOSDEM, Percona Live, and events across Europe, Asia, and the Americas.
