---
title: The Transaction Behavior Impact of innodb_rollback_on_timeout in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-transaction-behavior-impact-of-innodb_rollback_on_timeout-in-mysql/
  post_id: 22806
source_author:
  name: Sri Sakthivel
  slug: sri-sakthivel
  url: https://www.percona.com/blog/author/sri-sakthivel/
  website: ''
published_at: '2020-07-27T19:04:21'
published_at_gmt: '2020-07-27T19:04:21'
modified_at: '2026-04-27T22:08:50'
modified_at_gmt: '2026-04-27T22:08:50'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Benchmarks
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- Benchmarks
- InnoDB
- insight for DBAs
- insight for developers
- MySQL
- mysql-and-variants
tag_slugs:
- benchmarks
- innodb
- insight-for-dbas
- insight-for-developers
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/innodb_rollback_on_timeout.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The Transaction Behavior Impact of innodb_rollback_on_timeout in MySQL

Source: [Percona Blog](https://www.percona.com/blog/the-transaction-behavior-impact-of-innodb_rollback_on_timeout-in-mysql/)

Auteur source: [Sri Sakthivel](https://www.percona.com/blog/author/sri-sakthivel/)

Publication: 2020-07-27T19:04:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I would say that innodb_rollback_on_timeout is a very important parameter. In this blog, I am going to explain “innodb_rollback_on_timeout” and how it affects the transaction behavior at the MySQL level. I describe two scenarios with practical tests, as it would be helpful to understand this parameter better. What is innodb_rollback_on_timeout? The parameter Innodb_rollback_on_timeout will control … Continued

## Structure detectee

- H2: What is innodb_rollback_on_timeout?
- H2: Test Environment
- H3: Common Steps
- H2: Scenario 1 – Transaction with Innodb_rollback_on_timeout = OFF
- H3: Summary:
- H2: Scenario 2 – Transaction with Innodb_rollback_on_timeout = ON
- H3: Summary:
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [The Transaction Behavior Impact of innodb_rollback_on_timeout in MySQL](https://www.percona.com/wp-content/uploads/2026/03/innodb_rollback_on_timeout.png)
- content / image: [innodb_rollback_on_timeout](https://www.percona.com/wp-content/uploads/2026/03/innodb_rollback_on_timeout-300x157.png)

## Auteur source

Oracle certified MySQL DBA. Working on MySQL and related technologies to ensures database performance. Handling multi client projects round the clock. Currently focusing on MySQL Cluster technologies like Galera and Group replication/InnoDB cluster. Active MySQL Blogger.
