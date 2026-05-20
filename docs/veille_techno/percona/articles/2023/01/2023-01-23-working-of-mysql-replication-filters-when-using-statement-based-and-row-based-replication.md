---
title: Working of MySQL Replication Filters When Using Statement-based and Row-based Replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/working-of-mysql-replication-filters-when-using-statement-based-and-row-based-replication/
  post_id: 26543
source_author:
  name: Mughees Ahmed
  slug: mughees-ahmed
  url: https://www.percona.com/blog/author/mughees-ahmed/
  website: ''
published_at: '2023-01-23T14:14:00'
published_at_gmt: '2023-01-23T14:14:00'
modified_at: '2026-03-26T20:30:16'
modified_at_gmt: '2026-03-26T20:30:16'
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
- asynchronous MySQL replication
- MySQL
- MySQL Replication
- mysql-and-variants
- Replication
tag_slugs:
- asynchronous-mysql-replication
- mysql
- mysql-replication
- mysql-and-variants
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Replication-Filters.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Working of MySQL Replication Filters When Using Statement-based and Row-based Replication

Source: [Percona Blog](https://www.percona.com/blog/working-of-mysql-replication-filters-when-using-statement-based-and-row-based-replication/)

Auteur source: [Mughees Ahmed](https://www.percona.com/blog/author/mughees-ahmed/)

Publication: 2023-01-23T14:14:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A couple of days ago I was creating an index on the source and when I checked the replica side it was not replicated, so I just wanted to explain how the replication filter may increase the complexity of your DBA operations. Replication occurs by reading events from the binary log of the source and … Continued

## Structure detectee

- H3: Examples
- H3: Example one
- H3: Example two
- H3: Example three
- H3: Example four
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Working of MySQL Replication Filters When Using Statement-based and Row-based Replication](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Replication-Filters.jpg)
- content / image: [MySQL Replication Filters](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Replication-Filters-300x168.jpg)

## Auteur source

Over 5 years of experience in Administration in MySQL databases using various tools and technologies. Keen on learning new database technologies and having very good analytical skills. Working knowledge of Red Hat Linux, UNIX, Solaris, AWS, and GCP.
