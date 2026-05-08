---
title: InnoDB ClusterSet Deployment With MySQLRouter
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-clusterset-deployment-with-mysqlrouter/
  post_id: 27298
source_author:
  name: Anil Joshi
  slug: anil-joshi
  url: https://www.percona.com/blog/author/anil-joshi/
  website: ''
published_at: '2023-08-17T14:29:22'
published_at_gmt: '2023-08-17T14:29:22'
modified_at: '2026-03-26T20:29:12'
modified_at_gmt: '2026-03-26T20:29:12'
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
- Innodb ClusterSet
- MySQL
- MySQL Group Replication
- mysql-and-variants
- Replication
tag_slugs:
- innodb-clusterset
- mysql
- mysql-group-replication
- mysql-and-variants
- replication
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/InnoDB-ClusterSet-Deployment-With-MySQLRouter.jpeg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB ClusterSet Deployment With MySQLRouter

Source: [Percona Blog](https://www.percona.com/blog/innodb-clusterset-deployment-with-mysqlrouter/)

Auteur source: [Anil Joshi](https://www.percona.com/blog/author/anil-joshi/)

Publication: 2023-08-17T14:29:22

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog post will cover the basic setup of the InnoDB ClusterSet environment, which provides disaster tolerance for InnoDB Cluster deployments by associating a primary InnoDB Cluster with one or more replicas in alternate locations/different data centers. InnoDB ClusterSet automatically manages replication from the primary cluster to the replica clusters via a specific ClusterSet Async replication … Continued

## Structure detectee

- H3: Environment
- H3: Let’s set up the first cluster (“cluster1”)
- H3: Let’s now proceed with the second cluster (“cluster2”) setup
- H3: Validating the connection route
- H3: Changing ClusterSet topology
- H3: Verifying the routing policy in the existing clusterset
- H3: Perform emergency failover
- H3: Summary

## Images et graphiques reperes

- featured / image: [InnoDB ClusterSet Deployment With MySQLRouter](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-ClusterSet-Deployment-With-MySQLRouter.jpeg)
- content / image: [InnoDB ClusterSet Deployment](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2023-08-04-at-5.47.24-PM-scaled.png)

## Auteur source

I am Anil Joshi, and I work for Percona as a support engineer. I've worked with some well-known Open Source database technologies (MySQL/MariaDB, MongoDB, and Redis) for almost ten years. I am keenly interested in learning new databases and writing database content.
