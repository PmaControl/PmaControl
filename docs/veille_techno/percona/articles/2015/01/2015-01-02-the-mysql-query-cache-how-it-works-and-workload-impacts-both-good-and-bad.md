---
title: 'The MySQL Query Cache: How it works, plus workload impacts'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-mysql-query-cache-how-it-works-and-workload-impacts-both-good-and-bad/
  post_id: 8881
source_author:
  name: Arunjith Aravindan
  slug: arunjith-aravindan
  url: https://www.percona.com/blog/author/arunjith-aravindan/
  website: http://www.percona.com/blog/
published_at: '2015-01-02T08:00:13'
published_at_gmt: '2015-01-02T08:00:13'
modified_at: '2026-04-28T22:15:12'
modified_at_gmt: '2026-04-28T22:15:12'
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
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- Arunjith Aravindan
- MySQL query cache
- Percona Server for MySQL
- Peter Zaitsev
- Primary
- query caching
- query optimization
tag_slugs:
- arunjith-aravindan
- mysql-query-cache
- percona-server
- peter-zaitsev
- primary
- query-caching
- query-optimization
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-query-cache.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# The MySQL Query Cache: How it works, plus workload impacts

Source: [Percona Blog](https://www.percona.com/blog/the-mysql-query-cache-how-it-works-and-workload-impacts-both-good-and-bad/)

Auteur source: [Arunjith Aravindan](https://www.percona.com/blog/author/arunjith-aravindan/)

Publication: 2015-01-02T08:00:13

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The MySQL query cache is one of the prominent features in MySQL and a vital part of query optimization. It is important to know how the MySQL query cache works, as it has the potential to cause significant performance improvements – or a slowdown – of your workload. The MySQL query cache is a global … Continued

## Structure detectee

- H2: The MySQL query cache is a global one shared among the sessions

## Images et graphiques reperes

- featured / image: [The MySQL Query Cache: How it works, plus workload impacts](https://www.percona.com/wp-content/uploads/2026/03/MySQL-query-cache.png)

## Auteur source

Arunjith Aravindan is a seasoned MySQL expert and passionate open-source advocate with over 15 years of experience in open-source technologies and MySQL consulting. He holds a Master’s degree in Computer Applications (MCA) and brings deep expertise in computer science, software development, and database management. Author of Hands-On MySQL Administration: Managing MySQL on Premises and in the Cloud, published by O’Reilly, and Mastering Amazon Relational Database Service for MySQL: Building and Configuring MySQL Instances, published by BPB. In 2014, Arunjith joined Percona, a leading provider of open source database solutions and services, as a consultant. In his role, he works closely with Managed Services customers to build and maintain reliable and efficient MySQL infrastructures. Arunjith's expertise includes performance analysis and optimization of MySQL, RDS, and Aurora, query optimization and auditing, troubleshooting, as well as high availability and scalability in MySQL and major version upgrades. Armed with a passion for open-source technologies, particularly MySQL and PostgreSQL, Arunjith is committed to staying up to date with the latest developments in the industry. He possesses exceptional technical communication skills and can articulate complex ideas to both technical and non-technical audiences. This is evident in his numerous blog posts and speaking engagements at various conferences such as Percona Live in Austin, TX; AWS Community Day in Kochi; Percona Conferences in Singapore and Bangalore; and Percona University in Hyderabad.
