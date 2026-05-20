---
title: Best Practices for Configuring Optimal MySQL Memory Usage
source:
  name: Percona Blog
  url: https://www.percona.com/blog/best-practices-for-configuring-optimal-mysql-memory-usage/
  post_id: 15061
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2016-05-03T14:26:57'
published_at_gmt: '2016-05-03T14:26:57'
modified_at: '2026-04-16T16:23:21'
modified_at_gmt: '2026-04-16T16:23:21'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- MySQL
category_slugs:
- mysql
tags:
- Optimal MySQL Memory Usage
tag_slugs:
- optimal-mysql-memory-usage
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Optimal-MySQL-Memory-Usage.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Best Practices for Configuring Optimal MySQL Memory Usage

Source: [Percona Blog](https://www.percona.com/blog/best-practices-for-configuring-optimal-mysql-memory-usage/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2016-05-03T14:26:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll discuss best practices for configuring optimal MySQL memory usage. Correctly configuring memory is critical for MySQL performance and stability. Default settings in MySQL 5.7 use very little memory, which is inefficient—but over-allocation can cause instability or crashes. Key rule: MySQL should never cause the operating system to swap. Even small … Continued

## Structure detectee

- H2: Understanding MySQL Memory Usage
- H2: How Much Memory to Allocate
- H2: Example Configuration
- H2: Operating System Considerations
- H3: Swap
- H3: OOM Killer
- H3: NUMA
- H2: More Resources
- H3: Posts
- H3: Webinars
- H3: Presentations
- H3: Free eBooks
- H3: Tools

## Images et graphiques reperes

- featured / image: [Best Practices for Configuring Optimal MySQL Memory Usage](https://www.percona.com/wp-content/uploads/2026/03/Optimal-MySQL-Memory-Usage.png)
- content / image: [No-Significant-Swapping.png](https://www.percona.com/wp-content/uploads/2026/03/No-Significant-Swapping.png)
- content / image: [Heavy-Swapping.png](https://www.percona.com/wp-content/uploads/2026/03/Heavy-Swapping.png)
- content / image: [Swap-activity.png](https://www.percona.com/wp-content/uploads/2026/03/Swap-activity.png)
- content / image: [Download Percona Server for MySQL Today!](https://www.percona.com/wp-content/uploads/2026/03/445d83f3-2bc7-431b-8cd4-32df87f851bc.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
