---
title: 'Open Source Databases on Big Machines: Disk Speed and innodb_io_capacity'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/open-source-databases-big-machines-disk-speed-innodb_io_capacity/
  post_id: 16280
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2017-03-01T23:00:57'
published_at_gmt: '2017-03-01T23:00:57'
modified_at: '2026-03-20T21:18:35'
modified_at_gmt: '2026-03-20T21:18:35'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Open Source
category_slugs:
- mysql
- open-source
tags:
- Disk Performance
- InnoDB
- innodb_io_capacity
- innodb_io_capacity_max
- Performance
- PostgreSQL
tag_slugs:
- disk-performance
- innodb
- innodb_io_capacity
- innodb_io_capacity_max
- performance
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/innodb_io_capacity-e1488325571945.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Open Source Databases on Big Machines: Disk Speed and innodb_io_capacity

Source: [Percona Blog](https://www.percona.com/blog/open-source-databases-big-machines-disk-speed-innodb_io_capacity/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2017-03-01T23:00:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll look for the bottleneck that prevented the performance in my previous post from achieving better results. The powerful machine I used in the tests in my previous post has a comparatively slow disk, and therefore I expected my tests would hit a point when I couldn’t increase performance further due to the disk speed. … Continued

## Images et graphiques reperes

- featured / image: [Open Source Databases on Big Machines: Disk Speed and innodb_io_capacity](https://www.percona.com/wp-content/uploads/2026/03/innodb_io_capacity-e1488325571945.png)
- content / image: [MySQL_Ramdisk-1.png](https://www.percona.com/wp-content/uploads/2026/03/MySQL_Ramdisk-1.png)
- content / image: [PostgreSQL_scalability_ramdisk-1024x614.png](https://www.percona.com/wp-content/uploads/2026/03/PostgreSQL_scalability_ramdisk-1024x614.png)
- content / image: [io_capacity_freematiq-1024x774.png](https://www.percona.com/wp-content/uploads/2026/03/io_capacity_freematiq-1024x774.png)
- content / image: [io_capacity_percona-ssd-ts-2-1024x774.png](https://www.percona.com/wp-content/uploads/2026/03/io_capacity_percona-ssd-ts-2-1024x774.png)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
