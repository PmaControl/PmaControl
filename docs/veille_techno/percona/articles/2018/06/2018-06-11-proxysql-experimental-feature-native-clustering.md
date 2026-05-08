---
title: 'ProxySQL Experimental Feature: Native ProxySQL Clustering'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proxysql-experimental-feature-native-clustering/
  post_id: 18729
source_author:
  name: Walter Garcia
  slug: walter-garcia
  url: https://www.percona.com/blog/author/walter-garcia/
  website: ''
published_at: '2018-06-11T12:18:24'
published_at_gmt: '2018-06-11T12:18:24'
modified_at: '2026-05-05T19:59:52'
modified_at_gmt: '2026-05-05T19:59:52'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:proxysql
categories:
- MySQL
- ProxySQL
category_slugs:
- mysql
- proxysql
tags:
- cluster
- High Availability
- ProxySQL
tag_slugs:
- cluster
- high-availability
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/proxysql_cluster.png
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ProxySQL Experimental Feature: Native ProxySQL Clustering

Source: [Percona Blog](https://www.percona.com/blog/proxysql-experimental-feature-native-clustering/)

Auteur source: [Walter Garcia](https://www.percona.com/blog/author/walter-garcia/)

Publication: 2018-06-11T12:18:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

ProxySQL 1.4.2 introduced native clustering, allowing several ProxySQL instances to communicate with and share configuration updates with each other. In this blog post, I’ll review this new feature and how we can start working with 3 nodes. Before I continue, let’s review two common methods to installing ProxySQL. ProxySQL as a centralized server This is … Continued

## Structure detectee

- H4: ProxySQL as a centralized server
- H4: ProxySQL on app instances
- H2: Native ProxySQL Clustering
- H3: How does it work?
- H3: How can I start testing this new feature?
- H3: What happens if some node is down?
- H3: Summary

## Images et graphiques reperes

- featured / image: [ProxySQL Experimental Feature: Native ProxySQL Clustering](https://www.percona.com/wp-content/uploads/2026/03/proxysql_cluster.png)
- content / image: [ProxySQL Install most common set up](https://www.percona.com/wp-content/uploads/2026/03/proxysql1.png)
- content / image: [ProxySQL Install master-slave](https://www.percona.com/wp-content/uploads/2026/03/proxysql_on_application_instances.png)
- content / image: [ProxySQL Cluster](https://www.percona.com/wp-content/uploads/2026/03/proxysql3.png)

## Auteur source

Walter has worked as a DBA since 2010 in few companies like social gaming company in Latin America and other company in Spain. He lives in Mendoza, Argentina, He likes play football and he is learning to play guitar in his free time
