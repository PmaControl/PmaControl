---
title: Analyzing the distribution of InnoDB log file writes
source:
  name: Percona Blog
  url: https://www.percona.com/blog/analyzing-the-distribution-of-innodb-log-file-writes/
  post_id: 2417
source_author:
  name: Baron Schwartz
  slug: baron
  url: https://www.percona.com/blog/author/baron/
  website: http://www.percona.com/
published_at: '2010-07-17T03:23:49'
published_at_gmt: '2010-07-17T03:23:49'
modified_at: '2026-05-04T20:49:34'
modified_at_gmt: '2026-05-04T20:49:34'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- InnoDB
tag_slugs:
- innodb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Analyzing the distribution of InnoDB log file writes

Source: [Percona Blog](https://www.percona.com/blog/analyzing-the-distribution-of-innodb-log-file-writes/)

Auteur source: [Baron Schwartz](https://www.percona.com/blog/author/baron/)

Publication: 2010-07-17T03:23:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I recently did a quick analysis of the distribution of writes to InnoDB’s log files. On a high-traffic commodity MySQL server running Percona XtraDB for a gaming workload (mostly inserts to the “moves” table), I used strace to gather statistics about how the log file writes are distributed in terms of write size. InnoDB writes … Continued

## Auteur source

Baron is the lead author of High Performance MySQL. He is a former Percona employee.
