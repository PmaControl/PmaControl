---
title: Using ProxySQL to connect to IPv6-only databases over IPv4
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-proxysql-to-connect-to-ipv6-only-databases-over-ipv4/
  post_id: 19274
source_author:
  name: James Lawrie
  slug: james-lawrie
  url: https://www.percona.com/blog/author/james-lawrie/
  website: ''
published_at: '2018-09-10T10:30:10'
published_at_gmt: '2018-09-10T10:30:10'
modified_at: '2026-05-05T19:54:59'
modified_at_gmt: '2026-05-05T19:54:59'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:proxysql
categories:
- MySQL
- Percona Software
- ProxySQL
- Security
category_slugs:
- mysql
- percona-software
- proxysql
- security
tags:
- ipv6
- network
tag_slugs:
- ipv6
- network
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/connect-to-ipv6-database-from-ipv4-application-using-proxysql.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using ProxySQL to connect to IPv6-only databases over IPv4

Source: [Percona Blog](https://www.percona.com/blog/using-proxysql-to-connect-to-ipv6-only-databases-over-ipv4/)

Auteur source: [James Lawrie](https://www.percona.com/blog/author/james-lawrie/)

Publication: 2018-09-10T10:30:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

It’s 2018. Maybe now is the time to start migrating your network to IPv6, and your database infrastructure is a great place to start. Unfortunately, many legacy applications don’t offer the option to connect to MySQL directly over IPv6 (sometimes even if passing a hostname). We can work around this by using ProxySQL’s IPv6 support … Continued

## Structure detectee

- H4: Step 1: Install ProxySQL for your distribution
- H4: Step 2: Configure ProxySQL to listen on IPv4 TCP port 3306 by editing /etc/proxysql.cnf and starting it
- H4: Step 3: Configure ACLs on the destination database server to allow ProxySQL to connect over IPv6
- H4: Step 4: Add the IPv6 address of the destination server to ProxySQL and add users
- H4: Step 5: Configure your application to connect to ProxySQL over IPv4 on localhost4 (IPv4 localhost)
- H4: Step 6: Verify

## Images et graphiques reperes

- featured / image: [Using ProxySQL to connect to IPv6-only databases over IPv4](https://www.percona.com/wp-content/uploads/2026/03/connect-to-ipv6-database-from-ipv4-application-using-proxysql.jpg)
- content / image: [connect to ipv6 database from ipv4 application using proxysql](https://www.percona.com/wp-content/uploads/2026/03/connect-to-ipv6-database-from-ipv4-application-using-proxysql-300x199.jpg)

## Auteur source

James has spent over a decade in a variety of Linux and MySQL support roles, with a specific interest in reliability and performance through simplicity. He spends his free time riding motorbikes, lifting weights just to put them back down again, or studying Polish.
