---
title: Tuning InnoDB Primary Keys
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tuning-innodb-primary-keys/
  post_id: 19042
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2018-07-26T17:33:48'
published_at_gmt: '2018-07-26T17:33:48'
modified_at: '2026-05-05T19:54:00'
modified_at_gmt: '2026-05-05T19:54:00'
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
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pile-of-paper.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Tuning InnoDB Primary Keys

Source: [Percona Blog](https://www.percona.com/blog/tuning-innodb-primary-keys/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2018-07-26T17:33:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The choice of good InnoDB primary keys is a critical performance tuning decision. This post will guide you through the steps of choosing the best primary key depending on your workload. As a principal architect at Percona, one of my main duties is to tune customer databases. There are many aspects related to performance tuning … Continued

## Structure detectee

- H4: What is special about InnoDB primary keys?
- H4: A practical analogy
- H4: Determine your workload type
- H4: A read-intensive workload
- H4: You May Also Like

## Images et graphiques reperes

- featured / image: [Tuning InnoDB Primary Keys](https://www.percona.com/wp-content/uploads/2026/03/pile-of-paper.jpg)
- content / image: [A simple three level B-Tree](https://www.percona.com/wp-content/uploads/2026/03/btree-1.png)
  Caption: A simple three level B-Tree
- content / image: [Get the Solution Brief](https://www.percona.com/wp-content/uploads/2026/03/f4cfc8f9-0359-426a-b788-95f7e6b2421b.png)

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
