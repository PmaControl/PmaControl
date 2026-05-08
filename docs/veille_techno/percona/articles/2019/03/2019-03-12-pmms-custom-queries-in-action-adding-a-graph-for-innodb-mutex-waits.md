---
title: 'PMM’s Custom Queries in Action: Adding a Graph for InnoDB mutex waits'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pmms-custom-queries-in-action-adding-a-graph-for-innodb-mutex-waits/
  post_id: 44481
source_author:
  name: Daniel Guzmán Burgos
  slug: daniel-guzman-burgos
  url: https://www.percona.com/blog/author/daniel-guzman-burgos/
  website: ''
published_at: '2019-03-12T00:02:08'
published_at_gmt: '2019-03-12T00:02:08'
modified_at: '2026-04-28T00:04:26'
modified_at_gmt: '2026-04-28T00:04:26'
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
- search:pmm
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/04/Screen-Shot-2019-03-05-at-18.29.35-1024x525-1.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PMM’s Custom Queries in Action: Adding a Graph for InnoDB mutex waits

Source: [Percona Blog](https://www.percona.com/blog/pmms-custom-queries-in-action-adding-a-graph-for-innodb-mutex-waits/)

Auteur source: [Daniel Guzmán Burgos](https://www.percona.com/blog/author/daniel-guzman-burgos/)

Publication: 2019-03-12T00:02:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

One of the great things about Percona Monitoring and Management (PMM) is its flexibility. An example of that is how one can go beyond the exporters to collect data. One approach to achieve that is using textfile collectors, as explained in 1.15.0, PMM provides user the ability to take a SQL SELECT statement and … Continued

## Structure detectee

- H2: Custom Queries
- H4: What is it?
- H4: How do I enable that feature?
- H4: Where is the configuration file located?
- H4: How often is data being collected?
- H2: InnoDB Mutex monitoring
- H2: YAML Configuration File
- H2: Creating the graph in Grafana
- H2: Summary

## Images et graphiques reperes

- content / image: [Screen-Shot-2019-03-05-at-18.29.35-1024x525-1.png](https://www.percona.com/wp-content/uploads/2026/04/Screen-Shot-2019-03-05-at-18.29.35-1024x525-1.png)

## Auteur source

Daniel studied Electronic Engineering, but quickly becomes interested in all data things. He has worked as a DBA since 2007 for several companies. Working for Percona since 2014, he is the PMM Tech Lead
