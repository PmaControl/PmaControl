---
title: Avoiding MySQL ALTER table downtime
source:
  name: Percona Blog
  url: https://www.percona.com/blog/avoiding-mysql-alter-table-downtime/
  post_id: 8774
source_author:
  name: Andrew Moore
  slug: amoore
  url: https://www.percona.com/blog/author/amoore/
  website: ''
published_at: '2014-11-18T17:50:54'
published_at_gmt: '2014-11-18T17:50:54'
modified_at: '2026-04-28T22:14:25'
modified_at_gmt: '2026-04-28T22:14:25'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
- tag:percona-toolkit:378
categories:
- Insight for DBAs
- MySQL
- Percona Services
category_slugs:
- insight-for-dbas
- mysql
- percona-services
tags:
- MySQL ALTER table
- Percona MySQL Managed Services
- Percona Toolkit
- Primary
- pt-online-schema-change
tag_slugs:
- mysql-alter-table
- percona-mysql-managed-services
- percona-toolkit
- primary
- pt-online-schema-change
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Avoiding-MySQL-ALTER-table-downtime.jpeg
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Avoiding MySQL ALTER table downtime

Source: [Percona Blog](https://www.percona.com/blog/avoiding-mysql-alter-table-downtime/)

Auteur source: [Andrew Moore](https://www.percona.com/blog/author/amoore/)

Publication: 2014-11-18T17:50:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

MySQL table alterations can interrupt production traffic causing bad customer experience or in worst cases, loss of revenue. Not all DBAs, developers, and syadmins know MySQL well enough to avoid this pitfall. DBAs usually encounter these kinds of production interruptions when working with upgrade scripts that touch both application and database or if an inexperienced … Continued

## Structure detectee

- H3: To pt-osc or not to pt-osc?

## Images et graphiques reperes

- featured / image: [Avoiding MySQL ALTER table downtime](https://www.percona.com/wp-content/uploads/2026/03/Avoiding-MySQL-ALTER-table-downtime.jpeg)
- content / graph_or_chart: [DDL Decision chart](https://www.percona.com/wp-content/uploads/2026/03/DDLFlow1-300x296.png)
  Caption: Choosing the right DDL option

## Auteur source

Since fall 2013, Andrew has been working within Percona's Remote DBA team plying his experience to the client's environments and internal tools developed to keep operations slick. He lives in the UK with his young family and loves to complain about the less than perfect climate. Andrew makes time to pursue an amateur soccer career but won't be trading in MySQL any time soon.
