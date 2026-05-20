---
title: InnoDB locks and transaction isolation level
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-locks-and-transaction-isolation-level/
  post_id: 15335
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2016-06-17T18:49:17'
published_at_gmt: '2016-06-17T18:49:17'
modified_at: '2026-05-05T18:09:03'
modified_at_gmt: '2026-05-05T18:09:03'
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
tags:
- InnoDB
- Locking
- locking issues
- locks
- Transaction isolation
tag_slugs:
- innodb
- locking
- locking-issues
- locks
- transaction-isolation
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Slow-Queries-small-e1472850618366.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB locks and transaction isolation level

Source: [Percona Blog](https://www.percona.com/blog/innodb-locks-and-transaction-isolation-level/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2016-06-17T18:49:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

What is the difference between InnoDB locks and transaction isolation level? We’ll discuss it in this post. Recently I received a question from a user about one of my earlier blog posts. Since it wasn’t sent as a comment, I will answer it here. The question: > I am reading your article: > https://www.percona.com/resources/technical-presentations/troubleshooting-locking-issues-percona-mysql-webinar > … Continued

## Images et graphiques reperes

- featured / image: [InnoDB locks and transaction isolation level](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Slow-Queries-small-e1472850618366.jpg)
- content / image: [InnoDB locks and transaction isolation](https://www.percona.com/wp-content/uploads/2026/03/Troubleshooting-Slow-Queries-small-300x249.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
