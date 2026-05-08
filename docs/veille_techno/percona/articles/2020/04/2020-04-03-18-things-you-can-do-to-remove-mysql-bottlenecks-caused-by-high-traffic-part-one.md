---
title: 18 Things You Can Do to Remove MySQL Bottlenecks Caused by High Traffic (Part One)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/18-things-you-can-do-to-remove-mysql-bottlenecks-caused-by-high-traffic-part-one/
  post_id: 22083
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-04-03T16:27:23'
published_at_gmt: '2020-04-03T16:27:23'
modified_at: '2026-03-23T15:13:18'
modified_at_gmt: '2026-03-23T15:13:18'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- ProxySQL
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
- search:proxysql
categories:
- Database Trends
- Insight for DBAs
- MySQL
category_slugs:
- database-trends
- insight-for-dbas
- mysql
tags:
- High Availability
- insight for DBAs
- MySQL
tag_slugs:
- high-availability
- insight-for-dbas
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/remove-MySQL-traffic-bottlenecks.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# 18 Things You Can Do to Remove MySQL Bottlenecks Caused by High Traffic (Part One)

Source: [Percona Blog](https://www.percona.com/blog/18-things-you-can-do-to-remove-mysql-bottlenecks-caused-by-high-traffic-part-one/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-04-03T16:27:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is a three-part blog series. Part two is located here, and part three can be found here. There was no reason to plan for it, but the load on your system increased 100%, 300%, 500%, and your MySQL database has to support it. This is a reality many online systems have to deal with … Continued

## Structure detectee

- H3: 1. Scale your Cloud Instance Size
- H3: 2. Deploy More MySQL Slaves/Replicas
- H3: 3. Deploy ProxySQL for Connection Management and Caching
- H3: 4. Disable Heavy Load Applications Features
- H3: 5. Check for Resource Bottlenecks
- H3: 6. Get More Cores or Faster Cores

## Images et graphiques reperes

- featured / image: [18 Things You Can Do to Remove MySQL Bottlenecks Caused by High Traffic (Part One)](https://www.percona.com/wp-content/uploads/2026/03/remove-MySQL-traffic-bottlenecks.png)
- content / image: [remove MySQL traffic bottlenecks](https://www.percona.com/wp-content/uploads/2026/03/remove-MySQL-traffic-bottlenecks-300x168.png)
- content / image: [Screen-Shot-2020-04-01-at-3.16.41-PM-1024x426.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.16.41-PM-1024x426.png)
- content / image: [Screen-Shot-2020-04-01-at-3.19.51-PM-1024x785.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.19.51-PM-1024x785.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
