---
title: 'Seamless Table Modifications: Leveraging pt-online-schema-change for Online Alterations'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/seamless-table-modifications-leveraging-pt-online-schema-change-for-online-alterations/
  post_id: 28466
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2024-05-13T13:19:30'
published_at_gmt: '2024-05-13T13:19:30'
modified_at: '2026-03-26T20:26:23'
modified_at_gmt: '2026-03-26T20:26:23'
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
- tag:percona-toolkit:378
categories:
- Insight for DBAs
- MySQL
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- alter table
- MySQL
- MySQL ALTER table
- mysql-and-variants
- Online Alter
- Percona Toolkit
- pt-online-schema-change
tag_slugs:
- alter-table
- mysql
- mysql-alter-table
- mysql-and-variants
- online-alter
- percona-toolkit
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Seamless-Table-Modifications-in-MySQL-pt-online-schema-change.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Seamless Table Modifications: Leveraging pt-online-schema-change for Online Alterations

Source: [Percona Blog](https://www.percona.com/blog/seamless-table-modifications-leveraging-pt-online-schema-change-for-online-alterations/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2024-05-13T13:19:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Table modifications are a routine task for database administrators. The blog post Using Percona Toolkit to Alter Database Tables Online: A Controlled Approach provides insights into the process of altering tables online in a controlled manner, ensuring uninterrupted access for application users and preventing application downtime. We will focus here on utilizing the powerful “pt-online-schema-change” … Continued

## Structure detectee

- H3: Online table alterations with pt-online-schema-change:
- H3: Creation of an empty copy:
- H4: Row transfer and synchronization:
- H4: Replacement of the original table:
- H3: Benefits and considerations:
- H3: However, administrators should also consider the following:
- H3: Pre-flight checks
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Seamless Table Modifications: Leveraging pt-online-schema-change for Online Alterations](https://www.percona.com/wp-content/uploads/2026/03/Seamless-Table-Modifications-in-MySQL-pt-online-schema-change.jpg)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
