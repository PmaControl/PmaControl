---
title: Setup ProxySQL for High Availability (not a Single Point of Failure)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/setup-proxysql-for-high-availability-not-single-point-failure/
  post_id: 16160
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2017-01-19T22:05:18'
published_at_gmt: '2017-01-19T22:05:18'
modified_at: '2026-05-05T20:31:33'
modified_at_gmt: '2026-05-05T20:31:33'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:proxysql
categories:
- Insight for DBAs
- MySQL
- ProxySQL
category_slugs:
- insight-for-dbas
- mysql
- proxysql
tags:
- High Availability
- keepalived
- MySQL
- ProxySQL
- pxc
tag_slugs:
- high-availability
- keepalived
- mysql
- proxysql
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-for-High-Availability.png
image_count: 9
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Setup ProxySQL for High Availability (not a Single Point of Failure)

Source: [Percona Blog](https://www.percona.com/blog/setup-proxysql-for-high-availability-not-single-point-failure/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2017-01-19T22:05:18

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how to set up ProxySQL for high availability. During the last few months, we’ve had a lot of opportunities to present and discuss a very powerful tool that will become more and more used in the architectures supporting MySQL: ProxySQL. ProxySQL is becoming more flexible, solid, performant and … Continued

## Structure detectee

- H2: You can use ProxySQL for high availability.
- H2: Setup
- H2: Simple Setup using a single VIP, 3 ProxySQL and 3 Galera nodes
- H2: Let’s see another case
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [Setup ProxySQL for High Availability (not a Single Point of Failure)](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-for-High-Availability.png)
- content / image: [ProxySQL for High Availability](https://www.percona.com/wp-content/uploads/2026/03/MHA-2.bmp)
- content / image: [ProxySQL for High Availability](https://www.percona.com/wp-content/uploads/2026/03/tileProxy-300x244.png)
- content / image: [ProxySQL for High Availability](https://www.percona.com/wp-content/uploads/2026/03/ProxyCascade-300x178.png)
- content / image: [keepalived_logo_small.jpg](https://www.percona.com/wp-content/uploads/2026/03/keepalived_logo_small.jpg)
- content / image: [ProxySQL for High Availability](https://www.percona.com/wp-content/uploads/2026/03/proxy_keep_single-202x300.png)
- content / image: [ProxySQL for High Availability](https://www.percona.com/wp-content/uploads/2026/03/proxy_keep_single_failover-202x300.png)
- content / image: [proxy_keep_multiple-202x300.png](https://www.percona.com/wp-content/uploads/2026/03/proxy_keep_multiple-202x300.png)
- content / image: [ProxySQL for High Availability](https://www.percona.com/wp-content/uploads/2026/03/proxy_keep_multiple_full_failover-202x300.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
