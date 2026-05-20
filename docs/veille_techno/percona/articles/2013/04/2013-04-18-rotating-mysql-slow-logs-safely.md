---
title: Rotating MySQL Slow Logs Safely
source:
  name: Percona Blog
  url: https://www.percona.com/blog/rotating-mysql-slow-logs-safely/
  post_id: 6852
source_author:
  name: Peter Boros
  slug: peter-boros
  url: https://www.percona.com/blog/author/peter-boros/
  website: ''
published_at: '2013-04-18T10:00:30'
published_at_gmt: '2013-04-18T10:00:30'
modified_at: '2026-04-28T21:51:47'
modified_at_gmt: '2026-04-28T21:51:47'
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
- copytruncate
- FLUSH LOGS
- Groupon
- Kyle Oppenheim
- Logrotate
- MySQL slow logs
- no copytruncate
- Peter Boros
tag_slugs:
- copytruncate
- flush-logs
- groupon
- kyle-oppenheim
- logrotate
- mysql-slow-logs
- no-copytruncate
- peter-boros
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Rotating MySQL Slow Logs Safely

Source: [Percona Blog](https://www.percona.com/blog/rotating-mysql-slow-logs-safely/)

Auteur source: [Peter Boros](https://www.percona.com/blog/author/peter-boros/)

Publication: 2013-04-18T10:00:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post is part two of two. Like part one, published Wednesday, this is a cross-post from Groupon’s engineering blog. Thanks again to Kyle Oppenheim at Groupon. In my last post, I described a solution for keeping the caches of a MySQL standby server hot using MySQL slow logs with long_query_time set to 0. … Continued

## Structure detectee

- H2: Do not use copytruncate
- H2: Use FLUSH LOGS instead of sending SIGHUP
- H2: Disable MySQL slow logs during rotation
- H2: Putting it all together

## Auteur source

Peter is a Principal Architect at Percona's European consulting team, his special interests are performance tuning and automation for large scale systems. Before Percona, he worked at Zuora, Dropbox, and Sun microsystems, also taught MySQL courses for Oracle University. He currently lives in Debrecen, Hungary with his wife and kids.
