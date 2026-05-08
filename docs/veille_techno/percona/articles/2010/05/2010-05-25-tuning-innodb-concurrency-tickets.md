---
title: Tuning InnoDB Concurrency Tickets
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tuning-innodb-concurrency-tickets/
  post_id: 2326
source_author:
  name: Ryan Lowe
  slug: ryanalowe
  url: https://www.percona.com/blog/author/ryanalowe/
  website: http://www.percona.com/blog/
published_at: '2010-05-25T03:41:20'
published_at_gmt: '2010-05-25T03:41:20'
modified_at: '2026-04-28T21:13:15'
modified_at_gmt: '2026-04-28T21:13:15'
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
- MySQL
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
tags:
- Concurrency Tickets
- InnoDB
- InnoDB Concurrency
- innodb_concurrency_tickets
- Tuning
tag_slugs:
- concurrency-tickets
- innodb
- innodb-concurrency
- innodb_concurrency_tickets
- tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Screen-shot-2010-05-23-at-11.47.07-PM.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Tuning InnoDB Concurrency Tickets

Source: [Percona Blog](https://www.percona.com/blog/tuning-innodb-concurrency-tickets/)

Auteur source: [Ryan Lowe](https://www.percona.com/blog/author/ryanalowe/)

Publication: 2010-05-25T03:41:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

InnoDB has an oft-unused parameter innodb_concurrency_tickets that seems widely misunderstood. From the docs: “The number of threads that can enter InnoDB concurrently is determined by the innodb_thread_concurrency variable. A thread is placed in a queue when it tries to enter InnoDB if the number of threads has already reached the concurrency limit. When a thread … Continued

## Images et graphiques reperes

- featured / image: [Tuning InnoDB Concurrency Tickets](https://www.percona.com/wp-content/uploads/2026/03/Screen-shot-2010-05-23-at-11.47.07-PM.png)

## Auteur source

Ryan was a principal consultant and team manager at Percona until July 2014. He has experience with many database technologies in industries such as health care, telecommunications, and social networking.
