---
title: How to Find Duplicate, Unused, and Invisible Indexes in MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/duplicate-redundant-and-invisible-indexes/
  post_id: 26461
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2024-04-02T13:55:28'
published_at_gmt: '2024-04-02T13:55:28'
modified_at: '2026-03-26T20:26:31'
modified_at_gmt: '2026-03-26T20:26:31'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- Duplicate Index Detection
- invisible indexes
- MySQL
- MySQL Indexes
tag_slugs:
- duplicate-index-detection
- invisible-indexes
- mysql
- mysql-indexes
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mysql-find-unused-indexes.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Find Duplicate, Unused, and Invisible Indexes in MySQL

Source: [Percona Blog](https://www.percona.com/blog/duplicate-redundant-and-invisible-indexes/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2024-04-02T13:55:28

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This blog was originally published in January 2023 and was updated in April 2024. MySQL index is a data structure used to optimize the performance of database queries at the expense of additional writes and storage space to keep the index data structure up to date. It is used to quickly locate data without having … Continued

## Structure detectee

- H2: How to find the duplicate MySQL indexes
- H3: For example:
- H2: How to find the unused MySQL indexes
- H3: For example:
- H2: Use invisible indexes before deleting MySQL indexes
- H2: The Benefits of Removing Duplicate and Unused Indexes in MySQL
- H2: FAQs
- H3: Why are unused indexes in MySQL bad?
- H3: Why are duplicate indexes in MySQL bad?
- H3: How do you make an index invisible?
- H3: What are the advantages of making an index invisible?

## Images et graphiques reperes

- featured / image: [How to Find Duplicate, Unused, and Invisible Indexes in MySQL](https://www.percona.com/wp-content/uploads/2026/03/mysql-find-unused-indexes.jpg)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
