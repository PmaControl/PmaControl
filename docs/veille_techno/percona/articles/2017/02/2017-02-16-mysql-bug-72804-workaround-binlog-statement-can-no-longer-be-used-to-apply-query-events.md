---
title: 'MySQL Bug 72804 Workaround: "BINLOG statement can no longer be used to apply query events"'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-bug-72804-workaround-binlog-statement-can-no-longer-be-used-to-apply-query-events/
  post_id: 16291
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2017-02-16T23:39:31'
published_at_gmt: '2017-02-16T23:39:31'
modified_at: '2026-05-05T18:31:45'
modified_at_gmt: '2026-05-05T18:31:45'
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
- backup
- Binary Log
- MySQL
- pitr
- Replication
- troubleshooting
tag_slugs:
- backup
- binary-log
- mysql
- pitr
- replication
- troubleshooting
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Bug-72804.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Bug 72804 Workaround: "BINLOG statement can no longer be used to apply query events"

Source: [Percona Blog](https://www.percona.com/blog/mysql-bug-72804-workaround-binlog-statement-can-no-longer-be-used-to-apply-query-events/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2017-02-16T23:39:31

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at a workaround for MySQL bug 72804. Recently I worked on a ticket where a customer performed a point-in-time recovery PITR using a large set of binary logs. Normally we handle this by applying the last backup, then re-applying all binary logs created since the last backup. In the … Continued

## Images et graphiques reperes

- featured / image: [MySQL Bug 72804 Workaround: "BINLOG statement can no longer be used to apply query events"](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Bug-72804.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
