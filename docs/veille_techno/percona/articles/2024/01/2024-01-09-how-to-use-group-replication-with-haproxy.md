---
title: How to Use Group Replication with Haproxy
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-use-group-replication-with-haproxy/
  post_id: 27948
source_author:
  name: Ivan Groenewold
  slug: ivan-groenewold
  url: https://www.percona.com/blog/author/ivan-groenewold/
  website: ''
published_at: '2024-01-09T14:54:23'
published_at_gmt: '2024-01-09T14:54:23'
modified_at: '2026-03-26T20:26:45'
modified_at_gmt: '2026-03-26T20:26:45'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- ProxySQL
matched_filters:
- category:mysql:83
- search:proxysql
categories:
- Insight for DBAs
- MySQL
- Open Source
category_slugs:
- insight-for-dbas
- mysql
- open-source
tags:
- group replication
- haproxy
- Innodb cluster
- MySQL
- mysql-and-variants
tag_slugs:
- group-replication
- haproxy
- innodb-cluster
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/abstract-digital-background-science-technology-networks-big-data-link-3d-rendering-1.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Use Group Replication with Haproxy

Source: [Percona Blog](https://www.percona.com/blog/how-to-use-group-replication-with-haproxy/)

Auteur source: [Ivan Groenewold](https://www.percona.com/blog/author/ivan-groenewold/)

Publication: 2024-01-09T14:54:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When working with group replication, MySQL router would be the obvious choice for the connection layer. It is tightly coupled with the rest of the technologies since it is part of the InnoDB cluster stack. The problem is that except for simple workloads, MySQL router’s performance is still not on par with other proxies like … Continued

## Structure detectee

- H2: Architecture
- H2: Health check script
- H2: Xinetd service
- H2: Testing the service
- H2: Haproxy configuration
- H2: Connecting our application
- H3: Closing thoughts

## Images et graphiques reperes

- featured / image: [How to Use Group Replication with Haproxy](https://www.percona.com/wp-content/uploads/2026/03/abstract-digital-background-science-technology-networks-big-data-link-3d-rendering-1.jpg)

## Auteur source

Passionate about technology, Ivan Groenewold is a seasoned professional with extensive experience in database management, cloud infrastructure, and software development. With a focus on optimizing performance and scalability, Ivan excels in designing and implementing solutions for complex systems, particularly in MongoDB and cloud-native environments. Known for problem-solving and a results-driven mindset, Ivan combines technical expertise with a commitment to continuous learning, delivering high-quality solutions for modern enterprise applications.
