---
title: Using the loose_ option prefix in my.cnf
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-the-loose_-option-prefix-in-my-cnf/
  post_id: 15800
source_author:
  name: Daniel Kowalewski
  slug: daniel-kowalewski
  url: https://www.percona.com/blog/author/daniel-kowalewski/
  website: https://www.percona.com
published_at: '2016-10-11T21:41:57'
published_at_gmt: '2016-10-11T21:41:57'
modified_at: '2026-05-05T18:20:53'
modified_at_gmt: '2026-05-05T18:20:53'
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
- .cnf file
- loose_ option
- MySQL
- Percona Server for MySQL
tag_slugs:
- cnf-file
- loose_-option
- mysql
- percona-server
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/shutterstock_103971533.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using the loose_ option prefix in my.cnf

Source: [Percona Blog](https://www.percona.com/blog/using-the-loose_-option-prefix-in-my-cnf/)

Auteur source: [Daniel Kowalewski](https://www.percona.com/blog/author/daniel-kowalewski/)

Publication: 2016-10-11T21:41:57

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’ll look at how to use the loose_ option prefix in my.cnf in MySQL. mysqld throws errors at startup – and refuses to start up – if a non-existent options are defined in the my.cnf file. For example: 2016-10-05 15:56:07 23864 [ERROR] /usr/sbin/mysqld: unknown variable 'bogus_option=1' 1 2016 - 10 - 05 15 : 56 : 07 23864 [ ERROR ] / usr / sbin / mysqld : unknown variable 'bogus_option=1' The MySQL manual has a solution: use the loose_ prefix option in … Continued

## Structure detectee

- H4: Use Case 1:
- H4: Use Case 2:
- H4: Use Case 3:

## Images et graphiques reperes

- featured / image: [Using the loose_ option prefix in my.cnf](https://www.percona.com/wp-content/uploads/2026/03/shutterstock_103971533.jpg)

## Auteur source

Daniel joined Percona in August of 2015. Previously, he earned a B.S. in Computer Science from the University of Colorado in 2006, and was a DBA there until he joined Percona. In addition to MySQL, Daniel also has experience with Oracle and Microsoft SQL Server, but he much prefers to stay in the MySQL world. Daniel lives near Denver, CO with his wife, three-year-old son, and dog. If you can't reach him, he's probably in the mountains hiking, camping, or trying to get lost.
