---
title: Efficient Data Archiving in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/efficient-data-archiving-in-mysql/
  post_id: 25930
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2022-08-30T11:50:57'
published_at_gmt: '2022-08-30T11:50:57'
modified_at: '2026-03-26T20:31:18'
modified_at_gmt: '2026-03-26T20:31:18'
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
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Efficient-Data-Archiving-in-MySQL-1.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Efficient Data Archiving in MySQL

Source: [Percona Blog](https://www.percona.com/blog/efficient-data-archiving-in-mysql/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2022-08-30T11:50:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I have been working with a few customers with multiple terabytes of transactional data on their MySQL clusters. These very large datasets are not really needed for their daily operations but they are very convenient because they allow them to query historical data easily. However the convenience comes at a high price, you pay … Continued

## Structure detectee

- H2: Key elements
- H2: Capturing the changes (CDC)
- H2: Removing deletions
- H2: Storage engine for the archives
- H2: Architectures for efficient data archiving
- H3: Shifted table
- H3: Ignored table
- H2: Example
- H3: The application
- H3: Capturing the changes
- H3: Filtering script
- H3: All together
- H3: A more realistic architecture
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Efficient Data Archiving in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Efficient-Data-Archiving-in-MySQL-1.png)
- content / image: [Efficient Data Archiving in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Efficient-Data-Archiving-in-MySQL-1-300x157.png)
- content / image: [Add_Ra.png](https://www.percona.com/wp-content/uploads/2026/03/Add_Ra.png)
- content / image: [Efficient Data Archiving in MySQL](https://www.percona.com/wp-content/uploads/2026/03/Ra_ta.png)
- content / image: [Efficient Data Archiving in MySQL Maxwell](https://www.percona.com/wp-content/uploads/2026/03/Ra_ignore_t.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
