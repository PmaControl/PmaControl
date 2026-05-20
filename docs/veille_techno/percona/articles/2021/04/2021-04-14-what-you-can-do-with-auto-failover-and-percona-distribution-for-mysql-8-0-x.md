---
title: What You Can Do With Auto-Failover and Percona Distribution for MySQL (8.0.x)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/what-you-can-do-with-auto-failover-and-percona-distribution-for-mysql-8-0-x/
  post_id: 24178
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2021-04-14T14:37:50'
published_at_gmt: '2021-04-14T14:37:50'
modified_at: '2026-05-05T16:36:54'
modified_at_gmt: '2026-05-05T16:36:54'
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
- Open Source
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- open-source
- percona-software
tags:
- insight for DBAs
- MySQL
- mysql-and-variants
- Percona Software
tag_slugs:
- insight-for-dbas
- mysql
- mysql-and-variants
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/auto-failover-Percona-MySQL.png
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# What You Can Do With Auto-Failover and Percona Distribution for MySQL (8.0.x)

Source: [Percona Blog](https://www.percona.com/blog/what-you-can-do-with-auto-failover-and-percona-distribution-for-mysql-8-0-x/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2021-04-14T14:37:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Where x is >= 22 😉 The Problem There are few things your data does not like. One is water and another is fire. Well, guess what: If you think that everything will be fine after all, take a look: Given my ISP had part of its management infrastructure on OVH, they had been impacted … Continued

## Structure detectee

- H2: The Problem
- H2: Asynchronous Replication Automatic Failover
- H2: GR Failover
- H3: Why Can This Be a Simplified Version?
- H2: How-To
- H3: Conclusion
- H3: References

## Images et graphiques reperes

- featured / image: [What You Can Do With Auto-Failover and Percona Distribution for MySQL (8.0.x)](https://www.percona.com/wp-content/uploads/2026/03/auto-failover-Percona-MySQL.png)
- content / image: [OVH Fire](https://www.percona.com/wp-content/uploads/2026/03/dc_onfire2-1.jpg)
- content / image: [incidents2.png](https://www.percona.com/wp-content/uploads/2026/03/incidents2.png)
- content / image: [simple Async-replication](https://www.percona.com/wp-content/uploads/2026/03/1-async_failover_8022_pxc-GR-base-async.png)
- content / image: [2-async_failover_8022_pxc-GR-base-async.png](https://www.percona.com/wp-content/uploads/2026/03/2-async_failover_8022_pxc-GR-base-async.png)
- content / image: [3-async_failover_8022_pxc-GR-async_failover.png](https://www.percona.com/wp-content/uploads/2026/03/3-async_failover_8022_pxc-GR-async_failover.png)
- content / image: [Asynchronous Replication Automatic Failover](https://www.percona.com/wp-content/uploads/2026/03/4-async_failover_8022_pxc-GR-async_failover.png)
- content / image: [5-async_failover_8022_pxc-internal_GR-async_failover.png](https://www.percona.com/wp-content/uploads/2026/03/5-async_failover_8022_pxc-internal_GR-async_failover.png)
- content / image: [6-async_failover_8022_pxc-internal_GR-async_failover.png](https://www.percona.com/wp-content/uploads/2026/03/6-async_failover_8022_pxc-internal_GR-async_failover.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
