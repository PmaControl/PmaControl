---
title: Smart Update Strategy in Percona Operator for MySQL Based on Percona XtraDB Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/smart-update-strategy-in-percona-kubernetes-operator-for-percona-xtradb-cluster/
  post_id: 22915
source_author:
  name: Tomislav Plavcic
  slug: tplavcic
  url: https://www.percona.com/blog/author/tplavcic/
  website: ''
published_at: '2020-08-11T19:11:21'
published_at_gmt: '2020-08-11T19:11:21'
modified_at: '2026-04-27T22:11:56'
modified_at_gmt: '2026-04-27T22:11:56'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- Cloud
- MySQL
- Percona Software
category_slugs:
- cloud
- mysql
- percona-software
tags:
- cloud
- Kubernetes
- MySQL
- mysql-and-variants
- Percona Software
- pxc
tag_slugs:
- cloud
- kubernetes
- mysql
- mysql-and-variants
- percona-software
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/smart-update-strategy-percona-kubernetes-opeerator.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Smart Update Strategy in Percona Operator for MySQL Based on Percona XtraDB Cluster

Source: [Percona Blog](https://www.percona.com/blog/smart-update-strategy-in-percona-kubernetes-operator-for-percona-xtradb-cluster/)

Auteur source: [Tomislav Plavcic](https://www.percona.com/blog/author/tplavcic/)

Publication: 2020-08-11T19:11:21

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In Percona Operator for MySQL based on Percona XtraDB Cluster versions prior to 1.5.0, there were two methods for upgrading PXC clusters, and both of these use built-in StatefulSet update strategies. The first one is manual (OnDelete update strategy) and the second one is semi-automatic (RollingUpdate strategy). Since the Kubernetes operator is about automating the … Continued

## Structure detectee

- H2: Smart Update Strategy
- H2: How Does it Work?
- H2: Configuration Options Inside cr.yaml File
- H2: Limitations
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Smart Update Strategy in Percona Operator for MySQL Based on Percona XtraDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/smart-update-strategy-percona-kubernetes-opeerator.png)
- content / image: [smart update strategy percona kubernetes opeerator](https://www.percona.com/wp-content/uploads/2026/03/smart-update-strategy-percona-kubernetes-opeerator-300x168.png)

## Auteur source

Tomislav joined Percona in 2014 as a Build/Release Engineer and later moved to a QA Engineer role. Previous to that he was working in the banking industry in different roles from testing and development to change management. He lives in Croatia with his wife and two kids and likes to spend free time riding a bicycle or homebrewing.
