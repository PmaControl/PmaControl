---
title: 'InnoDB Cluster: Set Up Router and Validate Failover'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-cluster-set-up-router-and-validate-failover/
  post_id: 34878
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2025-05-20T15:35:04'
published_at_gmt: '2025-05-20T15:35:04'
modified_at: '2026-03-26T20:25:31'
modified_at_gmt: '2026-03-26T20:25:31'
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
- InnoDB
- MySQL
- mysql-and-variants
tag_slugs:
- innodb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Cluster-Set-Up-Router-and-Validate-Failover.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# InnoDB Cluster: Set Up Router and Validate Failover

Source: [Percona Blog](https://www.percona.com/blog/innodb-cluster-set-up-router-and-validate-failover/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2025-05-20T15:35:04

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Setting up an InnoDB Cluster requires three key components: Group Replication, MySQL Shell, and MySQL Router. In the previous post, we covered the process of building a 3-node InnoDB Cluster. In this post, we shift our focus to configuring MySQL Router and validating failover functionality. Environment overview We are using three InnoDB Cluster nodes along … Continued

## Structure detectee

- H2: Environment overview
- H2: Bootstrapping MySQL Router
- H2: MySQL Router configuration details
- H2: Starting MySQL Router and verifying the process
- H2: Validating MySQL Router port bindings
- H1: Testing read/write and read-only routing
- H2: Failover test
- H3: Verifying InnoDB Cluster status with MySQL Shell
- H3: Promoting a secondary node to primary
- H3: Verifying InnoDB Cluster member status using SQL
- H3: Verifying the traffic routing
- H3: Verifying the cluster status after failover
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [InnoDB Cluster: Set Up Router and Validate Failover](https://www.percona.com/wp-content/uploads/2026/03/InnoDB-Cluster-Set-Up-Router-and-Validate-Failover.jpg)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
