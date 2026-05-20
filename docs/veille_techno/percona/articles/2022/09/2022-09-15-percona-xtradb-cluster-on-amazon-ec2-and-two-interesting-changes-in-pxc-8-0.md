---
title: Percona XtraDB Cluster on Amazon EC2 and Two Interesting Changes in PXC 8.0
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-on-amazon-ec2-and-two-interesting-changes-in-pxc-8-0/
  post_id: 26012
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2022-09-15T12:02:13'
published_at_gmt: '2022-09-15T12:02:13'
modified_at: '2026-03-26T20:31:10'
modified_at_gmt: '2026-03-26T20:31:10'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- ProxySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-xtrabackup
- search:proxysql
- search:xtrabackup
categories:
- Cloud
- MySQL
- Percona Software
category_slugs:
- cloud
- mysql
- percona-software
tags:
- Amazon S3
- Amazon’s AWS
- MySQL
- mysql-and-variants
- Percona XtraDB Cluster
tag_slugs:
- amazon-s3
- amazons-aws
- mysql
- mysql-and-variants
- percona-xtradb-cluster
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-Amazon-EC2.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster on Amazon EC2 and Two Interesting Changes in PXC 8.0

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-on-amazon-ec2-and-two-interesting-changes-in-pxc-8-0/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2022-09-15T12:02:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This article outlines the basic configurations for setting up and deploying Percona XtraDB Cluster 8.0 (PXC) on Amazon EC2, as well as what is new in the setup compared to Percona XtraDB Cluster 5.7. What is Percona XtraDB Cluster an ideal fit for? Percona XtraDB Cluster is a cost-effective, high-performance clustering solution for mission-critical data. … Continued

## Structure detectee

- H2: What is Percona XtraDB Cluster an ideal fit for?
- H2: How is a three-node cluster configured in an EC2 environment?
- H2: Before starting the nodes, update the basic variables listed below for the nodes
- H2: How is the first node bootstrapped ?
- H2: How can the cluster’s remaining nodes be joined?
- H2: Additional supporting factors

## Images et graphiques reperes

- featured / image: [Percona XtraDB Cluster on Amazon EC2 and Two Interesting Changes in PXC 8.0](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-Amazon-EC2.png)
- content / image: [Percona XtraDB Cluster on Amazon EC2](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-on-Amazon-EC2-300x157.png)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
