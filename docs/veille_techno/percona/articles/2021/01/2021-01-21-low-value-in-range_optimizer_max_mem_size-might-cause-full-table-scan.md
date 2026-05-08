---
title: Low Value in range_optimizer_max_mem_size Might Cause Full Table Scan
source:
  name: Percona Blog
  url: https://www.percona.com/blog/low-value-in-range_optimizer_max_mem_size-might-cause-full-table-scan/
  post_id: 23809
source_author:
  name: Carlos Tutte
  slug: carlos-tutte
  url: https://www.percona.com/blog/author/carlos-tutte/
  website: ''
published_at: '2021-01-21T15:11:17'
published_at_gmt: '2021-01-21T15:11:17'
modified_at: '2026-04-27T22:21:09'
modified_at_gmt: '2026-04-27T22:21:09'
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
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- insight for DBAs
- insight for developers
- MySQL
- mysql-and-variants
tag_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/range_optimizer_max_mem_size-Might-Cause-Full-Table-Scan.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Low Value in range_optimizer_max_mem_size Might Cause Full Table Scan

Source: [Percona Blog](https://www.percona.com/blog/low-value-in-range_optimizer_max_mem_size-might-cause-full-table-scan/)

Auteur source: [Carlos Tutte](https://www.percona.com/blog/author/carlos-tutte/)

Publication: 2021-01-21T15:11:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Although how range_optimizer_max_mem_size operates is explained in the official doc, it’s not a well-known variable and surely not a variable you need to modify under most circumstances. But from time to time we get some performance tickets related to this. What problem does this variable cause if it is not properly sized? Let’s find out … Continued

## Images et graphiques reperes

- featured / image: [Low Value in range_optimizer_max_mem_size Might Cause Full Table Scan](https://www.percona.com/wp-content/uploads/2026/03/range_optimizer_max_mem_size-Might-Cause-Full-Table-Scan.png)
- content / image: [range_optimizer_max_mem_size Might Cause Full Table Scan](https://www.percona.com/wp-content/uploads/2026/03/range_optimizer_max_mem_size-Might-Cause-Full-Table-Scan-300x157.png)

## Auteur source

Computer engineer from Montevideo, Uruguay, joined Percona on February 2018, first as a support engineer, then moving to the consulting team. Working in complex IT solutions for more than 10 years, Carlos now specializes in MySQL and related technologies
