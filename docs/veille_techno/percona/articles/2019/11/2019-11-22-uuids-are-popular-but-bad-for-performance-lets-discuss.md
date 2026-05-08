---
title: MySQL UUIDs – Bad For Performance
source:
  name: Percona Blog
  url: https://www.percona.com/blog/uuids-are-popular-but-bad-for-performance-lets-discuss/
  post_id: 21155
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2019-11-22T18:52:57'
published_at_gmt: '2019-11-22T18:52:57'
modified_at: '2026-05-05T22:34:50'
modified_at_gmt: '2026-05-05T22:34:50'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- MySQL
tag_slugs:
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/UUID-popular.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL UUIDs – Bad For Performance

Source: [Percona Blog](https://www.percona.com/blog/uuids-are-popular-but-bad-for-performance-lets-discuss/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2019-11-22T18:52:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

If you do a quick web search about UUIDs and MySQL, you’ll get a fair number of results. Here are just a few examples: Storing UUID and Generated Columns Storing UUID Values in MySQL Illustrating Primary Key models in InnoDB and their impact on disk usage MySQL UUID Smackdown: UUID vs. INT for Primary Key … Continued

## Structure detectee

- H2: What are UUIDs?
- H2: What is so Wrong with UUID Values?
- H2: Size of the Values
- H2: Option 1: Saving IOPs with Pseudo-Random Order
- H2: Option 2: Mapping UUIDs to Integers
- H2: Results for the Alternate Approaches
- H2: Other Options than UUID Values?
- H3: Notes

## Images et graphiques reperes

- featured / image: [MySQL UUIDs – Bad For Performance](https://www.percona.com/wp-content/uploads/2026/03/UUID-popular.png)
- content / image: [Insertion rates for tables using different representation for UUID values](https://www.percona.com/wp-content/uploads/2026/03/rates_vs_sizes.png)
  Caption: Insertion rates for tables using different representation for UUID values
- content / image: [Insertion on tables using UUID values as primary keys, alternative solutions](https://www.percona.com/wp-content/uploads/2026/03/alternate_solutions.png)
  Caption: Insertion on tables using UUID values as primary keys, alternative solutions

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
