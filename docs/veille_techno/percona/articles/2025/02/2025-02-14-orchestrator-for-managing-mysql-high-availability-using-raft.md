---
title: Orchestrator (for Managing MySQL) High Availability Using Raft
source:
  name: Percona Blog
  url: https://www.percona.com/blog/orchestrator-for-managing-mysql-high-availability-using-raft/
  post_id: 29287
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2025-02-14T14:06:51'
published_at_gmt: '2025-02-14T14:06:51'
modified_at: '2026-03-26T20:25:44'
modified_at_gmt: '2026-03-26T20:25:44'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- MySQL High Availability
- mysql-and-variants
- orchestrator
- Orchestrator High Availability
- Orchestrator Raft
- orchestrator-agent
- Raft
tag_slugs:
- mysql
- mysql-high-availability
- mysql-and-variants
- orchestrator
- orchestrator-high-availability
- orchestrator-raft
- orchestrator-agent
- raft
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Orchestrator-for-Managing-MySQL-High-Availability-Using-Raft.jpg
image_count: 6
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Orchestrator (for Managing MySQL) High Availability Using Raft

Source: [Percona Blog](https://www.percona.com/blog/orchestrator-for-managing-mysql-high-availability-using-raft/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2025-02-14T14:06:51

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

As we know, Orchestrator is a MySQL high availability and replication management tool that aids in managing farms of MySQL servers. In this blog post, we discuss how to make the Orchestrator (which manages MySQL) itself fault-tolerant and highly available. When considering HA for the Orchestrator one of the popular choices will be using the Raft consensus. … Continued

## Structure detectee

- H2: What is Raf t?
- H2: Deployment
- H3: Installation
- H3: Orchestrator/Raft configuration
- H3: Node Discovery:
- H3: Accessing Orchestrator managing database( SQLlite3 ):
- H3: Health/Service:
- H3: Raft Failover/Switchover:
- H3: Summary

## Images et graphiques reperes

- featured / image: [Orchestrator (for Managing MySQL) High Availability Using Raft](https://www.percona.com/wp-content/uploads/2026/03/Orchestrator-for-Managing-MySQL-High-Availability-Using-Raft.jpg)
- content / image: [Orchestrator Raft Topology](https://www.percona.com/wp-content/uploads/2026/03/image2-1-14.png)
  Caption: Orchestrator Raft
- content / image: [Orchestrator Node discovery](https://www.percona.com/wp-content/uploads/2026/03/image4-25.png)
  Caption: Node Discovery
- content / image: [Orchestrator topology](https://www.percona.com/wp-content/uploads/2026/03/image3-24.png)
  Caption: MySQL Topology
- content / image: [Raft](https://www.percona.com/wp-content/uploads/2026/03/image1-43.png)
  Caption: Raft Nodes
- content / image: [Mysql-performance-tuning.png](https://www.percona.com/wp-content/uploads/2026/03/Mysql-performance-tuning.png)

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
