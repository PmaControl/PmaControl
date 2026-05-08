---
title: ProxySQL Improves MySQL SSL Connections
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proxysql-improves-mysql-ssl-connections/
  post_id: 17340
source_author:
  name: Jervin Real
  slug: jervin
  url: https://www.percona.com/blog/author/jervin/
  website: ''
published_at: '2017-09-19T18:47:39'
published_at_gmt: '2017-09-19T18:47:39'
modified_at: '2026-05-05T18:50:02'
modified_at_gmt: '2026-05-05T18:50:02'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- ProxySQL
matched_filters:
- category:proxysql:2261
- search:proxysql
categories:
- Insight for DBAs
- ProxySQL
- Security
category_slugs:
- insight-for-dbas
- proxysql
- security
tags:
- Connections
- MySQL
- OpenVPN
- ProxySQL
- security
- SSL
tag_slugs:
- connections
- mysql
- openvpn
- proxysql
- security
- ssl
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/mysql-connection-times-small.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ProxySQL Improves MySQL SSL Connections

Source: [Percona Blog](https://www.percona.com/blog/proxysql-improves-mysql-ssl-connections/)

Auteur source: [Jervin Real](https://www.percona.com/blog/author/jervin/)

Publication: 2017-09-19T18:47:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at how ProxySQL improves MySQL SSL connection performance. When deploying MySQL with SSL, the main concern is that the initial handshake causes significant overhead if you are not using connection pools (i.e., mysqlnd-mux with PHP, mysql.connector.pooling in Python, etc.). Closing and making new connections over and over can greatly … Continued

## Images et graphiques reperes

- featured / image: [ProxySQL Improves MySQL SSL Connections](https://www.percona.com/wp-content/uploads/2026/03/mysql-connection-times-small.png)
- content / image: [ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/proxysql-ssl.png)
- content / image: [ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/mysql-connection-times-300x209.png)

## Auteur source

As Senior Consultant, Jervin partners with Percona's customers on building reliable and highly performant MySQL infrastructures while also doing other fun stuff like watching cat videos on the internet. Jervin joined Percona in Apr 2010.
