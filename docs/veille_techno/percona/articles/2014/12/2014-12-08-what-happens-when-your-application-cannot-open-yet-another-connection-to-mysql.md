---
title: Application Cannot Open Another Connection to MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/what-happens-when-your-application-cannot-open-yet-another-connection-to-mysql/
  post_id: 8832
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2014-12-08T15:57:14'
published_at_gmt: '2014-12-08T15:57:14'
modified_at: '2026-04-28T22:14:57'
modified_at_gmt: '2026-04-28T22:14:57'
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
- MySQL database
- MySQL server connection error
tag_slugs:
- mysql-database
- mysql-server-connection-error
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/connection-to-MySQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Application Cannot Open Another Connection to MySQL

Source: [Percona Blog](https://www.percona.com/blog/what-happens-when-your-application-cannot-open-yet-another-connection-to-mysql/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2014-12-08T15:57:14

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Have you ever experienced a situation where one moment you can connect to the MySQL database and the next moment you cannot, only to be able to connect again a second later? As you may know one cannot open infinite connections with MySQL. There’s a practical limit and more often than not it is imposed … Continued

## Structure detectee

- H2: Understanding the problem at hand
- H2: Reproducing the problem
- H2: Possible Solutions
- H3: Increasing port range
- H3: Adding extra IP addresses and listening to multiple ports
- H3: Modifying the connection behavior of the application(s)
- H3: Tweaking TCP parameter settings
- H4: tcp_tw_reuse
- H4: tcp_tw_recycle
- H4: tcp_max_tw_ buckets
- H3: If “LAMP” server, use local socket
- H2: Clarification

## Images et graphiques reperes

- featured / image: [Application Cannot Open Another Connection to MySQL](https://www.percona.com/wp-content/uploads/2026/03/connection-to-MySQL.jpg)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
