---
title: Load Balancing ProxySQL in AWS
source:
  name: Percona Blog
  url: https://www.percona.com/blog/load-balancing-proxysql-in-aws/
  post_id: 23832
source_author:
  name: Ivan Groenewold
  slug: ivan-groenewold
  url: https://www.percona.com/blog/author/ivan-groenewold/
  website: ''
published_at: '2021-01-28T15:32:16'
published_at_gmt: '2021-01-28T15:32:16'
modified_at: '2026-04-27T22:21:29'
modified_at_gmt: '2026-04-27T22:21:29'
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
- Cloud
- MySQL
- ProxySQL
category_slugs:
- cloud
- mysql
- proxysql
tags:
- AWS
- cloud
- MySQL
- ProxySQL
tag_slugs:
- aws
- cloud
- mysql
- proxysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Load-Balancing-ProxySQL-in-AWS.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Load Balancing ProxySQL in AWS

Source: [Percona Blog](https://www.percona.com/blog/load-balancing-proxysql-in-aws/)

Auteur source: [Ivan Groenewold](https://www.percona.com/blog/author/ivan-groenewold/)

Publication: 2021-01-28T15:32:16

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are several ways to deploy ProxySQL between your applications and the database servers. A common approach is to have a floating virtual IP (VIP) managed by keepalived as the application endpoint. The proxies have to be strategically provisioned to improve the resiliency of the solution (different hardware, network segments, etc,). When we consider cloud … Continued

## Structure detectee

- H2: Creating a Load Balancer
- H2: Adding the ProxySQL Targets
- H2: Creating the LB Listener
- H2: Testing Access
- H3: Final Considerations

## Images et graphiques reperes

- featured / image: [Load Balancing ProxySQL in AWS](https://www.percona.com/wp-content/uploads/2026/03/Load-Balancing-ProxySQL-in-AWS.png)
- content / image: [Load Balancing ProxySQL in AWS](https://www.percona.com/wp-content/uploads/2026/03/Load-Balancing-ProxySQL-in-AWS-300x169.png)

## Auteur source

Passionate about technology, Ivan Groenewold is a seasoned professional with extensive experience in database management, cloud infrastructure, and software development. With a focus on optimizing performance and scalability, Ivan excels in designing and implementing solutions for complex systems, particularly in MongoDB and cloud-native environments. Known for problem-solving and a results-driven mindset, Ivan combines technical expertise with a commitment to continuous learning, delivering high-quality solutions for modern enterprise applications.
