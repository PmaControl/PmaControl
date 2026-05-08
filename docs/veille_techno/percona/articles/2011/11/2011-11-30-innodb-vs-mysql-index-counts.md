---
title: Innodb vs MySQL index counts
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-vs-mysql-index-counts/
  post_id: 3217
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2011-11-30T00:48:27'
published_at_gmt: '2011-11-30T00:48:27'
modified_at: '2026-03-23T22:10:32'
modified_at_gmt: '2026-03-23T22:10:32'
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
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Innodb vs MySQL index counts

Source: [Percona Blog](https://www.percona.com/blog/innodb-vs-mysql-index-counts/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2011-11-30T00:48:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I had a customer recently who a few strange errors in their mysqld.err log: [ERROR] Table database_name/table_name contains 8 indexes inside InnoDB, which is different from the number of indexes 7 defined in the MySQL 1 [ ERROR ] Table database_name / table_name contains 8 indexes inside InnoDB , which is different from the number of indexes 7 defined in the MySQL This customer was running Percona Server 5.1 and they got this error on two tables during a maintenance window when they were adding indexes to the same tables. We had a suspicion that it had something to do with Fast index creation … Continued

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
