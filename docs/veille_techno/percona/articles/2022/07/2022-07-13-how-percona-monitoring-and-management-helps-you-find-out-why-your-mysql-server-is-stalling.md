---
title: How Percona Monitoring and Management Helps You Find Out Why Your MySQL Server Is Stalling
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-percona-monitoring-and-management-helps-you-find-out-why-your-mysql-server-is-stalling/
  post_id: 25802
source_author:
  name: Sveta Smirnova
  slug: sveta-smirnova
  url: https://www.percona.com/blog/author/sveta-smirnova/
  website: http://www.percona.com/blog/
published_at: '2022-07-13T13:45:26'
published_at_gmt: '2022-07-13T13:45:26'
modified_at: '2026-03-26T20:31:38'
modified_at_gmt: '2026-03-26T20:31:38'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:monitoring:2104
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
- tag:percona-monitoring-and-management:2166
- tag:pmm:2167
categories:
- Monitoring
- MySQL
- Percona Software
category_slugs:
- monitoring
- mysql
- percona-software
tags:
- database performance
- InnoDB
- MySQL
- mysql-and-variants
- Percona Monitoring and Management
- PMM
tag_slugs:
- database-performance
- innodb
- mysql
- mysql-and-variants
- percona-monitoring-and-management
- pmm
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-MySQL-Server-Is-Stalling.png
image_count: 10
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How Percona Monitoring and Management Helps You Find Out Why Your MySQL Server Is Stalling

Source: [Percona Blog](https://www.percona.com/blog/how-percona-monitoring-and-management-helps-you-find-out-why-your-mysql-server-is-stalling/)

Auteur source: [Sveta Smirnova](https://www.percona.com/blog/author/sveta-smirnova/)

Publication: 2022-07-13T13:45:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog, I will demonstrate how to use Percona Monitoring and Management (PMM) to find out the reason why the MySQL server is stalling. I will use only one typical situation for the MySQL server stall in this example, but the same dashboards, graphs, and principles will help you in all other cases. Nobody … Continued

## Structure detectee

- H3: Conclusion

## Images et graphiques reperes

- featured / image: [How Percona Monitoring and Management Helps You Find Out Why Your MySQL Server Is Stalling](https://www.percona.com/wp-content/uploads/2026/03/Percona-Monitoring-and-Management-MySQL-Server-Is-Stalling.png)
- content / image: [PMM MySQL Instance Summary](https://www.percona.com/wp-content/uploads/2026/03/MySQLInstance_normal-scaled.png)
- content / graph_or_chart: [Percona monitoring dashboard](https://www.percona.com/wp-content/uploads/2026/03/MySQLInstance_lock-scaled.png)
- content / image: [MySQL InnoDB Details](https://www.percona.com/wp-content/uploads/2026/03/InnoDBActivity_lock-scaled.png)
- content / image: [MySQL InnoDB Details PMM](https://www.percona.com/wp-content/uploads/2026/03/InnoDBIO_lock-scaled.png)
- content / image: [Percona InnoDB](https://www.percona.com/wp-content/uploads/2026/03/InnoDBLog_lock-1024x736.png)
- content / image: [InnoDB Logging Performance](https://www.percona.com/wp-content/uploads/2026/03/InnoDBPool_lock-scaled.png)
- content / image: [MySQL and InnoDB](https://www.percona.com/wp-content/uploads/2026/03/InnoDBTransactions_lock-1024x321.png)
- content / image: [MySQL and InnoDB problems](https://www.percona.com/wp-content/uploads/2026/03/InnoDBRow_lock-scaled.png)
- content / image: [Query Analytics (QAN)](https://www.percona.com/wp-content/uploads/2026/03/QAN-1-scaled.png)

## Auteur source

Sveta joined Percona in 2015. Her main professional interests are problem solving, working with tricky issues, bugs, finding patterns that can solve typical issues quicker and teaching others how to deal with MySQL issues, bugs and gotchas effectively. Before joining Percona Sveta worked as a Support Engineer in the MySQL Bugs Analysis Support Group in MySQL AB-Sun-Oracle. She is the author of the books "MySQL Troubleshooting" and "MySQL Cookbook, 4th Edition".
