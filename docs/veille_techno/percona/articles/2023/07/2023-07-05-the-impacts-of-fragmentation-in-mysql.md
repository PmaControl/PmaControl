---
title: The Impacts of Fragmentation in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-impacts-of-fragmentation-in-mysql/
  post_id: 27184
source_author:
  name: Pep Pla
  slug: pep-pla
  url: https://www.percona.com/blog/author/pep-pla/
  website: ''
published_at: '2023-07-05T13:14:06'
published_at_gmt: '2023-07-05T13:14:06'
modified_at: '2026-03-26T20:29:30'
modified_at_gmt: '2026-03-26T20:29:30'
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
- InnoDB
- MySQL
- mysql-and-variants
- Performance
tag_slugs:
- innodb
- mysql
- mysql-and-variants
- performance
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/fragmentation-in-MySQL.png
image_count: 14
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The Impacts of Fragmentation in MySQL

Source: [Percona Blog](https://www.percona.com/blog/the-impacts-of-fragmentation-in-mysql/)

Auteur source: [Pep Pla](https://www.percona.com/blog/author/pep-pla/)

Publication: 2023-07-05T13:14:06

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Fragmentation is a common concern in some database systems. Highly fragmented tables can affect performance and resource allocation. But reducing fragmentation often involves rebuilding the table completely. This blog post will discuss fragmentation and its impact on InnoDB. What is fragmentation? We say that something is fragmented when it is formed by parts that are … Continued

## Structure detectee

- H2: What is fragmentation?
- H2: The principle of locality
- H2: How does fragmentation affect the locality of data?
- H2: Fragmentation in InnoDB
- H2: To split or not to split, that is the question.
- H2: Random vs. Sequential inserts, effect on fragmentation
- H2: Random inserts and deletes
- H2: Additional causes of fragmentation
- H2: Detecting fragmentation
- H2: Measuring page splits
- H2: InnoDB Ruby
- H2: Reducing fragmentation
- H2: Innodb_fill_factor
- H2: Random insert and delete tests and recommended fill factor
- H3: Total space file size
- H3: Page splits and merges
- H3: Fragmentation maps
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [The Impacts of Fragmentation in MySQL](https://www.percona.com/wp-content/uploads/2026/03/fragmentation-in-MySQL.png)
- content / image: [InnoDB empty leaf page](https://www.percona.com/wp-content/uploads/2026/03/Empty-InnoDB-Leaf.png)
- content / image: [InnoDB Leaf with data](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Leaf-with-data.png)
- content / image: [InnoDB sequential insertion pattern](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-sequential-insertion-pattern.png)
- content / image: [InnoDB random insertion pattern](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-random-insertion-pattern.png)
- content / image: [InnoDB sequential primary key insertion impact on storage allocation](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-sequential-primary-key-insertion.png)
- content / image: [Impact of random primary key insertion on storage allocation](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-random-primary-key-insertion.png)
- content / image: [Impact of InnoDB sequential primary key insertion and deletion on storage allocation](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-sequential-primary-key-insertion-and-deletion.png)
- content / image: [Impact of InnoDB random primary key insertion and deletion on storage allocation.](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-random-primary-key-insertion-and-deletion.png)
- content / image: [InnoDB Ruby](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Ruby.png)
- content / image: [Initial and final size of innodb file after multiple insertions and deletions.](https://www.percona.com/wp-content/uploads/2026/03/Initial-and-final-size.png)
- content / image: [Fill-factor-75.png](https://www.percona.com/wp-content/uploads/2026/03/Fill-factor-75.png)
- content / image: [Fill-factor-83.png](https://www.percona.com/wp-content/uploads/2026/03/Fill-factor-83.png)
- content / image: [Fill-factor-100.png](https://www.percona.com/wp-content/uploads/2026/03/Fill-factor-100.png)

## Auteur source

Pep has been working with databases all his life. Born in a small village by the Mediterranean, he currently lives in Barcelona. He loves tech, traveling, good food, music and, all things NASA. He hates talking about himself in the third person and has a particular sense of humor. Happily married, he is the father of three boys and three cats.
