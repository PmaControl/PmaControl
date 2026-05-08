---
title: MySQL 8.0 Hot Rows with NOWAIT and SKIP LOCKED
source:
  name: Percona Blog
  url: https://www.percona.com/blog/mysql8-hot-rows-with-nowait-skip-locked/
  post_id: 18922
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2018-06-29T13:44:52'
published_at_gmt: '2018-06-29T13:44:52'
modified_at: '2026-05-05T19:18:18'
modified_at_gmt: '2026-05-05T19:18:18'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- application performance
- InnoDB locks
- MySQL 8.0
- row locking
- SELECT...FOR UPDATE
tag_slugs:
- application-performance
- innodb-locks
- mysql-8-0
- row-locking
- select-for-update
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mysql-8.0-hot_rows.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# MySQL 8.0 Hot Rows with NOWAIT and SKIP LOCKED

Source: [Percona Blog](https://www.percona.com/blog/mysql8-hot-rows-with-nowait-skip-locked/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2018-06-29T13:44:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In MySQL 8.0 there are two new features designed to support lock handling: NOWAIT and SKIP LOCKED. In this post, we’ll look at how MySQL 8.0 handles hot rows. Up until now, how have you handled locks that are part of an active transaction or are hot rows? It’s likely that you have the application … Continued

## Images et graphiques reperes

- featured / image: [MySQL 8.0 Hot Rows with NOWAIT and SKIP LOCKED](https://www.percona.com/wp-content/uploads/2026/03/mysql-8.0-hot_rows.jpg)
- content / image: [Table-300x140.png](https://www.percona.com/wp-content/uploads/2026/03/Table-300x140.png)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
