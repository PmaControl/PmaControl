---
title: 18 Things You Can Do to Remove MySQL Bottlenecks Caused by High Traffic (Part Two)
source:
  name: Percona Blog
  url: https://www.percona.com/blog/18-things-you-can-do-to-remove-mysql-bottlenecks-caused-by-high-traffic-part-two/
  post_id: 22089
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2020-04-06T15:41:22'
published_at_gmt: '2020-04-06T15:41:22'
modified_at: '2026-05-05T23:11:24'
modified_at_gmt: '2026-05-05T23:11:24'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
- search:pmm
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-High-Traffic-Two.png
image_count: 16
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# 18 Things You Can Do to Remove MySQL Bottlenecks Caused by High Traffic (Part Two)

Source: [Percona Blog](https://www.percona.com/blog/18-things-you-can-do-to-remove-mysql-bottlenecks-caused-by-high-traffic-part-two/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2020-04-06T15:41:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This is a three-part blog series that focuses on dealing with an unexpected high traffic event as it is happening. Part one can be found here and part three can be found here. 7. Get More Memory Complexity: Low Potential Impact: High If your data does not fit into memory well, your MySQL performance is … Continued

## Structure detectee

- H3: 7. Get More Memory
- H3: 8. Move To Faster Storage
- H3: 9. Check Your Network
- H3: 10. Locate and Optimize Queries Which Cause the Load
- H3: 11. Add Missing Indexes
- H3: 12. Drop Unneeded Indexes

## Images et graphiques reperes

- featured / image: [18 Things You Can Do to Remove MySQL Bottlenecks Caused by High Traffic (Part Two)](https://www.percona.com/wp-content/uploads/2026/03/MySQL-High-Traffic-Two.png)
- content / image: [MySQL High Traffic](https://www.percona.com/wp-content/uploads/2026/03/MySQL-High-Traffic-Two-300x168.png)
- content / image: [Screen-Shot-2020-04-01-at-3.20.44-PM-1024x804.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.20.44-PM-1024x804.png)
- content / image: [Screen-Shot-2020-04-01-at-3.21.30-PM-1024x484.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.21.30-PM-1024x484.png)
- content / image: [Screen-Shot-2020-04-01-at-3.22.11-PM-1024x376.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.22.11-PM-1024x376.png)
- content / image: [Screen-Shot-2020-04-01-at-3.22.41-PM-1024x368.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.22.41-PM-1024x368.png)
- content / image: [Screen-Shot-2020-04-01-at-3.23.18-PM-1024x399.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.23.18-PM-1024x399.png)
- content / image: [Screen-Shot-2020-04-01-at-3.23.46-PM-1024x370.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.23.46-PM-1024x370.png)
- content / image: [Screen-Shot-2020-04-01-at-3.24.13-PM-1024x370.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.24.13-PM-1024x370.png)
- content / image: [Screen-Shot-2020-04-01-at-3.24.55-PM-1024x366.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.24.55-PM-1024x366.png)
- content / image: [Screen-Shot-2020-04-01-at-3.25.38-PM-1024x409.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.25.38-PM-1024x409.png)
- content / image: [Screen-Shot-2020-04-01-at-3.26.12-PM-1024x179.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.26.12-PM-1024x179.png)
- content / image: [Screen-Shot-2020-04-01-at-3.26.54-PM-1024x355.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.26.54-PM-1024x355.png)
- content / image: [Screen-Shot-2020-04-01-at-3.27.39-PM-1024x517.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.27.39-PM-1024x517.png)
- content / image: [Screen-Shot-2020-04-01-at-3.28.20-PM-1024x341.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.28.20-PM-1024x341.png)
- content / image: [Screen-Shot-2020-04-01-at-3.28.50-PM-1024x195.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2020-04-01-at-3.28.50-PM-1024x195.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
