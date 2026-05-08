---
title: Using pt-heartbeat with ProxySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/using-pt-heartbeat-with-proxysql/
  post_id: 21390
source_author:
  name: David Ducos
  slug: david-ducos
  url: https://www.percona.com/blog/author/david-ducos/
  website: ''
published_at: '2020-01-02T20:29:06'
published_at_gmt: '2020-01-02T20:29:06'
modified_at: '2026-04-27T21:27:21'
modified_at_gmt: '2026-04-27T21:27:21'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
- ProxySQL
matched_filters:
- category:mysql:83
- category:proxysql:2261
- search:percona-toolkit
- search:proxysql
categories:
- MySQL
- ProxySQL
category_slugs:
- mysql
- proxysql
tags:
- MySQL
- ProxySQL
- pt-heartbeat
tag_slugs:
- mysql
- proxysql
- pt-heartbeat
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pt-heartbeat-with-ProxySQL.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Using pt-heartbeat with ProxySQL

Source: [Percona Blog](https://www.percona.com/blog/using-pt-heartbeat-with-proxysql/)

Auteur source: [David Ducos](https://www.percona.com/blog/author/david-ducos/)

Publication: 2020-01-02T20:29:06

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

ProxySQL and Orchestrator are usually installed to achieve high availability when using MySQL replication. On a failover (or graceful takeover) scenario, Orchestrator will promote a slave, and ProxySQL will redirect the traffic. Depending on how your environment is configured, and how long the promotion takes, you could end up in a scenario where you need … Continued

## Structure detectee

- H3: Why Would We Want pt-heartbeat With ProxySQL?
- H3: How Do I Deploy pt-heartbeat for a ProxySQL Environment?
- H3: Dealing With Master Takeover/Failover
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Using pt-heartbeat with ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/pt-heartbeat-with-ProxySQL.png)
- content / image: [Using pt-heartbeat with ProxySQL](https://www.percona.com/wp-content/uploads/2026/03/pt-heartbeat-with-ProxySQL-300x168.png)

## Auteur source

David studied Computer Science in National University of La Plata and has worked as a DBA consultant since 2008. For the past 3 years he worked with a worldwide platform of free classifieds up until he joined Percona's consulting team in November 2014. David lives near Buenos Aires, Argentina and in his free time loves to spend time with his family.
