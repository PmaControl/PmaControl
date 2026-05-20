---
title: How a set of queries can be killed in MySQL using Percona Toolkit’s pt-kill
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-a-set-of-queries-can-be-killed-in-mysql-using-pt-kill/
  post_id: 8745
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2014-11-19T15:18:49'
published_at_gmt: '2014-11-19T15:18:49'
modified_at: '2026-04-28T22:14:10'
modified_at_gmt: '2026-04-28T22:14:10'
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
- Arunjith Aravindan
- MySQL
- Percona Toolkit
- Primary
- pt-kill
- slow queries
tag_slugs:
- arunjith-aravindan
- mysql
- percona-toolkit
- primary
- pt-kill
- slow-queries
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How a set of queries can be killed in MySQL using Percona Toolkit’s pt-kill

Source: [Percona Blog](https://www.percona.com/blog/how-a-set-of-queries-can-be-killed-in-mysql-using-pt-kill/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2014-11-19T15:18:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

You might have encountered situations where you had to kill some specific select queries that were running for long periods and choking the database. This post will go into more detail with an example of report query offloading. Report query (select) offloading to a slave server is a common practice to reduce the workload of … Continued

## Structure detectee

- H2: Scripts and Regex vs Percona’s pt-kill

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
