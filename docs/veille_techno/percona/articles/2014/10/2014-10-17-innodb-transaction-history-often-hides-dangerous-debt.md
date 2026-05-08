---
title: Innodb transaction history often hides dangerous ‘debt’
source:
  name: Percona Blog
  url: https://www.percona.com/blog/innodb-transaction-history-often-hides-dangerous-debt/
  post_id: 8640
source_author:
  name: Peter Zaitsev
  slug: pz
  url: https://www.percona.com/blog/author/pz/
  website: ''
published_at: '2014-10-17T14:02:36'
published_at_gmt: '2014-10-17T14:02:36'
modified_at: '2026-05-04T22:28:00'
modified_at_gmt: '2026-05-04T22:28:00'
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
- InnoDB
- Percona Cloud Tools
- Percona Server for MySQL
- Peter Zaitsev
- Primary
- unpurged transaction histories
- write-intensive workloads
- XtraDB
tag_slugs:
- innodb
- percona-cloud-tools
- percona-server
- peter-zaitsev
- primary
- unpurged-transaction-histories
- write-intensive-workloads
- xtradb
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/img_5440169573bec.png
image_count: 11
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Innodb transaction history often hides dangerous ‘debt’

Source: [Percona Blog](https://www.percona.com/blog/innodb-transaction-history-often-hides-dangerous-debt/)

Auteur source: [Peter Zaitsev](https://www.percona.com/blog/author/pz/)

Publication: 2014-10-17T14:02:36

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In many write-intensive workloads Innodb/XtraDB storage engines you may see hidden and dangerous “debt” being accumulated – unpurged transaction “history” which if not kept in check over time will cause serve performance regression or will take all free space and cause an outage. Let’s talk about where it comes from and what can you do … Continued

## Images et graphiques reperes

- featured / image: [Innodb transaction history often hides dangerous ‘debt’](https://www.percona.com/wp-content/uploads/2026/03/img_5440169573bec.png)
- content / image: [img_544016e387ead.png](https://www.percona.com/wp-content/uploads/2026/03/img_544016e387ead.png)
- content / image: [img_5440177dc5724.png](https://www.percona.com/wp-content/uploads/2026/03/img_5440177dc5724.png)
- content / image: [img_54401a3062412.png](https://www.percona.com/wp-content/uploads/2026/03/img_54401a3062412.png)
- content / image: [img_54401a807db08.png](https://www.percona.com/wp-content/uploads/2026/03/img_54401a807db08.png)
- content / image: [img_54401a8f02963.png](https://www.percona.com/wp-content/uploads/2026/03/img_54401a8f02963.png)
- content / image: [img_54401a9d6ba44.png](https://www.percona.com/wp-content/uploads/2026/03/img_54401a9d6ba44.png)
- content / image: [img_54401e1a9c7ba.png](https://www.percona.com/wp-content/uploads/2026/03/img_54401e1a9c7ba.png)
- content / image: [img_54401e2aa5fd1.png](https://www.percona.com/wp-content/uploads/2026/03/img_54401e2aa5fd1.png)
- content / image: [img_54401e3b22dfa.png](https://www.percona.com/wp-content/uploads/2026/03/img_54401e3b22dfa.png)
- content / image: [img_54401e549f1f1.png](https://www.percona.com/wp-content/uploads/2026/03/img_54401e549f1f1.png)

## Auteur source

Peter managed the High Performance Group within MySQL until 2006, when he founded Percona. Peter has a Master's Degree in Computer Science and is an expert in database kernels, computer hardware, and application scaling.
