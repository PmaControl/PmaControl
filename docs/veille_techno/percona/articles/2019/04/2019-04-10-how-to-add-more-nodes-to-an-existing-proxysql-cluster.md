---
title: How to Add More Nodes to an Existing ProxySQL Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-add-more-nodes-to-an-existing-proxysql-cluster/
  post_id: 20185
source_author:
  name: Walter Garcia
  slug: walter-garcia
  url: https://www.percona.com/blog/author/walter-garcia/
  website: ''
published_at: '2019-04-10T11:52:16'
published_at_gmt: '2019-04-10T11:52:16'
modified_at: '2026-05-05T17:33:17'
modified_at_gmt: '2026-05-05T17:33:17'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/proxysql_on_application_instances.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Add More Nodes to an Existing ProxySQL Cluster

Source: [Percona Blog](https://www.percona.com/blog/how-to-add-more-nodes-to-an-existing-proxysql-cluster/)

Auteur source: [Walter Garcia](https://www.percona.com/blog/author/walter-garcia/)

Publication: 2019-04-10T11:52:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my previous post, some time ago, I wrote about the new cluster feature of ProxySQL. For that post, we were working with three nodes, now we’ll work with even more! If you’ve installed one ProxySQL per application instance and would like to work up to more, then this post is for you. If this … Continued

## Structure detectee

- H2: From a new proxysql cluster installation
- H2: How to configure the global_variables table
- H2: Configure “proxysql_servers” table
- H3: How can we check if there are errors in the synchronization process?
- H3: How do you fix this?
- H3: What happens if the new nodes already exist in the proxysql_servers table of the current cluster?
- H3: How does ProxySQL monitor other ProxySQL nodes to sync locally?
- H4: To perform the syncing process:
- H2: Summary

## Images et graphiques reperes

- featured / image: [How to Add More Nodes to an Existing ProxySQL Cluster](https://www.percona.com/wp-content/uploads/2026/03/proxysql_on_application_instances.png)

## Auteur source

Walter has worked as a DBA since 2010 in few companies like social gaming company in Latin America and other company in Spain. He lives in Mendoza, Argentina, He likes play football and he is learning to play guitar in his free time
