---
title: 'Using Percona Toolkit to Alter Database Tables Online: A Controlled Approach'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-percona-toolkit-to-alter-database-tables-online-a-controlled-approach/
  post_id: 26381
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2022-12-12T14:22:20'
published_at_gmt: '2022-12-12T14:22:20'
modified_at: '2026-03-26T20:30:26'
modified_at_gmt: '2026-03-26T20:30:26'
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
- MySQL
- mysql-and-variants
- Percona Toolkit
- pt-online-schema-change
tag_slugs:
- mysql
- mysql-and-variants
- percona-toolkit
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Toolkit-to-Alter-Database-Tables-Online.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using Percona Toolkit to Alter Database Tables Online: A Controlled Approach

Source: [Percona Blog](https://www.percona.com/blog/using-percona-toolkit-to-alter-database-tables-online-a-controlled-approach/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2022-12-12T14:22:20

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Table modifications are a common task for database administrators. In this blog, I’ll explain how to alter tables online in a controlled manner that does not disrupt application users or cause application downtime. One of the tools in Percona Toolkit is pt-online-schema-change, a utility that alters the structure of a table without interfering with the … Continued

## Structure detectee

- H2: How to test the pt-online-schema-change command?
- H3: Dry-run test:
- H2: How to run the ALTER TABLE?
- H2: Can we pause the pt-online-schema-change execution? Yes!
- H2: Can we review the data and tables before swapping them? Yes!
- H4: Find the TRIGGERS:
- H4: Run the following SQL to swap the tables and remove the triggers.
- H4: Lastly, remove the triggers and the old table:
- H2: Wrap up

## Images et graphiques reperes

- featured / image: [Using Percona Toolkit to Alter Database Tables Online: A Controlled Approach](https://www.percona.com/wp-content/uploads/2026/03/Percona-Toolkit-to-Alter-Database-Tables-Online.png)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
