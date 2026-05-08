---
title: 'How Not to do MySQL High Availability: Geographic Node Distribution with Galera-Based Replication Misuse'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-not-to-do-mysql-high-availability-geographic-node-distribution-with-galera-based-replication-misuse/
  post_id: 19603
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2018-11-16T00:46:50'
published_at_gmt: '2018-11-16T00:46:50'
modified_at: '2026-05-05T20:23:22'
modified_at_gmt: '2026-05-05T20:23:22'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- Galera Cluster
- High Availability
- network
- pxc
tag_slugs:
- galera-cluster
- high-availability
- network
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-High-Availability-2.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Not to do MySQL High Availability: Geographic Node Distribution with Galera-Based Replication Misuse

Source: [Percona Blog](https://www.percona.com/blog/how-not-to-do-mysql-high-availability-geographic-node-distribution-with-galera-based-replication-misuse/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2018-11-16T00:46:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Let’s talk about MySQL high availability (HA) and synchronous replication once more. It’s part of a longer series on some high availability reference architecture solutions over geographically distributed areas. Part 1: Reference Architecture(s) for High Availability Solutions in Geographic Distributed Scenarios: Why Should I Care? Part 2: MySQL High Availability On-Premises: A Geographically Distributed Scenario … Continued

## Structure detectee

- H2: The Problem
- H2: What Happen When I Put Things on the Network?
- H2: An Example
- H2: What Is the Right Thing To Do?
- H2: Conclusions
- H2: References
- H2: Sample test

## Images et graphiques reperes

- featured / image: [How Not to do MySQL High Availability: Geographic Node Distribution with Galera-Based Replication Misuse](https://www.percona.com/wp-content/uploads/2026/03/MySQL-High-Availability-2.png)
- content / image: [Picture1-1-1024x359.png](https://www.percona.com/wp-content/uploads/2026/03/Picture1-1-1024x359.png)
- content / image: [Picture2-1-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/Picture2-1-scaled.png)
- content / image: [Picture3.png](https://www.percona.com/wp-content/uploads/2026/03/Picture3.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
