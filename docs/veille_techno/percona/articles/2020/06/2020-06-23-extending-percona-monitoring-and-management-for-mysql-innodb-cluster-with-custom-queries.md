---
title: Extending Percona Monitoring and Management for MySQL InnoDB Cluster with Custom Queries
source:
  name: Percona Blog
  url: https://www.percona.com/blog/extending-percona-monitoring-and-management-for-mysql-innodb-cluster-with-custom-queries/
  post_id: 22602
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2020-06-23T17:37:52'
published_at_gmt: '2020-06-23T17:37:52'
modified_at: '2026-05-05T17:51:44'
modified_at_gmt: '2026-05-05T17:51:44'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- XtraBackup
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-xtrabackup
- search:pmm
- search:xtrabackup
- tag:pmm:2167
categories:
- Monitoring
- MySQL
- Percona Software
category_slugs:
- monitoring
- mysql
- percona-software
tags:
- group replication
- Innodb cluster
- MySQL
- mysql-and-variants
- Percona Software
- PMM
tag_slugs:
- group-replication
- innodb-cluster
- mysql
- mysql-and-variants
- percona-software
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pmm-innodb-custom-queries.png
image_count: 5
graph_or_chart_count: 2
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Extending Percona Monitoring and Management for MySQL InnoDB Cluster with Custom Queries

Source: [Percona Blog](https://www.percona.com/blog/extending-percona-monitoring-and-management-for-mysql-innodb-cluster-with-custom-queries/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2020-06-23T17:37:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A few days ago, a customer got in touch asking how they could use Percona Monitoring and Management (PMM) to monitor the roles played by each node in an InnoDB cluster. More specifically, they wanted to check when one of the nodes changed its role from Primary to Secondary, or vice-versa. PMM allows for a … Continued

## Structure detectee

- H2: Deploying Our Test Cluster
- H2: Starting With a Regular Percona Server for MySQL
- H2: Creating an InnoDB Cluster
- H2: Adding a Second Node
- H2: Adding a Third Node
- H2: Deploying a PMM Server
- H2: Monitoring the InnoDB Cluster on PMM
- H2: Creating a Custom Query and Dashboard

## Images et graphiques reperes

- featured / image: [Extending Percona Monitoring and Management for MySQL InnoDB Cluster with Custom Queries](https://www.percona.com/wp-content/uploads/2026/03/pmm-innodb-custom-queries.png)
- content / image: [pmm innodb custom queries](https://www.percona.com/wp-content/uploads/2026/03/pmm-innodb-custom-queries-300x168.png)
- content / graph_or_chart: [Creating a custom dashboard](https://www.percona.com/wp-content/uploads/2026/03/custom_graph.png)
  Caption: Creating a Dashboard for our custom query
- content / graph_or_chart: [Dashboard showing change of nodes' roles](https://www.percona.com/wp-content/uploads/2026/03/graph-3.png)
  Caption: The custom dashboard showing the exact moment node2 is promoted to PRIMARY
- content / image: [Discrete panel displaying nodes' roles](https://www.percona.com/wp-content/uploads/2026/03/graph2.jpg)
  Caption: Using a Discrete panel to display the nodes’ roles

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
