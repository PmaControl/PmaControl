---
title: How MySQL ‘queries’ and ‘questions’ are measured
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-mysql-queries-and-questions-are-measured/
  post_id: 8202
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-05-29T10:00:23'
published_at_gmt: '2014-05-29T10:00:23'
modified_at: '2026-05-04T22:20:51'
modified_at_gmt: '2026-05-04T22:20:51'
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
- MySQL status variables
- Peter Zaitsev
- stored procedure calls
- Threads_Running
tag_slugs:
- mysql-status-variables
- peter-zaitsev
- stored-procedure-calls
- threads_running
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How MySQL ‘queries’ and ‘questions’ are measured

Source: [Percona Blog](https://www.percona.com/blog/how-mysql-queries-and-questions-are-measured/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-05-29T10:00:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL has status variables “questions” and “queries” which are rather close but also a bit different, making it confusing for many people. The manual describing it might not be very easy to understand: Queries The number of statements executed by the server. This variable includes statements executed within stored programs, unlike the Questions variable. It does not count COM_PING or COM_STATISTICS commands. Questions The number of statements executed by the server. This includes only statements sent to the server by clients and not statements executed within stored programs, unlike the Queries variable. This variable does not count COM_PING, COM_STATISTICS, COM_STMT_PREPARE, COM_STMT_CLOSE, or COM_STMT_RESET commands. 1 2 3 4 5 6 7 Queries The number of statements executed by the server . This variable includes statements executed within stored programs , unlike the Questions variabl...

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
