---
title: Why base64-output=DECODE-ROWS does not print row events in MySQL binary logs
source:
  name: Percona Blog
  url: https://www.percona.com/blog/why-base64-outputdecode-rows-does-not-print-row-events-in-mysql-binary-logs/
  post_id: 9401
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2015-07-30T07:00:53'
published_at_gmt: '2015-07-30T07:00:53'
modified_at: '2026-04-28T22:22:32'
modified_at_gmt: '2026-04-28T22:22:32'
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
- base64-output=DECODE-ROWS
- binary logs
- InnoDB table
- MySQL
- Primary
- Sveta Smirnova
tag_slugs:
- base64-outputdecode-rows
- binary-logs
- innodb-table
- mysql
- primary
- sveta-smirnova
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-binary-logs.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Why base64-output=DECODE-ROWS does not print row events in MySQL binary logs

Source: [Percona Blog](https://www.percona.com/blog/why-base64-outputdecode-rows-does-not-print-row-events-in-mysql-binary-logs/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2015-07-30T07:00:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Lately, I saw many cases when users specified the option --base64 - output = DECODE - ROWS to print out a statement representation of row events in MySQL binary logs just to get nothing. Reason for this is obvious: option --base64 - output = DECODE - ROWS does not convert row events into its string representation, this is the job of the option -- verbose. But why users … Continued

## Structure detectee

- H2: MySQL binary logs

## Images et graphiques reperes

- featured / image: [Why base64-output=DECODE-ROWS does not print row events in MySQL binary logs](https://www.percona.com/wp-content/uploads/2026/03/MySQL-binary-logs.jpg)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
