---
title: 'A Tale of Two Databases: How PostgreSQL and MySQL Handle Torn Pages'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/a-tale-of-two-databases-how-postgresql-and-mysql-handle-torn-pages/
  post_id: 35060
source_author:
  name: Pep Pla
  slug: pep-pla
  url: https://www.percona.com/blog/author/pep-pla/
  website: ''
published_at: '2025-07-03T12:00:48'
published_at_gmt: '2025-07-03T12:00:48'
modified_at: '2026-03-26T20:25:24'
modified_at_gmt: '2026-03-26T20:25:24'
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
- PostgreSQL
category_slugs:
- insight-for-dbas
- mysql
- postgresql
tags:
- checkpoint
- MySQL
- PostgreSQL
- redo logs
- torn pages
- wal files
tag_slugs:
- checkpoint
- mysql
- postgresql
- redo-logs
- torn-pages
- wal-files
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/How-PostgreSQL-and-MySQL-Handle-Torn-Pages.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A Tale of Two Databases: How PostgreSQL and MySQL Handle Torn Pages

Source: [Percona Blog](https://www.percona.com/blog/a-tale-of-two-databases-how-postgresql-and-mysql-handle-torn-pages/)

Auteur source: [Pep Pla](https://www.percona.com/blog/author/pep-pla/)

Publication: 2025-07-03T12:00:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Welcome to this first installment of the blog series, which explores how PostgreSQL and MySQL deal with different aspects of relational databases. This post is about how to handle torn pages. As a long-time open source database administrator, I have always been fascinated by the differences in how these two databases handle various challenges and … Continued

## Structure detectee

- H2: What is a torn page?
- H2: How do we handle a torn page?
- H2: Let’s synchronize our watches… I mean disks
- H2: Another brick in the WAL
- H2: It is a checkpoint, Charlie!
- H2: A torn page is born
- H2: How does PostgreSQL handle torn pages?
- H2: How does MySQL (InnoDB) handle torn pages?
- H3: Final thoughts

## Images et graphiques reperes

- featured / image: [A Tale of Two Databases: How PostgreSQL and MySQL Handle Torn Pages](https://www.percona.com/wp-content/uploads/2026/03/How-PostgreSQL-and-MySQL-Handle-Torn-Pages.jpg)

## Auteur source

Pep has been working with databases all his life. Born in a small village by the Mediterranean, he currently lives in Barcelona. He loves tech, traveling, good food, music and, all things NASA. He hates talking about himself in the third person and has a particular sense of humor. Happily married, he is the father of three boys and three cats.
