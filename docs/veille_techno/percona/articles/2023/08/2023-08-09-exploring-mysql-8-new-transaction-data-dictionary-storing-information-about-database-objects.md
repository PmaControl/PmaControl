---
title: 'Exploring MySQL 8 New Transaction Data Dictionary: Storing Information About Database Objects'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/exploring-mysql-8-new-transaction-data-dictionary-storing-information-about-database-objects/
  post_id: 27359
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2023-08-09T12:33:48'
published_at_gmt: '2023-08-09T12:33:48'
modified_at: '2026-03-26T20:29:15'
modified_at_gmt: '2026-03-26T20:29:15'
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
- MySQL
- mysql-and-variants
tag_slugs:
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-8-Transaction-Data-Dictionary.jpeg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Exploring MySQL 8 New Transaction Data Dictionary: Storing Information About Database Objects

Source: [Percona Blog](https://www.percona.com/blog/exploring-mysql-8-new-transaction-data-dictionary-storing-information-about-database-objects/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2023-08-09T12:33:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL 8 brought a significant architectural transformation by replacing the traditional MyISAM-based system tables with the Transaction Data Dictionary (TDD), a more efficient and reliable approach. This upgrade has vastly improved the management and storage of metadata, resulting in better reliability and scalability for various database objects. In this blog post, we will explore the … Continued

## Structure detectee

- H2: Benefits of the Transaction Data Dictionary
- H2: Storing information about database objects in TDD
- H2: Data dictionary views
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Exploring MySQL 8 New Transaction Data Dictionary: Storing Information About Database Objects](https://www.percona.com/wp-content/uploads/2026/03/MySQL-8-Transaction-Data-Dictionary.jpeg)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
