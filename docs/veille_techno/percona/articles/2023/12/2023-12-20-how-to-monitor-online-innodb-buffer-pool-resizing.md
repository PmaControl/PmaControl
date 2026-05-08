---
title: How to Monitor Online InnoDB Buffer Pool Resizing
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-monitor-online-innodb-buffer-pool-resizing/
  post_id: 27870
source_author:
  name: Brijesh Chauhan
  slug: brijesh-chauhan
  url: https://www.percona.com/blog/author/brijesh-chauhan/
  website: ''
published_at: '2023-12-20T14:27:42'
published_at_gmt: '2023-12-20T14:27:42'
modified_at: '2026-03-26T20:26:50'
modified_at_gmt: '2026-03-26T20:26:50'
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
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Monitor-Online-InnoDB-Buffer-Pool-Resizing.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Monitor Online InnoDB Buffer Pool Resizing

Source: [Percona Blog](https://www.percona.com/blog/how-to-monitor-online-innodb-buffer-pool-resizing/)

Auteur source: [Brijesh Chauhan](https://www.percona.com/blog/author/brijesh-chauhan/)

Publication: 2023-12-20T14:27:42

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The InnoDB buffer pool acts as a powerhouse for MySQL, caching frequently accessed data and index pages in memory to accelerate query performance. In this blog post, we will go through the process of InnoDB buffer pool resizing online, covering why it is important to monitor its progress and how to monitor it. Importance of … Continued

## Structure detectee

- H2: Importance of monitoring the InnoDB buffer pool resize
- H2: Monitoring buffer pool size changes
- H2: Buffer pool resize monitoring in action
- H2: Managing Online Buffer Pool Resizing

## Images et graphiques reperes

- featured / image: [How to Monitor Online InnoDB Buffer Pool Resizing](https://www.percona.com/wp-content/uploads/2026/03/Monitor-Online-InnoDB-Buffer-Pool-Resizing.png)
- content / image: [PMM Buffer Pool](https://www.percona.com/wp-content/uploads/2026/03/buffer_pool_resize_status-1024x292.png)
- content / image: [buffer_pool_resize_status_code.png](https://www.percona.com/wp-content/uploads/2026/03/buffer_pool_resize_status_code.png)

## Auteur source

Brijesh Chauhan is an experienced MySQL DBA who has been working with Percona since May 2021. Prior to joining Percona, he worked for a leading cloud service provider. Brijesh currently resides in Bangalore with his wife and daughter.
