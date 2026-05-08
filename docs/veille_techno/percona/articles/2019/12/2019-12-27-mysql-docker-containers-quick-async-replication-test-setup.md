---
title: 'MySQL Docker Containers: Quick Async Replication Test Setup'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql-docker-containers-quick-async-replication-test-setup/
  post_id: 21314
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2019-12-27T15:34:37'
published_at_gmt: '2019-12-27T15:34:37'
modified_at: '2026-04-27T21:26:40'
modified_at_gmt: '2026-04-27T21:26:40'
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
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- MySQL
- Percona Software
tag_slugs:
- mysql
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-Docker-Containers-Async.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL Docker Containers: Quick Async Replication Test Setup

Source: [Percona Blog](https://www.percona.com/blog/mysql-docker-containers-quick-async-replication-test-setup/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2019-12-27T15:34:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog discusses a few concepts about Docker and how we can use it to run a MySQL async replication environment. Docker is a tool designed to make it easier for developers and sysadmins to create/develop, configure, and run applications with containers. The container allows us to package all parts of the application it needs, … Continued

## Structure detectee

- H2: Custom Network Instead of the Default
- H2: Storage for Persisting the Data
- H2: Configuration File for the Primary Instance
- H2: Provisioning the Primary Instance
- H2: Docker Volume for Replication Storage
- H2: Configuration File for the Replica Instance
- H2: Provisioning the Replica Instance
- H2: Verify the Replication Status

## Images et graphiques reperes

- featured / image: [MySQL Docker Containers: Quick Async Replication Test Setup](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Docker-Containers-Async.png)
- content / image: [MySQL Docker Containers Async](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Docker-Containers-Async-300x168.png)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
