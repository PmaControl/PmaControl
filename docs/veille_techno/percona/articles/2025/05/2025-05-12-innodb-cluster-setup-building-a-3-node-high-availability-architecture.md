---
title: 'InnoDB Cluster Setup: Building a 3-Node High Availability Architecture'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-cluster-setup-building-a-3-node-high-availability-architecture/
  post_id: 34867
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2025-05-12T14:11:20'
published_at_gmt: '2025-05-12T14:11:20'
modified_at: '2026-03-26T20:25:33'
modified_at_gmt: '2026-03-26T20:25:33'
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
- Innodb cluster
- MySQL
- mysql-and-variants
tag_slugs:
- innodb-cluster
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Cluster-setup.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB Cluster Setup: Building a 3-Node High Availability Architecture

Source: [Percona Blog](https://www.percona.com/blog/innodb-cluster-setup-building-a-3-node-high-availability-architecture/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2025-05-12T14:11:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Modern applications need to be highly available and easy to scale. A three-node MySQL InnoDB Cluster—built on MySQL Group Replication and connected through MySQL Router—provides a reliable way to support critical workloads. To set up this architecture, you start by deploying three MySQL server instances. In this example, the nodes are assigned the following hostname-to-IP … Continued

## Structure detectee

- H2: Define Hostname-to-IP mappings
- H2: Verify MySQL installation on all nodes
- H2: Create the InnoDB cluster on the seed node
- H2: Add an additional two nodes to the InnoDB cluster
- H2: Validate the cluster after adding the second node
- H2: Add the third node (ArunClusterND3) to achieve fault tolerance

## Images et graphiques reperes

- featured / image: [InnoDB Cluster Setup: Building a 3-Node High Availability Architecture](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Cluster-setup.jpg)
- content / image: [three-node MySQL InnoDB Cluster](https://www.percona.com/wp-content/uploads/2026/03/Screenshot-2025-05-10-at-8.00.36-PM-227x300-1.png)
- content / image: [mysql performance tuning](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-1-1.png)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
