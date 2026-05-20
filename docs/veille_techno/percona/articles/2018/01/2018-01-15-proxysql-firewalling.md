---
title: ProxySQL Firewalling
source:
  name: Percona Blog
  url: https://www.percona.com/blog/proxysql-firewalling/
  post_id: 17884
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2018-01-15T22:57:10'
published_at_gmt: '2018-01-15T22:57:10'
modified_at: '2026-05-05T18:58:20'
modified_at_gmt: '2026-05-05T18:58:20'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- ProxySQL
matched_filters:
- category:monitoring:2104
- category:mysql:83
- category:proxysql:2261
- search:proxysql
categories:
- Monitoring
- MySQL
- ProxySQL
- Security
category_slugs:
- monitoring
- mysql
- proxysql
- security
tags:
- Database security
- Firewall
- Firewalling
- How to
- ProxySQL
- security
tag_slugs:
- database-security
- firewall
- firewalling
- how-to
- proxysql
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-Firewalling-small.png
image_count: 15
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# ProxySQL Firewalling

Source: [Percona Blog](https://www.percona.com/blog/proxysql-firewalling/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2018-01-15T22:57:10

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at ProxySQL firewalling (how to use ProxySQL as a firewall). Not long ago we had an internal discussion about security, and how to enforce a stricter set of rules to prevent malicious acts and block other undesired queries. ProxySQL came up as a possible tool that could help us … Continued

## Structure detectee

- H2: Using ProxySQL
- H2: How?
- H2: The rules
- H2: What is the impact?
- H2: Use match_digest
- H2: What can be done? Use DIGEST instead
- H2: Conclusions

## Images et graphiques reperes

- featured / image: [ProxySQL Firewalling](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL-Firewalling-small.png)
- content / image: [ProxySQL Firewalling](https://www.percona.com/wp-content/uploads/2026/03/ProxySQL_firewall-300x300.png)
- content / image: [Screen-Shot-2018-01-03-at-1.37.04-AM.png](https://www.percona.com/wp-content/uploads/2026/03/Screen-Shot-2018-01-03-at-1.37.04-AM.png)
- content / image: [queries_routed_baseline.png](https://www.percona.com/wp-content/uploads/2026/03/queries_routed_baseline.png)
- content / image: [queries_routed_baseline_per_server.png](https://www.percona.com/wp-content/uploads/2026/03/queries_routed_baseline_per_server.png)
- content / image: [QP_cost_baseline.png](https://www.percona.com/wp-content/uploads/2026/03/QP_cost_baseline.png)
- content / image: [QP_efficency_baseline.png](https://www.percona.com/wp-content/uploads/2026/03/QP_efficency_baseline.png)
- content / image: [queries_routed_match.png](https://www.percona.com/wp-content/uploads/2026/03/queries_routed_match.png)
- content / image: [queries_routed_match_per_server.png](https://www.percona.com/wp-content/uploads/2026/03/queries_routed_match_per_server.png)
- content / image: [QP_cost_match.png](https://www.percona.com/wp-content/uploads/2026/03/QP_cost_match.png)
- content / image: [QP_efficency_match.png](https://www.percona.com/wp-content/uploads/2026/03/QP_efficency_match.png)
- content / image: [queries_routed_digest.png](https://www.percona.com/wp-content/uploads/2026/03/queries_routed_digest.png)
- content / image: [queries_routed_digest_per_server.png](https://www.percona.com/wp-content/uploads/2026/03/queries_routed_digest_per_server.png)
- content / image: [QP_cost_digest.png](https://www.percona.com/wp-content/uploads/2026/03/QP_cost_digest.png)
- content / image: [QP_efficency_digest.png](https://www.percona.com/wp-content/uploads/2026/03/QP_efficency_digest.png)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
