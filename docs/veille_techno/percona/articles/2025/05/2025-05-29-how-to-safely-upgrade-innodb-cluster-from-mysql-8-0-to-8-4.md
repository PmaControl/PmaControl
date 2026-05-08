---
title: How to Safely Upgrade InnoDB Cluster From MySQL 8.0 to 8.4
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-safely-upgrade-innodb-cluster-from-mysql-8-0-to-8-4/
  post_id: 34936
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2025-05-29T13:17:43'
published_at_gmt: '2025-05-29T13:17:43'
modified_at: '2026-03-26T20:25:28'
modified_at_gmt: '2026-03-26T20:25:28'
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
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Upgrade-InnoDB-Cluster.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Safely Upgrade InnoDB Cluster From MySQL 8.0 to 8.4

Source: [Percona Blog](https://www.percona.com/blog/how-to-safely-upgrade-innodb-cluster-from-mysql-8-0-to-8-4/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2025-05-29T13:17:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, we continue from where we left off in the previous post, InnoDB Cluster Setup: Building a 3-Node High Availability Architecture, where we demonstrated how to set up a MySQL InnoDB Cluster with three nodes to achieve high availability. Here, we walk through the step-by-step process of performing a rolling upgrade of that … Continued

## Structure detectee

- H2: Step 1: Verify cluster health and identify node roles before upgrade
- H2: Step 2: Ensure all users are using caching_sha2_password for secure and seamless upgrades
- H2: Step 3: Prepare the secondary node (ArunClusterND3) for upgrade by configuring the MySQL APT repository
- H2: Step 4: Upgrading MySQL Shell and performing server upgrade checks on secondary node ArunClusterND3
- H2: Step 5: Resolving deprecated variable error
- H2: Step 6: Disable automatic group replication startup before upgrading
- H2: Step 7: Upgrading to MySQL Server 8.4
- H2: Step 8: Upgrading the primary node (ArunClusterND1) to MySQL 8.4.5
- H2: Step 9: Observing automatic primary re-election during upgrade
- H2: Step 10: Final node upgrade and cluster validation
- H2: Step 11: Notice: Metadata version mismatch warning
- H3: Solution: Upgrading cluster metadata
- H3: Conclusion: Upgrading a MySQL InnoDB cluster to version 8.4

## Images et graphiques reperes

- featured / image: [How to Safely Upgrade InnoDB Cluster From MySQL 8.0 to 8.4](https://www.percona.com/wp-content/uploads/2026/03/Upgrade-InnoDB-Cluster.jpg)
- content / image: [InnoDB Cluster Upgrade](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-3-1.png)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
