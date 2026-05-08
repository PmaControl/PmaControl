---
title: Provisioning Replication With Clone Plugin
source:
  name: Percona Blog
  url: https://www.percona.com/blog/provisioning-replication-with-clone-plugin/
  post_id: 27313
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2023-08-08T13:58:14'
published_at_gmt: '2023-08-08T13:58:14'
modified_at: '2026-03-26T20:29:15'
modified_at_gmt: '2026-03-26T20:29:15'
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
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Provisioning-Replication-With-Clone-Plugin.jpeg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Provisioning Replication With Clone Plugin

Source: [Percona Blog](https://www.percona.com/blog/provisioning-replication-with-clone-plugin/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2023-08-08T13:58:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The clone plugin was introduced in MySQL 8.0.17 and offers a convenient method for cloning data from either a local or remote MySQL server instance. This cloning process creates a physical snapshot of the data stored in InnoDB, including schemas, tables, tablespaces, and data dictionary metadata. The clone plugin allows for easy provisioning of MySQL … Continued

## Structure detectee

- H2: Installation of the Clone Plugin
- H2: Controlling the plugin activation state
- H2: Creating a user with required privileges
- H2: Receiver instance
- H2: Configuring clone valid donor list
- H2: Cloning an instance from a donor server
- H2: Monitoring the cloning progress
- H2: Set up replication after cloning
- H2: Local cloning
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Provisioning Replication With Clone Plugin](https://www.percona.com/wp-content/uploads/2026/03/Provisioning-Replication-With-Clone-Plugin.jpeg)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
