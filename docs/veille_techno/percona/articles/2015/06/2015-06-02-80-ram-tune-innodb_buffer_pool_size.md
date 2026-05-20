---
title: innodb_buffer_pool_size – Is 80% of RAM the right amount?
source:
  name: Percona Blog
  url: https://www.percona.com/blog/80-ram-tune-innodb_buffer_pool_size/
  post_id: 9289
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2015-06-02T10:00:07'
published_at_gmt: '2015-06-02T10:00:07'
modified_at: '2026-03-25T18:01:03'
modified_at_gmt: '2026-03-25T18:01:03'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- galera
- Heikki Tuuri
- innodb_buffer_pool_size
- Jay Janssen
- MySQL 5.7
- MySQL manual
- Peter Zaitsev
- Primary
- RAM
- Tuning
tag_slugs:
- galera
- heikki-tuuri
- innodb_buffer_pool_size
- jay-janssen
- mysql-5-7
- mysql-manual
- peter-zaitsev
- primary
- ram
- tuning
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/innodb_buffer_pool_size.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# innodb_buffer_pool_size – Is 80% of RAM the right amount?

Source: [Percona Blog](https://www.percona.com/blog/80-ram-tune-innodb_buffer_pool_size/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2015-06-02T10:00:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It seems these days if anyone knows anything about tuning InnoDB, it’s that you MUST tune your innodb_buffer_pool_size to 80% of your physical memory. This is such prolific tuning advice, it seems ingrained in many a DBA’s minds. The MySQL manual to this day refers to this rule, so who can blame the DBA? The … Continued

## Structure detectee

- H2: What uses the memory on your server?
- H2: A rule of thumb
- H2: The origins of the rule
- H2: How should you tune innodb_buffer_pool_size?
- H3: More resources:
- H4: Posts
- H4: Webinars
- H4: Presentations
- H4: Free eBooks
- H4: Tools

## Images et graphiques reperes

- featured / image: [innodb_buffer_pool_size – Is 80% of RAM the right amount?](https://www.percona.com/wp-content/uploads/2026/03/innodb_buffer_pool_size.jpg)

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
