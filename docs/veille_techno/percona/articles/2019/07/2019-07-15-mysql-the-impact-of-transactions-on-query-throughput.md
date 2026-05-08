---
title: 'MySQL: The Impact of Transactions on Query Throughput'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-the-impact-of-transactions-on-query-throughput/
  post_id: 20569
source_author:
  name: Tibor Korocz
  slug: tibor-koroczpercona-com
  url: https://www.percona.com/blog/author/tibor-koroczpercona-com/
  website: ''
published_at: '2019-07-15T14:30:02'
published_at_gmt: '2019-07-15T14:30:02'
modified_at: '2026-04-27T21:18:39'
modified_at_gmt: '2026-04-27T21:18:39'
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
category_slugs:
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Transactions-1.png
image_count: 4
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL: The Impact of Transactions on Query Throughput

Source: [Percona Blog](https://www.percona.com/blog/mysql-the-impact-of-transactions-on-query-throughput/)

Auteur source: [Tibor Korocz](https://www.percona.com/blog/author/tibor-koroczpercona-com/)

Publication: 2019-07-15T14:30:02

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Recently I had a customer where every single query was running in a transaction, as well as even the simplest selects. Unfortunately, this is not unique and many connectors like Java love to do that. In their case, the Java connector changed autocommit=off for the connection itself at the beginning, and as these were permanent … Continued

## Structure detectee

- H3: Test Case
- H4: Disclaimer
- H3: Test results on MySQL 5.6
- H3: But what does that mean? My Selects are slower?
- H3: What is a transaction?
- H3: Testing different versions
- H3: Conclusion

## Images et graphiques reperes

- featured / graph_or_chart: [MySQL: The Impact of Transactions on Query Throughput](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Transactions-1.png)
- content / graph_or_chart: [Impact of Transactions on Query Throughput](https://www.percona.com/wp-content/uploads/2026/03/MySQL-The-Impact-of-Transactions-on-Query-Throughput.jpeg)
- content / image: [MySQL-5.6-1.png](https://www.percona.com/wp-content/uploads/2026/03/MySQL-5.6-1.png)
- content / image: [Impact-of-Transactions.png](https://www.percona.com/wp-content/uploads/2026/03/Impact-of-Transactions.png)

## Auteur source

Tibi joined Percona in 2015 as a Consultant. Before joining Percona, among many other things, he worked at the world’s largest car hire booking service as a Senior Database Engineer. He enjoys trying and working with the latest technologies and applications which can help or work with MySQL together. In his spare time he likes to spend time with his friends, travel around the world and play ultimate frisbee.
