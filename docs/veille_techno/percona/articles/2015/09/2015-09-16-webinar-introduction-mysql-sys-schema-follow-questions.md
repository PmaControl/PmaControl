---
title: 'Webinar: Introduction to MySQL SYS Schema follow up questions'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/webinar-introduction-mysql-sys-schema-follow-questions/
  post_id: 10033
source_author:
  name: Daniel Guzmán Burgos
  slug: daniel-guzman-burgos
  url: https://www.percona.com/blog/author/daniel-guzman-burgos/
  website: ''
published_at: '2015-09-16T21:57:53'
published_at_gmt: '2015-09-16T21:57:53'
modified_at: '2026-03-25T18:32:34'
modified_at_gmt: '2026-03-25T18:32:34'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- Insight for DBAs
- MySQL
- Webinars
category_slugs:
- insight-for-dbas
- mysql
- webinars
tags:
- Performance Schema
- sys schema
tag_slugs:
- performance-schema
- sys-schema
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Webinar: Introduction to MySQL SYS Schema follow up questions

Source: [Percona Blog](https://www.percona.com/blog/webinar-introduction-mysql-sys-schema-follow-questions/)

Auteur source: [Daniel Guzmán Burgos](https://www.percona.com/blog/author/daniel-guzman-burgos/)

Publication: 2015-09-16T21:57:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Thanks to all who attended my webinar Introduction to MySQL SYS Schema. This blog is for me to address the extra questions I didn’t have time to answer on the stream. Can i have the performance_schema enabled in 5.6 and then install the sys schema? Or they are one and the same? You need to … Continued

## Structure detectee

- H2: Can i have the performance_schema enabled in 5.6 and then install the sys schema? Or they are one and the same?
- H2: The installation of sys schema on primary database will be replicated to the slaves?
- H2: Can MySQL save the slow running query in any table?
- H2: How to see the query execution date & time from events_statements_current/history views in performance_schema?
- H2: When the Sys Schema views show certain stats for the queries, is there a execution time range for queries under evaluation or is it like all the queries executed until date?
- H2: I want to write the automated script to rebuild table or index. How to determine which table(s) or index(es) need to be rebuilt because of high fragmentation ratio?
- H2: Downside to using? Overhead?
- H2: What is the performance cost with regards to memory and io when using sys schema? Are there any tweaks or server variables with help the sys schema performing better?
- H2: For replicate how does sys schema record data?
- H2: Is sys schema built into any o the Percona releases?
- H2: Is it possible to use SYS schema in Galera 3 nodes cluster?
- H2: Can you create trending off information pulled from the Sys Schema? Full table scans over time, latency over time, that kind of thing?
- H2: How do I reset the performance data to start collecting from scratch?
- H2: Can we install SYS schema before 5.6?
- H2: Does sys support performance_schema from 5.0?
- H2: If you install the sys schema on one node of a Galera cluster will all the nodes get the Sys schema? Also, is the Sys schema cluster aware or does it only track the local node?

## Auteur source

Daniel studied Electronic Engineering, but quickly becomes interested in all data things. He has worked as a DBA since 2007 for several companies. Working for Percona since 2014, he is the PMM Tech Lead
